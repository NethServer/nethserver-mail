#!/usr/bin/perl

#
# Copyright (C) 2019 Nethesis S.r.l.
# http://www.nethesis.it - nethserver@nethesis.it
#
# This script is part of NethServer.
#
# NethServer is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License,
# or any later version.
#
# NethServer is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with NethServer.  If not, see COPYING.
#

use strict;
use warnings;
use JSON;

sub read_rspamd
{
    my $api = shift;
    my $out = `curl --connect-timeout 2 'http://localhost:11334/$api' 2>/dev/null`;
    if ($out) {
        return decode_json($out);
    } else {
        return {};
    }
}

sub get_defaults
{
    my $type = shift;
    my $db = shift;

    if ($type eq 'user') {
        my $default_quota = $db->get_prop('dovecot', 'QuotaDefaultSize') || '20';
        my $default_retention = $db->get_prop('dovecot', 'SpamRetentionTime') || '180d';
        return {
            'MailAccess' => 'public',
            'MailForwardKeepMessageCopy' => 'no',
            'MailForwardStatus' => 'disabled',
            'MailQuotaCustom' => $default_quota,
            'MailQuotaType' => 'default',
            'MailSpamRetentionStatus' => 'disabled',
            'MailSpamRetentionTime' => substr($default_retention, 0, -1), # remove final 'd'
            'MailStatus' => 'enabled',
            'MailForwardAddress' => [],
        };
    } else {
        return {
            'MailStatus' => 'enabled',
            'MailAccess' => 'public'
        };
    }
}

sub get_account_object
{
    my $account = shift;
    my $users = shift;
    my $groups = shift;
    my $adb = shift;
    my $ret;

    my @tmp = split(/\@/,$account);
    my $wildcard_name = $tmp[0]."@";
    if ($account eq 'root') {
        return {'name' => 'root', 'type' => 'builtin'};
    } elsif ($users->{$account}) {
        my $displayname = $account;
        $displayname =~ s/(\@.*)$//;
        return {'name' => $account, 'displayname' => $displayname, 'type' => 'user'};
    } elsif ($groups->{$account}) {
        return {'name' => $account, 'type' => 'group'}
    } elsif (index($account, 'vmail+') == 0) {
        # remove vmail+ prefix
        return {'name' => substr($account, 6), 'type' => 'public'};
    } elsif ($adb->get($account)) {
        $account =~ s/(\@.*)$//;
        return {'name' => $account, 'type' => $adb->get_prop($account, 'type')};
    } elsif ($adb->get($wildcard_name)) {
        return {'name' => $wildcard_name, 'type' => $adb->get_prop($wildcard_name, 'type')}
    } else {
        return {'name' => $account, 'type' => 'external'};
    }
}

sub get_public_mailbox_event_args
{
    my $input = shift;
    my @args;

    my $acls = $input->{'acls'};
    my $newname = $input->{'newname'} || "";

    # current name
    push(@args, $input->{'name'});
    if ($newname ne '' && $newname ne $input->{'name'}) {
        # rename 
        push( @args, $newname);
    } else {
        # confirm name
        push( @args, $input->{'name'});
    }

    my @read = ("read", "lookup", "write-seen");
    my @write = ("insert", "create", "write", "write-deleted");
    my @owner = ("expunge", "admin", "post");

    foreach (@$acls) {
        push(@args, $_->{'type'}."=". $_->{'name'});
        my $perms = "";
        if ($_->{'right'} eq 'read') {
            $perms = join(" ", @read);
        } elsif ($_->{'right'} eq 'read-write') {
            $perms = join(" ", @read, @write);
        } elsif ($_->{'right'} eq 'full') {
            $perms = join(" ", @read, @write, @owner);
        }
        push(@args, $perms);
    }

    return \@args;
}

1;
