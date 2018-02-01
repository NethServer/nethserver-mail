Summary: Enforces anti-spam and anti-virus checks on any message entering the mail system.
Name: nethserver-mail-filter
Version: 2.0.0
Release: 1%{?dist}
License: GPL
URL: %{url_prefix}/%{name} 
Source0: %{name}-%{version}.tar.gz
BuildArch: noarch
Requires: nethserver-mail-common, nethserver-antivirus
Requires: nethserver-dnsmasq, nethserver-unbound
Requires: rspamd
Requires: redis
Requires: zstd

BuildRequires: perl
BuildRequires: nethserver-devtools

%description
Configures rspamd that is an advanced spam filtering system that allows evaluation of messages
by a number of rules including regular expressions, statistical analysis and
custom services such as URL black lists. Each message is analysed by Rspamd
and given a spam score.

%prep
%setup

%build
perl createlinks

%install
rm -rf %{buildroot}
mkdir -p root/var/lib/redis/rspamd
(cd root; find . -depth -print | cpio -dump %{buildroot})
%{genfilelist} %{buildroot} \
  --dir /var/lib/redis/rspamd 'attr(0755,redis,redis)' \
  --file /etc/rspamd/dkim_whitelist.inc 'attr(0440,_rspamd,_rspamd)' \
  --file /etc/rspamd/local.d/mid.inc 'attr(0440,_rspamd,_rspamd)' \
  --file /etc/rspamd/spf_whitelist.inc 'attr(0440,_rspamd,_rspamd)' \
  --file /var/lib/rspamd/2tld.inc.local 'attr(0640,_rspamd,_rspamd)' \
  --file /var/lib/rspamd/dkim_whitelist.inc.local 'attr(0640,_rspamd,_rspamd)' \
  --file /var/lib/rspamd/dmarc_whitelist.inc.local 'attr(0640,_rspamd,_rspamd)' \
  --file /var/lib/rspamd/mime_types.inc.local 'attr(0640,_rspamd,_rspamd)' \
  --file /var/lib/rspamd/rspamd_dynamic 'attr(0640,_rspamd,_rspamd)' \
  --file /var/lib/rspamd/spf_dkim_whitelist.inc.local 'attr(0640,_rspamd,_rspamd)' \
  --file /var/lib/rspamd/spf_whitelist.inc.local 'attr(0640,_rspamd,_rspamd)' \
  --file /var/lib/rspamd/surbl-whitelist.inc.local 'attr(0640,_rspamd,_rspamd)' \
> %{name}-%{version}-filelist

%files -f %{name}-%{version}-filelist
%config(noreplace) /etc/rspamd/dkim_whitelist.inc
%config(noreplace) /etc/rspamd/local.d/mid.inc
%config(noreplace) /etc/rspamd/spf_whitelist.inc
%config(noreplace) /var/lib/rspamd/2tld.inc.local
%config(noreplace) /var/lib/rspamd/dkim_whitelist.inc.local
%config(noreplace) /var/lib/rspamd/dmarc_whitelist.inc.local
%config(noreplace) /var/lib/rspamd/mime_types.inc.local
%config(noreplace) /var/lib/rspamd/rspamd_dynamic
%config(noreplace) /var/lib/rspamd/spf_dkim_whitelist.inc.local
%config(noreplace) /var/lib/rspamd/spf_whitelist.inc.local
%config(noreplace) /var/lib/rspamd/surbl-whitelist.inc.local
%defattr(-,root,root)
%doc COPYING
%doc README.rst
%dir %{_nseventsdir}/%{name}-update

%changelog
* Fri Oct 06 2017 Davide Principi <davide.principi@nethesis.it> - 1.4.5-1
- Sieve errors prevent mail forwarding - Bug NethServer/dev#5351

* Mon May 22 2017 Davide Principi <davide.principi@nethesis.it> - 1.4.4-1
- mail-filter: enable spamassassin anti-backscatter rules - NethServer/dev#5280

* Mon Sep 05 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.3-1
- mail blacklist ignored if whitelist is empty - Bug #3401 [NethServer 6]

* Wed Aug 24 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.2-1
- Mail disclaimer is not added #5080

* Thu Jul 21 2016 Davide Principi <davide.principi@nethesis.it> - 1.4.1-1
- SMTP mail reception delayed in receive only systems - NethServer/dev#5050

* Thu Jul 07 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 1.4.0-1
- First NS7 release

* Thu Mar 03 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.6-1
- Email attachment block custom list too strict - Enhancement #3361 [NethServer]
- sa-update without internet breaks spamd/amavisd - Bug #3359 [NethServer]

* Thu Feb 18 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.5-1
- Update KAM.cf spam sigs - Enhancement #3350 [NethServer]
- Amavis virus+spam policy tweaks - Enhancement #3348 [NethServer]

* Tue Nov 10 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.4-1
- Use DNSBL to fight spam - Feature #3302 [NethServer]
- Log smtp traffic rejection - Enhancement #3295 [NethServer]
- amavisd default log_level - Enhancement #3274 [NethServer]

* Wed May 20 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.3-1
- Mail filter bypass - Enhancement #3150 [NethServer]
- Spam scan of relay domains - Bug #3148 [NethServer]

* Thu Apr 23 2015 Davide Principi <davide.principi@nethesis.it> - 1.3.2-1
- SMTP proxy: error on email domain creation - Bug #3124 [NethServer]

* Thu Apr 09 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.1-1
- Error in /etc/postfix/virtual and /etc/dovecot/dovecot.conf template - Bug #3093 [NethServer]

* Thu Mar 05 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.0-1
- Mail filter: block port 25 from LAN to external network - Enhancement #2894 [NethServer]

* Tue Dec 09 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.2.1-1.ns6
- Support  Sanesecurity Foxhole - Feature #2897 [NethServer]

* Tue Oct 07 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.2.0-1.ns6
- Differentiate Postfix syslog_name parameters - Enhancement #2784
- Relax Postix restrictions for whitelisted senders - Enhancement #2768

* Wed Mar 19 2014 Davide Principi <davide.principi@nethesis.it> - 1.1.6-1.ns6
- Let amavisd PASS unchecked content - Enhancement #2689 [NethServer]

* Mon Mar 10 2014 Davide Principi <davide.principi@nethesis.it> - 1.1.5-1.ns6
- Antivirus dashboard widget - Enhancement #2686 [NethServer]
- Wrong default thresholds for antispam - Enhancement #2681 [NethServer]
- Mail filter: remove clamav warning - Enhancement #2678 [NethServer]

* Wed Dec 18 2013 Davide Principi <davide.principi@nethesis.it> - 1.1.4-1.ns6
- Stale ClamAV DB warning - Bug #2351 [NethServer]

* Thu Oct 17 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.3-1.ns6
- Ignore spam training when moving a message to Trash #2252

* Mon Sep 02 2013 Davide Principi <davide.principi@nethesis.it> - 1.1.2-1.ns6
- amavisd-new 2.8.0 from EPEL - Enhancement #2093 [NethServer]

* Tue Jun 25 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.1-1.ns6
- Adjust bayes scores  #1909

* Tue Apr 30 2013 Davide Principi <davide.principi@nethesis.it> - 1.1.0-1.ns6
- Full automatic package install/upgrade/uninstall support #1870 #1872 #1874
- Spam settings migration #1820
- White/Black lists migration #1796

* Tue Mar 19 2013 Davide Principi <davide.principi@nethesis.it> - 1.0.1-1.ns6
- spam-training.sh: fixed wrong bash syntax to close stdout descriptor. Refs #1656
- *.spec: use url_prefix macro in URL tag; removed nethserver-devtools specific version requirement; fixed Release tag expansion. Refs #1654

