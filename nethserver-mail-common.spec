Summary: Common configuration for mail packages
Name: nethserver-mail-common
Version: 1.6.6
Release: 1%{?dist}
License: GPL
URL: %{url_prefix}/%{name}
Source0: %{name}-%{version}.tar.gz
BuildArch: noarch

Requires: nethserver-mail-smarthost
Requires: amavisd-new, perl-Convert-BinHex, tmpwatch

BuildRequires: nethserver-devtools

%description
Common configuration for mail packages, based on Postfix.

%prep
%setup

%build
%{makedocs}
perl createlinks

%install
rm -rf %{buildroot}
(cd root; find . -depth -print | cpio -dump %{buildroot})
%{genfilelist} %{buildroot} | sed '
\|^%{_sysconfdir}/sudoers.d/20_nethserver_mail_common$| d
' > %{name}-%{version}-filelist

mkdir -p %{buildroot}/%{_nsstatedir}/mail-disclaimers

%files -f %{name}-%{version}-filelist
%defattr(-,root,root)
%doc COPYING
%doc README.rst
%dir %{_nseventsdir}/%{name}-update
%dir %attr(2775,root,adm) %{_nsstatedir}/mail-disclaimers
%config %attr (0440,root,root) %{_sysconfdir}/sudoers.d/20_nethserver_mail_common

%changelog
* Fri Nov 24 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.6-1
- LocalDelivery mail domain actually relayed - NethServer/dev#5387

* Fri Oct 06 2017 Davide Principi <davide.principi@nethesis.it> - 1.6.5-1
- Sieve errors prevent mail forwarding - Bug NethServer/dev#5351

* Thu Jul 06 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.4-1
- Backup-data: add bayes db - NethServer/dev#5325

* Mon Mar 06 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.3-1
- Migration from sme8 - NethServer/dev#5196

* Wed Aug 24 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.2-1
- Mail disclaimer is not added #5080

* Thu Jul 21 2016 Davide Principi <davide.principi@nethesis.it> - 1.6.1-1
- SMTP mail reception delayed in receive only systems - NethServer/dev#5050

* Thu Jul 07 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 1.6.0-1
- First NS7 release

* Fri May 20 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.5-1
- Email queue management: limit total email - Enhancement #3377 [NethServer]

* Wed Apr 27 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.4-1
- Postfix/amavisd speed_adjust parameter - Enhancement #3379 [NethServer]

* Thu Feb 18 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.3-1
- Disable Postfix address_verify_negative_cache  - Enhancement #3347 [NethServer]
- Mail common: remove template warning  - Enhancement #3335 [NethServer]

* Tue Nov 10 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.2-1
- amavisd default log_level - Enhancement #3274 [NethServer]

* Wed Jul 15 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.1-1
- Event trusted-networks-modify - Enhancement #3195 [NethServer]

* Wed Jun 24 2015 Davide Principi <davide.principi@nethesis.it> - 1.5.0-1
- Postfix: in-memory address verification database - Enhancement #3135 [NethServer]
- Dovecot admin master user - Feature #2990 [NethServer]
- "Queue message max size" custom value - Enhancement #2940 [NethServer]
- mail-server: configure IP-based access policy from UI - Feature #2919 [NethServer]

* Tue May 19 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.5-1
- mail-common/smarthost: SASL authentication failure - Bug #3143 [NethServer]

* Thu Apr 23 2015 Davide Principi <davide.principi@nethesis.it> - 1.4.4-1
- SMTP proxy: error on email domain creation - Bug #3124 [NethServer]

* Thu Apr 09 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.3-1
- Smarthost configuration: cannot disable authentication - Bug #2979 [NethServer]

* Tue Dec 09 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.2-1.ns6
- Avoid fetchmail bounces - Enhancement #2954 [NethServer]
- DNS: remove role property from dns db key - Enhancement #2915 [NethServer]

* Mon Nov 03 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.1-1.ns6
- Access policy smtpauth still too restrictive - Bug #2917 [NethServer]

* Tue Oct 07 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.0-1.ns6
- Silence postfix resource problems notifications - Enhancement #2824
- Relay denied to SMTP clients both in local networks and submission_whitelist - Bug #2814
- Smarthost configuration ignored during migration - Bug #2804
- Edit workgroup name when role is Workstation - Enhancement #2803
- Differentiate Postfix syslog_name parameters - Enhancement #2784
- Relax Postix restrictions for whitelisted senders - Enhancement #2768
- Customizable SMTP HELO value - Enhancement #2767
- Mail spying / always Bcc - Feature #2750

* Fri Jun 06 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.3-1.ns6
- Disclaimer creation fails - Bug #2724
- Mail: Incorrect value RelayPort_label - Bug #2713

* Wed Feb 05 2014 Davide Principi <davide.principi@nethesis.it> - 1.3.2-1.ns6
- Mail server: can't send mail bigger than 50MB - Bug #2634 [NethServer]

* Mon Sep 02 2013 Davide Principi <davide.principi@nethesis.it> - 1.3.1-1.ns6
- SMTP temporary error on non-existing recipients - Bug #2108 [NethServer]
- amavisd-new 2.8.0 from EPEL - Enhancement #2093 [NethServer]

* Mon Jul 29 2013 Davide Principi <davide.principi@nethesis.it> - 1.3.0-1.ns6
- Mail-common: email queue management - Feature #2042 [NethServer]

* Mon Jun 10 2013 Davide Principi <davide.principi@nethesis.it> - 1.2.1-1.ns6
- Spawn more amavisd wokers #1908
- Timeout after END-OF-MESSAGE from localhost #1968

* Tue Apr 30 2013 Davide Principi <davide.principi@nethesis.it> - 1.2.0-1.ns6
- Full automatic package install/upgrade/uninstall support #1870 #1872 #1874
- Allow submission policy overriding in dependant packages and DB #1818 #1856
- Added default empty disclaimer files to workaround altermime SIGSEGVs #1819

* Tue Mar 19 2013 Davide Principi <davide.principi@nethesis.it> - 1.1.0-1.ns6
- MX record configuration. Refs #1725
- *.spec: use url_prefix macro in URL tag; set minimum version requirements. Refs #1654 #1653

* Thu Jan 31 2013 Davide Principi <davide.principi@nethesis.it> - 1.0.1-1.ns6
- Postfix installation moved to nethserver-base . Refs #1635 -- admin's mailbox


