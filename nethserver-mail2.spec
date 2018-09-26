%define obsversion 2.2.9

Name: nethserver-mail
Summary: Mail services configuration
Version: 2.2.10
Release: 1%{?dist}
License: GPL
URL: %{url_prefix}/%{name}
BuildArch: noarch
Source0: %{name}-%{version}.tar.gz
%description
Mail services configuration packages, based on Postfix, Dovecot, Rspamd

%package common
Summary: Common configuration for mail packages
BuildArch: noarch
Requires: nethserver-base
Obsoletes: %{name}2-common < %{obsversion}
Provides: %{name}2-common = %{version}
BuildRequires: nethserver-devtools
%description common
Common configuration for mail packages, based on Postfix.

%package disclaimer
Summary: Append legal/disclaimer text to outbound messages
Requires: altermime
Requires: %{name}-common >= %{version}
Obsoletes: %{name}2-disclaimer < %{obsversion}
Provides: %{name}2-disclaimer = %{version}
BuildRequires: nethserver-devtools
BuildArch: noarch
%description disclaimer
Append legal/disclaimer text to outbound messages with alteMIME

%package filter
Summary: Enforces anti-spam and anti-virus checks on any message entering the mail system.
BuildArch: noarch
Requires: %{name}-common >= %{version}, nethserver-antivirus
Requires: nethserver-dnsmasq, nethserver-unbound
Requires: rspamd >= 1.7.4
Requires: redis
Requires: zstd
Requires: mod_authnz_pam
Obsoletes: %{name}2-filter < %{obsversion}
Provides: %{name}2-filter = %{version}
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
Requires: dovecot, dovecot-pigeonhole, dovecot-antispam
Requires: dovecot-deleted-to-trash
Requires: %{name}-common >= %{version}
Requires: perl(Text::Unidecode)
Requires: postfix
Requires: nethserver-sssd
Requires: opendkim
Obsoletes: %{name}2-server < %{obsversion}
Provides: %{name}2-server = %{version}
BuildRequires: nethserver-devtools
%description server
Mail server implementation based on postfix and dovecot packages.

%package ipaccess
Summary: IMAP IP access policy for a specific group of users
BuildArch: noarch
Requires: %{name}-server >= %{version}
Obsoletes: %{name}2-ipaccess < %{obsversion}
Provides: %{name}2-ipaccess = %{version}
%description ipaccess
Mail server extension that implements IP access policy for IMAP service based
on group membership.

%package getmail
Summary: NethServer getmail
BuildArch: noarch
Requires: %{name}-server >= %{version}, %{name}-filter >= %{version}
Obsoletes: nethserver-getmail < %{obsversion}
Provides: nethserver-getmail = %{version}
Obsoletes: %{name}2-getmail < %{obsversion}
Provides: %{name}2-getmail
Requires: getmail
%description getmail
Getmail add-on for NethServer

%package p3scan
Summary: NethServer p3scan
BuildArch: noarch
Obsoletes: nethserver-p3scan < %{obsversion}
Provides: nethserver-p3scan = %{version}
Obsoletes: %{name}2-p3scan < %{obsversion}
Provides: %{name}2-p3scan = %{version}
Requires: nethserver-firewall-base
Requires: %{name}-filter >= %{version}
Requires: p3scan
%description p3scan
p3scan (pop3 proxy) add-on for NethServer


%prep
%setup

%build
for package in common server ipaccess filter getmail p3scan disclaimer; do
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

cat >>common.lst <<'EOF'
%dir %{_nseventsdir}/%{name}-common-update
%dir %attr(0770,root,vmail) %{_nsstatedir}/sieve-scripts
%dir %attr(2775,root,adm) %{_nsstatedir}/mail-disclaimers
%config %attr (0440,root,root) %{_sysconfdir}/sudoers.d/20_nethserver_mail_common
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
%config(noreplace) %attr(0440,_rspamd,_rspamd) /etc/rspamd/dkim_whitelist.inc
%config(noreplace) %attr(0440,_rspamd,_rspamd) /etc/rspamd/local.d/mid.inc
%config(noreplace) %attr(0440,_rspamd,_rspamd) /etc/rspamd/spf_whitelist.inc
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

%install
for package in common server ipaccess filter getmail p3scan disclaimer; do
    (cd ${package}; find . -depth -print | cpio -dump %{buildroot})
done

%files common -f common.lst
%defattr(-,root,root)
%doc COPYING
%doc README.rst

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
