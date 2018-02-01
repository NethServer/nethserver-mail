Summary: Mail server implementation based on postfix and dovecot packages
Name: nethserver-mail-server
Version: 1.11.0
Release: 1%{?dist}
License: GPL
URL: %{url_prefix}/%{name} 
Source0: %{name}-%{version}.tar.gz
BuildArch: noarch

Requires: dovecot, dovecot-pigeonhole, dovecot-antispam
Requires: dovecot-deleted-to-trash
Requires: nethserver-mail-common
Requires: perl(Text::Unidecode)
Requires: postfix
Requires: nethserver-sssd

BuildRequires: nethserver-devtools

%description
Mail server implementation based on postfix and dovecot packages.

%prep
%setup

%build
%{makedocs}
mkdir -p root%{perl_vendorlib}
perl createlinks

%install
rm -rf %{buildroot}
(cd root; find . -depth -print | cpio -dump %{buildroot})
%{genfilelist} %{buildroot} | \
  sed '\:^/etc/suoders.d/: d' > %{name}-%{version}-filelist.global

mkdir -p %{buildroot}/%{_nsstatedir}/vmail
mkdir -p %{buildroot}/%{_nsstatedir}/sieve-scripts
mkdir -p %{buildroot}/%{_sysconfdir}/dovecot/sieve-scripts
mkdir -p %{buildroot}/%{_sysconfdir}/dovecot/sievc/Maildir

# List of files for the "ipaccess" subpackage
cat - > %{name}-%{version}-filelist-ipaccess <<'EOF' 
/etc/e-smith/db/configuration/defaults/dovecot/RestrictedAccessGroup
/etc/e-smith/templates/etc/dovecot/ipaccess.conf/20restricted_access_group
/etc/e-smith/templates/etc/dovecot/dovecot.conf/40postlogin-ipaccess
/etc/e-smith/events/nethserver-mail-server-save/templates2expand/etc/dovecot/ipaccess.conf
/etc/e-smith/events/nethserver-mail-server-update/templates2expand/etc/dovecot/ipaccess.conf
/etc/e-smith/events/trusted-networks-modify/templates2expand/etc/dovecot/ipaccess.conf
/etc/e-smith/events/nethserver-mail-server-ipaccess-update/services2adjust/dovecot
/etc/e-smith/events/nethserver-mail-server-ipaccess-update/templates2expand/etc/dovecot/dovecot.conf
/etc/e-smith/events/nethserver-mail-server-ipaccess-update/templates2expand/etc/dovecot/ipaccess.conf
/usr/libexec/nethserver/dovecot-postlogin-ipaccess
EOF

# Generate the main packge filelist by filtering out subpackage files:
grep -v -x -F -f %{name}-%{version}-filelist-ipaccess %{name}-%{version}-filelist.global > %{name}-%{version}-filelist

%pre
# ensure vmail user exists:
if ! id vmail >/dev/null 2>&1 ; then
   useradd -c 'Virtual mailboxes owner' -r -M -d /var/lib/nethserver/vmail -s /sbin/nologin vmail
fi

# add vmail group to postfix user
usermod -G vmail -a postfix >/dev/null 2>&1

%files -f %{name}-%{version}-filelist
%defattr(-,root,root)
%doc COPYING
%doc README.rst
%doc bats/
%dir %{_nseventsdir}/%{name}-update
%attr(0644, root, root) %config(noreplace) %{_sysconfdir}/logrotate.d/imap
%doc migration/sync_maildirs.sh
%ghost %attr(0644, root, root) %{_sysconfdir}/pam.d/dovecot-master
%dir %attr(0700,vmail,vmail) %{_nsstatedir}/vmail
%dir %attr(0770,root,vmail) %{_nsstatedir}/sieve-scripts
%dir %attr(0775,root,root) %{_sysconfdir}/dovecot/sieve-scripts
%dir %attr(0775,root,root) %{_sysconfdir}/dovecot/sievc/Maildir
%config %attr (0440,root,root) %{_sysconfdir}/sudoers.d/20_nethserver_mail_server
%attr(0644,root,root) %config %ghost %{_sysconfdir}/systemd/system/dovecot.service.d/limits.conf

%package ipaccess
Summary: IMAP IP access policy for a specific group of users

%files ipaccess -f %{name}-%{version}-filelist-ipaccess
%dir %{_nseventsdir}/%{name}-ipaccess-update
%doc COPYING
%attr(0644,root,root) %config %ghost %{_sysconfdir}/dovecot/ipaccess.conf

%description ipaccess
Mail server extension that implements IP access policy for IMAP service based
on group membership.

%changelog
* Tue Jan 23 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.11.0-1
- IP-based IMAP access restriction - NethServer/dev#5395

* Fri Nov 24 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.10.18-1
- Shared mailboxes not restored from configuration backup - Bug NethServer/dev#5381
- Bad mail delivery to non-existing mailbox with catchall address - Bug NethServer/dev#5379

* Tue Oct 03 2017 Davide Principi <davide.principi@nethesis.it> - 1.10.17-1
- Spam training is not triggered - Bug NethServer/dev#5353

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

* Wed May 10 2017 Davide Principi <davide.principi@nethesis.it> - 1.10.12-1
- Upgrade from NS 6 via backup and restore - NethServer/dev#5234

* Tue Apr 11 2017 Davide Principi <davide.principi@nethesis.it> - 1.10.11-1
- Redirection of postmaster to external e-mail address - NethServer/dev#5266
- Custom mail quota applied once - Bug NethServer/dev#5265
- Upgrade from NS 6 via backup and restore - NethServer/dev#5234

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

* Thu Jul 21 2016 Davide Principi <davide.principi@nethesis.it> - 1.10.1-1
- Web UI: missing labels - Bug NethServer/dev#5061

* Thu Jul 07 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 1.10.0-1
- First NS7 release

* Wed Oct 28 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.9.1-1
- Automatic group email addresses creation - Enhancement #3267 [NethServer]

* Mon Jun 22 2015 Davide Principi <davide.principi@nethesis.it> - 1.9.0-1
- Dovecot admin master user - Feature #2990 [NethServer]
- Error during add/remove system users from a group - Bug #3182 [NethServer]

* Wed May 20 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.8.9-1
- Spam scan of relay domains - Bug #3148 [NethServer]

* Thu Apr 23 2015 Davide Principi <davide.principi@nethesis.it> - 1.8.8-1
- Error in /etc/postfix/virtual and /etc/dovecot/dovecot.conf template - Bug #3093 [NethServer]
- Log imap actions - Feature #3042 [NethServer]

* Thu Apr 09 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.8.7-1
- Error in /etc/postfix/virtual and /etc/dovecot/dovecot.conf template - Bug #3093 [NethServer]

* Fri Feb 27 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.8.6-1
- Log imap actions - Feature #3042 [NethServer]

* Thu Feb 19 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.8.5-1
- Email not deleted when using Outlook client - Enhancement #2810 [NethServer]

* Tue Dec 09 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.8.3-1.ns6
- Avoid fetchmail bounces - Enhancement #2954 [NethServer]
- sync_maildirs.sh delete - Bug #2884 [NethServer]

* Mon Nov 03 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.8.2-1.ns6
- nethserver-mail-group-acl-cleanup FAILED: group not deleted - Bug #2933 [NethServer]
- Action nethserver-mail-group-change-subscriptions fails - Bug #2888 [NethServer]

* Wed Oct 15 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.8.1-1.ns6
- Backup config: remove /etc/aliases - Feature #2739

* Tue Oct 07 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.8.0-1.ns6
- Avoid excessive postfix reload  - Enhancement #2843
- Dovecot: separate log files for IMAP and LMTP/delivery - Enhancement #2841
- Relay denied to SMTP clients both in local networks and submission_whitelist - Bug #2814
- Edit workgroup name when role is Workstation - Enhancement
- Relax Postix restrictions for whitelisted senders - Enhancement #2768
- Mail spying / always Bcc - Feature #2750
- Customizable mail quota increments - Enhancement #2723
- Dashboard mail quota panel: order by size is wrong - Bug #2698

* Thu Jun 12 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.7.0-1.ns6
- AD group mail delivery type switch - Feature #2751
- Use DNS A record to locate AD controllers - Enhancement #2729
- Configurable AD accounts LDAP subtree - Enhancement #2727
- SOGo does not display user mail quota - Bug #2722
- Backup config: minimize creation of new backup - Enhancement #2699

* Thu Apr 17 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.4-1.ns6
- Open POP3s port (995) - Bug #2703

* Mon Mar 10 2014 Davide Principi <davide.principi@nethesis.it> - 1.6.3-1.ns6
- Backup Notification to System administrator fails by default - Bug #2675 [NethServer]

* Fri Feb 28 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.2-1.ns6
- Fix error on pseudonym creation - Bug #2679

* Wed Feb 26 2014 Davide Principi <davide.principi@nethesis.it> - 1.6.1-1.ns6
- Skip migration of builtin mail aliases - Enhancement #2646 [NethServer]

* Wed Feb 05 2014 Davide Principi <davide.principi@nethesis.it> - 1.6.0-1.ns6
- NethCamp 2014 - Task #2618 [NethServer]
- Override mail system /etc/aliases - Enhancement #2499 [NethServer]
- Move admin user in LDAP DB - Feature #2492 [NethServer]
- everyone@ mail alias - Feature #2464 [NethServer]
- Dashboard mail quota usage report - Feature #2433 [NethServer]
- Apply submission whitelist to smtpd port 25 - Enhancement #2422 [NethServer]
- Update all inline help documentation - Task #1780 [NethServer]
- Dashboard: new widgets - Enhancement #1671 [NethServer]

* Wed Dec 18 2013 Davide Principi <davide.principi@nethesis.it> - 1.5.0-1.ns6
- Kerberos keytab file is missing for new services - Bug #2407 [NethServer]
- Non-spam messages in SpamFolder are retained indefinitely - Enhancement #2290 [NethServer]
- Group Shared Folder always shown - Bug #2214 [NethServer]
- Mail-server: avoid warning if Samba is not installed - Enhancement #2153 [NethServer]
- group-create event fails on nethserver-mail-group-acl-adjust action - Bug #2151 [NethServer]
- Allow dot in user and group names - Enhancement #2087 [NethServer]
- Directory: backup service accounts passwords  - Enhancement #2063 [NethServer]
- Mail-server: automatic subscription of group shared folders - Feature #1879 [NethServer]

* Mon Sep 02 2013 Davide Principi <davide.principi@nethesis.it> - 1.4.6-1.ns6
- Group (User) UI module: opening a group for update fails - Bug #2082 [NethServer]

* Fri Aug 02 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.5-1.ns6
- nethserver-mail-backup: handle files and directories with spaces

* Mon Jul 22 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.4-1.ns6
- Make Dovecot accessibile from red interface when server is in gateway mode #2071
- make Postfix accessibile from red interface when server is in gateway mode #2069

* Fri Jul 12 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.3-1.ns6
- Backup: implement and document full restore #2043

* Thu Jul 04 2013 Davide Principi <davide.principi@nethesis.it> - 1.4.2-1.ns6
- Fixed automatic subscription of group shared folders - Feature #1879 [NethServer]

* Mon Jun 10 2013 Davide Principi <davide.principi@nethesis.it> - 1.4.1-1.ns6
- Enable dovecot Listescape plugin #2003
- Automatic subscription of group shared folders #1879

* Wed May 29 2013 Davide Principi <davide.principi@nethesis.it> - 1.4.0-1.ns6
- IMAP access for Active Directory users #1747

* Tue May  7 2013 Davide Principi <davide.principi@nethesis.it> - 1.3.1-1.ns6
- Require nethserver-directory explicitly #1870

* Tue Apr 30 2013 Davide Principi <davide.principi@nethesis.it> - 1.3.0-1.ns6
- Full automatic package install/upgrade/uninstall support #1870 #1872 #1874
- Group Mail UI plugin: show default group pseudonym in create action #1795
- Use sieve global script auto-compilation #1877
- Enabled imapflags obsolete sieve extnsion #1816
- Allow SMTP/AUTH on port 25 through "legacy" SubmissionPolicyType #1818
- Forced delivery of messages to an empty group to postmaster account #1822
- Honoured delivery type "copy" when recipient is group@hostname #1857
- Migrate user sieve filters #1815
- Switch submission policy to "authenticated" when mail-server is installed #1856
- Cleanup empty Maildir/ directory in user's home; reset POSIX acls on destination Maildir/ #1669
- Fixed removal of duplicated postmaster entry during migration #1841
- Migrate spam settings #1820
- Fixed expansion of all pseudonym when a domain is modified #1823
- Experimental support to Active Directory GSSAPI AUTH #1747
- Fixed address extension +spam propagated during message forwarding: removed vmail.nh internal domain suffix, using local(8) daemon with /etc/postfix/aliases template #1844
- Fixed ACL storage path of shared mailboxes #1739
- Fixed master-users file is world-readable: /etc/dovecot/master-users, set permissions 0640  #1825
- Fixed user mail forwarding not honoured in group delivery #1840
- Added migration/sync_maildirs.sh utility: synchronization script for maildirs #1808
- Migrate pseudonym pointing to another pseudonym #1806
- Name pseudonym after migrated account name #1805 

* Tue Mar 19 2013 Davide Principi <davide.principi@nethesis.it> - 1.2.0-1.ns6
- vmail storage moved from /var/lib/vmail into /var/lib/nethserver/vmail, removing user/ subdir. Refs #1739
- Optionally, create default user's mail addresses during user creation. #1623
- IMAP access to admin's mailbox. #1622
- Migration support. #1669 #1726
- Support domainless pseudonyms. #1665
- Create default primary mail domain record. #1530 
- /etc/sysconfig/dovecot template: fixed wrong bash syntax to close stderr descriptor. Fixes #1656 
- *.spec: use %%{url_prefix} macro in URL tag; set minimum version requirements. #1654 #1653


* Thu Jan 31 2013 Davide Principi <davide.principi@nethesis.it> - 1.1.0-1.ns6
- Added postfix/SystemUserRecipientStatus prop. Refs #1635
- Removed localhost.localdomain from transport delivery table. Refs #1635
- Migrate admin's standard mailbox to vmail storage. Refs #1622
- Grant IMAP access to system users in /etc/dovecot/system-users passwd database. Refs #1622
- Dovecot certificates under nethserver-base certificate management. Refs #1634
