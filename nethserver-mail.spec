%define obsversion 2.2.11

Name: nethserver-mail
Summary: Mail services configuration
Version: 2.29.5
Release: 1%{?dist}
License: GPL
URL: %{url_prefix}/%{name}
BuildArch: noarch
Source0: %{name}-%{version}.tar.gz
Source1: %{name}-ui.tar.gz
%description
Mail services configuration packages, based on Postfix, Dovecot, Rspamd

%package common
Summary: Common configuration for mail packages
BuildArch: noarch
Requires: %{name}-smarthost >= %{version}
Obsoletes: nethserver-mail2-common < %{obsversion}
Provides: nethserver-mail2-common = %{version}
BuildRequires: nethserver-devtools
Requires: nethserver-cockpit-lib
Requires: discount
Requires: bind-utils
Requires: swaks
%description common
Common configuration for mail packages, based on Postfix.

%package disclaimer
Summary: Append legal/disclaimer text to outbound messages
Requires: altermime
Requires: %{name}-common >= %{version}
Obsoletes: nethserver-mail2-disclaimer < %{obsversion}
Provides: nethserver-mail2-disclaimer = %{version}
BuildRequires: nethserver-devtools
BuildArch: noarch
%description disclaimer
Append legal/disclaimer text to outbound messages with alteMIME

%package filter
Summary: Enforces anti-spam and anti-virus checks on any message entering the mail system.
BuildArch: noarch
Requires: %{name}-common >= %{version}, nethserver-antivirus
Requires: nethserver-dnsmasq, nethserver-unbound
Requires: rspamd >= 2.2
Requires: redis
Requires: zstd
Requires: mod_authnz_pam
Requires: olefy
Requires: nethserver-httpd-admin-service
Obsoletes: nethserver-mail2-filter < %{obsversion}
Provides: nethserver-mail2-filter = %{version}
Obsoletes: nethserver-spamd < 1.0.2
BuildRequires: perl
BuildRequires: nethserver-devtools
%description filter
Configures rspamd that is an advanced spam filtering system that allows evaluation of messages
by a number of rules including regular expressions, statistical analysis and
custom services such as URL black lists. Each message is analysed by Rspamd
and given a spam score.

%package server
Summary: Mail server implementation based on postfix and dovecot packages
BuildArch: noarch
Requires: dovecot, dovecot-pigeonhole
Requires: dovecot-deleted-to-trash
Requires: %{name}-common >= %{version}
Requires: perl(Text::Unidecode)
Requires: postfix
Requires: nethserver-sssd
Requires: opendkim
Obsoletes: nethserver-mail2-server < %{obsversion}
Provides: nethserver-mail2-server = %{version}
BuildRequires: nethserver-devtools
%description server
Mail server implementation based on postfix and dovecot packages.

%package ipaccess
Summary: IMAP IP access policy for a specific group of users
BuildArch: noarch
Requires: %{name}-server >= %{version}
Obsoletes: nethserver-mail2-ipaccess < %{obsversion}
Provides: nethserver-mail2-ipaccess = %{version}
%description ipaccess
Mail server extension that implements IP access policy for IMAP service based
on group membership.

%package getmail
Summary: NethServer getmail
BuildArch: noarch
Requires: %{name}-server >= %{version}, %{name}-filter >= %{version}
Obsoletes: nethserver-getmail < %{obsversion}
Provides: nethserver-getmail = %{version}
Obsoletes: nethserver-mail2-getmail < %{obsversion}
Provides: nethserver-mail2-getmail
Requires: getmail
%description getmail
Getmail add-on for NethServer

%package imapsync
Summary: NethServer imapsync
BuildArch: noarch
Requires: %{name}-server >= %{version}
Requires: imapsync
%description imapsync
Imapsync add-on for NethServer

%package p3scan
Summary: NethServer p3scan
BuildArch: noarch
Obsoletes: nethserver-p3scan < %{obsversion}
Provides: nethserver-p3scan = %{version}
Obsoletes: nethserver-mail2-p3scan < %{obsversion}
Provides: nethserver-mail2-p3scan = %{version}
Requires: nethserver-firewall-base
Requires: %{name}-filter >= %{version}
Requires: p3scan
%description p3scan
p3scan (pop3 proxy) add-on for NethServer

%package smarthost
Summary: Route outbound email messages to a smart host
BuildArch: noarch
Requires: nethserver-base
Requires: cyrus-sasl-plain
Requires: postfix
%description smarthost
Configures Postfix to send outbound messages through the given MTA (smarthost),
with SMTP/AUTH and StartTLS encryption.

%package quarantine
Summary: NethServer Email quarantine
BuildArch: noarch
Requires: %{name}-filter >= %{version}
Requires: rspamd >= 1.8.0
%description quarantine
Quarantine (Rspamd feature) add-on for NethServer

%prep
%setup -q

%build
sed -i 's/_RELEASE_/%{version}/' %{name}.json
for package in common server ipaccess filter getmail p3scan disclaimer smarthost quarantine imapsync; do
    if [[ -f createlinks-${package} ]]; then
        # Hack around createlinks output dir prefix, hardcoded as "root/":
        rm -f root
        ln -sf ${package} root
        perl createlinks-${package}
    fi
    ( cd ${package} ; %{makedocs} )
    %{genfilelist} ${PWD}/${package} \
          >> ${package}.lst
    # !!! Do not create any file or directory after genfilelist invocation !!!
done

#
# Create additional directories and override permissions from genfilelist
#

mkdir -p common/%{perl_vendorlib}
mkdir -p common/%{_nsstatedir}/mail-disclaimers
mkdir -p common/%{_nsstatedir}/sieve-scripts
mkdir -p server/%{_nsstatedir}/vmail
mkdir -p filter/var/lib/redis/rspamd
mkdir -p getmail/var/lib/getmail
mkdir -p imapsync/var/log/imapsync
mkdir -p imapsync/var/lib/nethserver/imapsync

sed -i -e '\|^/etc/sudoers.d/50_nsapi_nethserver_mail|d' common.lst
cat >>common.lst <<'EOF'
%dir %{_nseventsdir}/%{name}-common-update
%dir %attr(0770,root,vmail) %{_nsstatedir}/sieve-scripts
%dir %attr(2775,root,adm) %{_nsstatedir}/mail-disclaimers
%config %attr (0440,root,root) %{_sysconfdir}/sudoers.d/20_nethserver_mail_common
%attr(0440,root,root) /etc/sudoers.d/50_nsapi_nethserver_mail
/usr/share/cockpit/nethserver/applications/%{name}.json
/usr/libexec/nethserver/api/%{name}/
/usr/share/cockpit/%{name}/
EOF

cat >>server.lst <<'EOF'
%dir %{_nseventsdir}/%{name}-server-update
%attr(0644, root, root) %config(noreplace) %{_sysconfdir}/logrotate.d/imap
%ghost %attr(0644, root, root) %{_sysconfdir}/pam.d/dovecot-master
%dir %attr(0700,vmail,vmail) %{_nsstatedir}/vmail
%config %attr (0440,root,root) %{_sysconfdir}/sudoers.d/20_nethserver_mail_server
%attr(0644,root,root) %config %ghost %{_sysconfdir}/systemd/system/dovecot.service.d/limits.conf
EOF

cat >>ipaccess.lst <<'EOF'
%dir %{_nseventsdir}/%{name}-ipaccess-update
%attr(0644,root,root) %config %ghost %{_sysconfdir}/dovecot/ipaccess.conf
EOF

cat >>disclaimer.lst <<'EOF'
%dir %{_nseventsdir}/%{name}-disclaimer-update
%attr(0644,root,root) %config %ghost %{_sysconfdir}/postfix/disclaimer
EOF

cat >>filter.lst <<'EOF'
%dir %{_nseventsdir}/%{name}-filter-update
%dir %attr(0755,redis,redis) /var/lib/redis/rspamd
%config(noreplace) %attr(0440,_rspamd,_rspamd) /etc/rspamd/local.d/maps.d/dkim_whitelist.inc.local
%config(noreplace) %attr(0440,_rspamd,_rspamd) /etc/rspamd/local.d/maps.d/dmarc_whitelist.inc.local
%config(noreplace) %attr(0440,_rspamd,_rspamd) /etc/rspamd/local.d/maps.d/greylist-whitelist-domains.inc
%config(noreplace) %attr(0440,_rspamd,_rspamd) /etc/rspamd/local.d/maps.d/mid.inc
%config(noreplace) %attr(0440,_rspamd,_rspamd) /etc/rspamd/local.d/maps.d/mime_types.inc.local
%config(noreplace) %attr(0440,_rspamd,_rspamd) /etc/rspamd/local.d/maps.d/redirectors.inc
%config(noreplace) %attr(0440,_rspamd,_rspamd) /etc/rspamd/local.d/maps.d/spf_dkim_whitelist.inc.local
%config(noreplace) %attr(0440,_rspamd,_rspamd) /etc/rspamd/local.d/maps.d/spf_whitelist.inc.local
%config(noreplace) %attr(0440,_rspamd,_rspamd) /etc/rspamd/local.d/maps.d/surbl-whitelist.inc.local
%config(noreplace) %attr(0640,_rspamd,_rspamd) /var/lib/rspamd/2tld.inc.local
%config(noreplace) %attr(0640,_rspamd,_rspamd) /var/lib/rspamd/dkim_whitelist.inc.local
%config(noreplace) %attr(0640,_rspamd,_rspamd) /var/lib/rspamd/dmarc_whitelist.inc.local
%config(noreplace) %attr(0640,_rspamd,_rspamd) /var/lib/rspamd/mime_types.inc.local
%config(noreplace) %attr(0640,_rspamd,_rspamd) /var/lib/rspamd/rspamd_dynamic
%config(noreplace) %attr(0640,_rspamd,_rspamd) /var/lib/rspamd/spf_dkim_whitelist.inc.local
%config(noreplace) %attr(0640,_rspamd,_rspamd) /var/lib/rspamd/spf_whitelist.inc.local
%config(noreplace) %attr(0640,_rspamd,_rspamd) /var/lib/rspamd/surbl-whitelist.inc.local
EOF

cat >>getmail.lst <<'EOF'
%dir %{_nseventsdir}/%{name}-getmail-update
%dir %attr(0750,vmail,vmail) /var/lib/getmail
EOF

cat >>p3scan.lst <<'EOF'
%dir %{_nseventsdir}/%{name}-p3scan-update
EOF

cat >>smarthost.lst <<'EOF'
%dir %{_nseventsdir}/%{name}-smarthost-update
EOF

cat >>quarantine.lst <<'EOF'
%dir %{_nseventsdir}/%{name}-quarantine-update
EOF

cat >>imapsync.lst <<'EOF'
%dir %{_nseventsdir}/%{name}-imapsync-update
%dir %attr(0700,vmail,vmail) /var/log/imapsync
%dir %attr(2770,root,vmail) /var/lib/nethserver/imapsync
EOF

%install
mkdir -p %{buildroot}/usr/share/cockpit/%{name}/
mkdir -p %{buildroot}/usr/share/cockpit/nethserver/applications/
mkdir -p %{buildroot}/usr/libexec/nethserver/api/%{name}/
tar xf %{SOURCE1} -C %{buildroot}/usr/share/cockpit/%{name}/
cp -a %{name}.json %{buildroot}/usr/share/cockpit/nethserver/applications/
cp -a api/* %{buildroot}/usr/libexec/nethserver/api/%{name}/

for package in common server ipaccess filter getmail p3scan disclaimer smarthost quarantine imapsync; do
    (cd ${package}; find . -depth -print | cpio -dump %{buildroot})
done

%files common -f common.lst
%defattr(-,root,root)
%doc COPYING
%doc README.rst
%doc ui/README.md

%files disclaimer -f disclaimer.lst
%defattr(-,root,root)
%doc COPYING
%doc README.rst

%files filter -f filter.lst
%defattr(-,root,root)
%doc COPYING
%doc README.rst

%files server -f server.lst
%defattr(-,root,root)
%doc COPYING
%doc README.rst
%doc bats/
%doc migration/sync_maildirs.sh
%doc migration/bayes_training.sh

%files ipaccess -f ipaccess.lst
%defattr(-,root,root)
%doc COPYING
%doc README.rst

%files getmail -f getmail.lst
%defattr(-,root,root)
%doc COPYING
%doc README.rst

%files p3scan -f p3scan.lst
%defattr(-,root,root)
%doc COPYING
%doc README.rst

%files smarthost -f smarthost.lst
%defattr(-,root,root)
%doc COPYING
%doc README.rst

%files quarantine -f quarantine.lst
%defattr(-,root,root)
%doc COPYING
%doc README.rst

%files imapsync -f imapsync.lst
%defattr(-,root,root)
%doc COPYING
%doc README.rst

%pre common
# ensure vmail group exists for sieve-directory
getent group vmail > /dev/null || groupadd -r vmail

%pre server
# ensure vmail user and group exists:
getent group vmail > /dev/null || groupadd -r vmail
if ! id vmail >/dev/null 2>&1 ; then
   useradd -g vmail -c 'Virtual mailboxes owner' -r -M -d /var/lib/nethserver/vmail -s /sbin/nologin vmail
fi

# add vmail group to postfix user
usermod -G vmail -a postfix >/dev/null 2>&1

%changelog
* Mon Jan 11 2021 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.29.5-1
- IMAPSYNC: dynamic exclusion of the Trash folder  - NethServer/dev#6376

* Tue Dec 22 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.19.4-1
- Imapsync should not count and sync shared folders - Bug NethServer/dev#6373

* Wed Dec 16 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.19.3-1
- Email mailboxes incomplete page loading - Bug NethServer/dev#6366

* Wed Nov 25 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.19.2-1
- Access web applications from port 980 - NethServer/dev#6344

* Wed Nov 18 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.19.1-1
- New NethServer 7.9.2009 defaults - NethServer/dev#6320

* Tue Nov 17 2020 Davide Principi <davide.principi@nethesis.it> - 2.19.0-1
- Disable extended mode for oletools - NethServer/dev#6334
- New NethServer 7.9.2009 defaults - NethServer/dev#6320

* Tue Nov 17 2020 Davide Principi <davide.principi@nethesis.it> - 2.18.2-1
- Disable extended mode for oletools - NethServer/dev#6334

* Wed Nov 04 2020 Davide Principi <davide.principi@nethesis.it> - 2.18.1-1
- Official Olefy 0.56 does not block macro virus - Bug NethServer/dev#6321

* Wed Oct 21 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.18.0-1
- Imapsync: Exclude folders of synchronization - NethServer/dev#6306
- Imapsync: double-quote in a password broke imapsync  - Bug NethServer/dev#6303
- Group mail-quota-recalc action failed - Bug NethServer/dev#6310
- IMAPSYNC: Passfile are not saved in UTF8  - Bug NethServer/dev#6308

* Fri Oct 09 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.17.4-1
- Imapsync: Provide synchronization information - NethServer/dev#6299

* Mon Sep 28 2020 Davide Principi <davide.principi@nethesis.it> - 2.17.3-1
- Email Domains page error after user removal - Bug NethServer/dev#6282

* Thu Sep 03 2020 Davide Principi <davide.principi@nethesis.it> - 2.17.2-1
- Email documentation upgrade to Cockpit - NethServer/dev#6247

* Mon Jul 20 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.17.1-1
- Imapsync: handle password with special chars - Bug NethServer/dev#6233
- ui. fix doc info title in relay (#211)

* Wed Jul 08 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.17.0-1
- Postfix: Move TLS to nethserver-mail-common - NethServer/dev#6219

* Thu Jul 02 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.16.0-1
- IMAP account syncronization - NethServer/dev#6211
- Human readable numbers in Cockpit dashboards - NethServer/dev#6206

* Tue Jun 16 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.15.2-1
- Spamtrain.nh error when spam filtering is disabled - Bug NethServer/dev#6200
- Mail: can't set custom mailbox quota - Bug NethServer/dev#6197

* Tue Jun 09 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.15.1-1
- Postfix: use a stronger Diffie-Hellman group - NethServer/dev#6192

* Thu May 28 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.15.0-1
- Remove TLS 1.0 and TLS 1.1 - NethServer/dev#6170
- Smarthost events generate an error - Bug NethServer/dev#6180

* Fri May 22 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.14.0-1
- Create smart host relay to send email with another smtp - NethServer/dev#6169

* Tue May 19 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.13.1-1
- Cockpit: user deletion doesn't clean up db accounts - NethServer/dev#6164

* Tue Apr 28 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.13.0-1
- RSPAMD: LUA rules are not disabled when SpamCheckStatus is disabled - NethServer/dev#6131
- New mail server defaults - NethServer/dev#6118

* Tue Mar 31 2020 Davide Principi <davide.principi@nethesis.it> - 2.12.4-1
- nethserver-mail-getmail installs docs in / - Bug NethServer/dev#6102

* Tue Mar 24 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.12.3-1
- mail-filter: add mail counters to inventory (#188)

* Thu Mar 19 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.12.2-1
- [cockpit] email - disable greylist - Bug NethServer/dev#6084

* Mon Mar 02 2020 Stephane de Labrusse <stephdl@de-labrusse.fr> - 2.12.1-1
- Cockpit: error while updating block attachment custom list - Bug NethServer/dev#6073

* Mon Feb 24 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.12.0-1
- Email/dovecot: add shared seen support - NethServer/dev#6064

* Tue Feb 11 2020 Davide Principi <davide.principi@nethesis.it> - 2.11.3-1
- POP3 connector discards mail during ClamAV reloads - Bug NethServer/dev#6052

* Mon Jan 27 2020 Stephane de Labrusse <stephdl@de-labrusse.fr> - 2.11.2-1
- Wrong DKIM configuration instructions - Bug NethServer/dev#6036

* Mon Jan 27 2020 Stephane de Labrusse <stephdl@de-labrusse.fr> - nethserver-nfs.spec-1
- Wrong DKIM configuration instructions - Bug NethServer/dev#6036

* Fri Jan 17 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.11.1-1
- Maildir not deleted from Cockpit - Bug NethServer/dev#6030

* Wed Jan 15 2020 Davide Principi <davide.principi@nethesis.it> - 2.11.0-1
- Cockpit: change package Dashboard page title - NethServer/dev#6004
- Upgrade to Rspamd 2.x - NethServer/dev#5964
- Rspamd: whitelist/blacklist enhancement - NethServer/dev#5940

* Tue Dec 10 2019 Davide Principi <davide.principi@nethesis.it> - 2.10.0-1
- Rspamd: whitelist/blacklist enhancement - NethServer/dev#5940

* Mon Dec 02 2019 Davide Principi <davide.principi@nethesis.it> - 2.9.2-2
- Fix olefy service restart - Hotfix NethServer/dev#5963

* Mon Dec 02 2019 Davide Principi <davide.principi@nethesis.it> - 2.9.2-1
- Olefy TCP port 10050 conflict - Bug NethServer/dev#5963
- Rspamd: deleting a rule always delete latest - Bug NethServer/dev#5951
- Getmail: differences between NethGUI and Cockpit - Bug NethServer/dev#5952

* Thu Nov 21 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.9.1-1
- Email: domain menu not correctly shown - Bug Nethserver/dev#5942

* Wed Nov 20 2019 Davide Principi <davide.principi@nethesis.it> - 2.9.0-1
- Cockpit: incorrect alias addresses shown on public folders - Bug NethServer/dev#5915
- Scan MS Office files for bad macros - NethServer/dev#5891

* Mon Oct 28 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.8.3-1
- Logs page in Cockpit - Bug NethServer/dev#5866
- Cosmetic: log noise from cockpit email mailboxes - Bug NethServer/dev#5878
- Bad auth with sender relay host - Bug NethServer/dev#5888
- Cockpit: failed validation on mail connectors - Bug NethServer/dev#5873

* Thu Oct 10 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.8.2-1
- Cockpit: improve English labels - NethServer/dev#5856

* Wed Oct 02 2019 Stephane de Labrusse <stephdl@de-labrusse.fr> - 2.8.1-1
- Rspamd: authenticated users must get score headers - Bug NethServer/dev#5852

* Tue Oct 01 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.8.0-1
- Junk folder for public mailboxes  - NethServer/dev#5843
- Sudoers based authorizations for Cockpit UI - NethServer/dev#5805
- Improved Cockpit UI

* Fri Sep 20 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.7.3-1
- Custom mail quota ignored with short login name - Bug NethServer/dev#5837

* Thu Sep 19 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.7.2-1
- Groups not listed in Cockpit public mailbox creation form - Bug NethServer/dev#5833
- Public mailbox email alias not available with Cockpit creation - Bug NethServer/dev#5834

* Tue Sep 03 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.7.1-1
- Cockpit Mail: "EnableAutoGroups" action and SpamFolder - Bug Nethserver/dev#5824
- Cockpit. List correct application version - Nethserver/dev#5819

* Fri Aug 30 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.7.0-1
- getmail: log errors via syslog - NethServer/dev#5815
- Antivirus: improve memory usage - NethServer/dev#5803

* Wed Aug 28 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.6.7-1
- Rspamd: multimap must use a smtp rejection message  - Bug NethServer/dev#5811

* Fri Jul 05 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.6.6-1
- Backup-config fails fails due to shared mailbox name containing dots - Bug Nethserver/dev#5783

* Tue Jun 11 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.6.5-1
- Do not soft reject authenticated users - Bug NethServer/dev#5773
- SPAM subject can create false positive - Bug NethServer/dev#5771
- Cockpit API: handle name clash for external destinations
- Cockpit UI: support aliases with external destinations only

* Wed May 29 2019 Stephane de Labrusse <stephdl@de-labrusse.fr> - 2.6.4-1
- Failed to send email during clamd DB reload - Bug NethServer/dev#5769

* Mon May 27 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.6.3-1
- Minor fixes on Cockpit UI

* Tue May 21 2019 Stephane de Labrusse <stephdl@de-labrusse.fr> - 2.6.2-1
- Rspamd cannot find /plugins/effective_tld_names.dat - Bug NethServer/dev#5761

* Wed May 15 2019 Stephane de Labrusse <stephdl@de-labrusse.fr> - 2.6.1-1
- Cockpit UI user mail quota slider assigns a bad value - Bug NethServer/dev#5758
- AV check skipped during clamd DB reloads - Bug NethServer/dev#5755

* Wed May 08 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.6.0-1
- Mail Cockpit UI - NethServer/dev#5744
- Mail server: sender dependent relay - NethServer/dev#5743
- Bypass Rspamd spam checks if IP is whitelisted - NethServer/dev#5752

* Wed Apr 10 2019 Davide Principi <davide.principi@nethesis.it> - 2.5.0-1
- Email distribution lists based on system groups - NethServer/dev#5725
- Release of rspamd 1.9.1 - NethServer/dev#5741

* Thu Feb 28 2019 Stephane de Labrusse <stephdl@de-labrusse.fr> - 2.4.5-1
- SMTP sender/login validation - NethServer/dev#5672
- Spamtrain could end in redis timeout - Bug NethServer/dev#5722

* Mon Jan 21 2019 Davide Principi <davide.principi@nethesis.it> - 2.4.4-1
- Mail quota not updated - Bug NethServer/dev#5697
- SMTP sender/login validation - NethServer/dev#5672

* Sat Dec 22 2018 Stephane de Labrusse <stephdl@de-labrusse.fr> - 2.4.3-1
- Getmail: emails are no more deleted - Bug NethServer/dev#5679

* Wed Dec 12 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.4.2-1
- Sub folders of shared mailbox are not removed - Bug NethServer/dev#5671

* Fri Dec 07 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.4.1-1
- Detach user-cleanup event from user-delete - NethServer/dev#5624

* Mon Dec 03 2018 Davide Principi <davide.principi@nethesis.it> - 2.4.0-1
- Rebuild or replace dovecot plugins - Bug NethServer/dev#5646
- Relax HELO restrictions from trusted networks - NethServer/dev#5644

* Mon Nov 05 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.3.2-1
- Upgrade rspamd-1.8.1 - NethServer/dev#5608
- Integration of arm specific requirements - NethServer/dev#5610
- Email notification API - NethServer/dev#5614

* Wed Oct 24 2018 Davide Principi <davide.principi@nethesis.it> - 2.3.1-1
- P3Scan spam score skew - Bug NethServer/dev#5606
- Getmail spam score skew - Bug NethServer/dev#5611
- Disable 'sa-update' cron job after migration to rspamd - Bug NethServer/dev#5605
- Fix p3scan attachment corruption

* Tue Oct 02 2018 Davide Principi <davide.principi@nethesis.it> - 2.3.0-1
- Mail2 automatic upgrade - NethServer/dev#5589
- Change confusing UI labels in Dashboard and MailAccount User - NethServer/dev#5596

* Fri Sep 28 2018 Davide Principi <davide.principi@nethesis.it> - 2.2.10-1
- Rspamd doesn't rewrite subject with p3scan - Bug NethServer/dev#5583

* Tue Sep 25 2018 Davide Principi <davide.principi@nethesis.it> - 2.2.9-1
- Rspamd doesn't rewrite subject with getmail - Bug NethServer/dev#5572
- Fix HTTP code in proxy config fo rspamd UI - NethServer/nethserver-mail#71

* Tue Aug 28 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.2.8-1
- Email quota validator error for users - NethServer/dev#5566

* Mon Aug 13 2018 Stephane de Labrusse <stephdl@de-labrusse.fr> - 2.2.7-1
- Email moved to junk folder if nethserver-mail2-filter is not installed - NethServer/dev#5562
- Code from Mark Verlinde (markVnl)

* Tue Aug 07 2018 Stephane de Labrusse <stephdl@de-labrusse.fr> - 2.2.6-1
- Rspamd service issue when antivirus is disabled - NethServer/dev#5551
- Enhancement: (un)mask password fields - NethServer/dev#5554

* Sat Jul 07 2018 Stephane de Labrusse <stephdl@de-labrusse.fr> - 2.2.5-1
- Show the DKIM key as 255 characters chunks - NethServer/dev#5539

* Mon Jul 02 2018 Davide Principi <davide.principi@nethesis.it> - 2.2.4-1
- Fix of keytab backup configuration expansion - NethServer/dev#5536

* Tue Jun 26 2018 Davide Principi <davide.principi@nethesis.it> - 2.2.3-1
- Allow Elliptic Curve Criptography (ECC) certificate - NethServer/dev#5509

* Thu Jun 14 2018 Davide Principi <davide.principi@nethesis.it> - 2.2.2-1
- DKIM signature for legal note (disclaimer) - NethServer/dev#5528

* Thu Jun 07 2018 Davide Principi <davide.principi@nethesis.it> - 2.2.1-1
- DKIM: body hash mismatch or not verifiable  - Bug NethServer/dev#5514
- Getmail discards an email if rspamc fails to connect to rspamd - Bug NethServer/dev#5513

* Wed May 16 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.2.0-1
- Change of defaults for NS 7.5 - NethServer/dev#5490

* Fri May 11 2018 Stephane de Labrusse <stephdl@de-labrusse.fr> - 2.1.1-1
- rspamd-1.7.4 - NethServer/dev#5478

* Tue Apr 17 2018 Davide Principi <davide.principi@nethesis.it> - 2.1.0-1
- Disable SSLv3 protocol and weak ciphers - NethServer/dev#5421
- Upgrade rspamd to 1.7.3 - NethServer/dev#5437
- Mail2: disable greylisting by default - NethServer/dev#5449
- Append a legal note to sent messages for Email 2 - NethServer/dev#5452
- Email domain not delivering to shared mailbox - Bug NethServer/dev#5445
- Hardening TLS policy 2018-03-30 - NethServer/dev#5438
- SMTP proxy cannot relay primary mail domain  - Bug NethServer/dev#5456

* Thu Mar 08 2018 Davide Principi <davide.principi@nethesis.it> - 2.0.0-1
- Rspamd as a new nethserver-mail-filter - NethServer/dev#5394

* Tue Jan 23 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.11.0-1
- IP-based IMAP access restriction - NethServer/dev#5395

* Fri Nov 24 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.6-1
- LocalDelivery mail domain actually relayed - NethServer/dev#5387

* Fri Nov 24 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.10.18-1
- Shared mailboxes not restored from configuration backup - Bug NethServer/dev#5381
- Bad mail delivery to non-existing mailbox with catchall address - Bug NethServer/dev#5379

* Fri Oct 06 2017 Davide Principi <davide.principi@nethesis.it> - 1.6.5-1
- Sieve errors prevent mail forwarding - Bug NethServer/dev#5351

* Tue Oct 03 2017 Davide Principi <davide.principi@nethesis.it> - 1.10.17-1
- Spam training is not triggered - Bug NethServer/dev#5353

* Thu Jul 06 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.4-1
- Backup-data: add bayes db - NethServer/dev#5325

* Wed Jun 28 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.10.16-1
- spam-expunge: avoid cron output for vmail public mailbox

* Tue May 30 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.10.15-1
- List builtin entries in User mailboxes and Mail aliases pages - NethServer/dev#5296
- Do not list shared mailbox subfolders - Bug NethServer/dev#5298
- Remove "vmail+" prefix from Mail aliases page - NethServer/dev#5300
- Bogus Junk folder appears in shared mailboxes - Bug NethServer/dev#5294
- Add "Local network only" option to User mailboxes - NethServer/dev#5295
- Multiple forward addresses in mailboxes - NethServer/dev#5292
- Set a group name as mail alias destination - NethServer/dev#5293

* Thu May 25 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.10.14-1
- ns6upgrade: suppress cosmetic errors

* Mon May 22 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.10.13-1
- Spam expunge: avoid "Fatal: User doesn't exist " error - Bug NethServer/dev#5287

* Mon May 22 2017 Davide Principi <davide.principi@nethesis.it> - 1.4.4-1
- mail-filter: enable spamassassin anti-backscatter rules - NethServer/dev#5280

* Wed May 10 2017 Davide Principi <davide.principi@nethesis.it> - 1.10.12-1
- Upgrade from NS 6 via backup and restore - NethServer/dev#5234

* Tue Apr 11 2017 Davide Principi <davide.principi@nethesis.it> - 1.10.11-1
- Redirection of postmaster to external e-mail address - NethServer/dev#5266
- Custom mail quota applied once - Bug NethServer/dev#5265
- Upgrade from NS 6 via backup and restore - NethServer/dev#5234

* Mon Mar 06 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.3-1
- Migration from sme8 - NethServer/dev#5196

* Fri Mar 03 2017 Davide Principi <davide.principi@nethesis.it> - 1.10.10-1
- Relay rejected for primary mail domain - Bug NethServer/dev#5233
- Fix service access prop

* Wed Mar 01 2017 Davide Principi <davide.principi@nethesis.it> - 1.10.9-1
- User mail quota ignored during message delivery - Bug NethServer/dev#5216
- Mail domain option Accept unknown recipients overrides valid addresses - Bug NethServer/dev#5222
- Allow removal of primary mail domain - NethServer/dev#5227

* Tue Feb 14 2017 Davide Principi <davide.principi@nethesis.it> - 1.10.8-1
- Support short login with POP3  - Bug NethServer/dev#5212
- Mail forward ignored by mail alias - Bug NethServer/dev#5210
- Accounts DB: user type overrides existing pseudonym record - Bug NethServer/dev#5211

* Thu Jan 26 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.10.7-1
- Email alias does not expand group members - Bug NethServer/dev#5207

* Wed Jan 18 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.10.6-1
- Priority rule on email aliases - NethServer/dev#5201
- Mail server: enable user shared mailbox by default - NethServer/dev#5192

* Thu Dec 15 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.10.5-1
- Sieve filters duplicate user maildirs with legacy short format - Bug NethServer/dev#5150

* Mon Nov 07 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.10.4-1
- Legacy short user name support - NethServer/dev#5144

* Mon Oct 17 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.10.3-1
- Shared mailboxes default "0" element - Bug NethServer/dev#5132

* Thu Sep 22 2016 Davide Principi <davide.principi@nethesis.it> - 1.10.2-1
- Lucene IMAP Full Text Search - NethServer/dev#5106

* Mon Sep 05 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.3-1
- mail blacklist ignored if whitelist is empty - Bug #3401 [NethServer 6]

* Wed Aug 24 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.2-1
- Mail disclaimer is not added #5080

* Wed Aug 24 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.2-1
- Mail disclaimer is not added #5080

* Thu Jul 21 2016 Davide Principi <davide.principi@nethesis.it> - 1.6.1-1
- SMTP mail reception delayed in receive only systems - NethServer/dev#5050

* Thu Jul 21 2016 Davide Principi <davide.principi@nethesis.it> - 1.4.1-1
- SMTP mail reception delayed in receive only systems - NethServer/dev#5050

* Thu Jul 21 2016 Davide Principi <davide.principi@nethesis.it> - 1.10.1-1
- Web UI: missing labels - Bug NethServer/dev#5061

* Thu Jul 07 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 1.6.0-1
- First NS7 release

* Thu Jul 07 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 1.4.0-1
- First NS7 release

* Thu Jul 07 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 1.10.0-1
- First NS7 release
