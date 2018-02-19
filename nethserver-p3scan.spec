Name:		nethserver-p3scan
Version: 1.1.2
Release: 1%{?dist}
Summary:	NethServer p3scan
Group:		Networking/Daemons
License:	GPLv2
URL:		http://www.nethesis.it
Source0:	%{name}-%{version}.tar.gz
BuildArch: 	noarch

BuildRequires:	nethserver-devtools
Requires:	nethserver-firewall-base
Requires:	nethserver-spamd
Requires:	nethserver-mail-filter
Requires:	p3scan

%description
p3scan (pop3 proxy) add-on for NethServer

%prep
%setup

%build
%{makedocs}
perl createlinks

%install
rm -rf %{buildroot}
(cd root   ; find . -depth -not -name '*.orig' -print  | cpio -dump %{buildroot})
%{genfilelist} %{buildroot} > %{name}-%{version}-%{release}-filelist
echo "%doc COPYING" >> %{name}-%{version}-%{release}-filelist

%clean 
rm -rf %{buildroot}

%pre

%files -f %{name}-%{version}-%{release}-filelist
%defattr(-,root,root)
%dir %{_nseventsdir}/%{name}-update

%changelog
* Thu Sep 22 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.2-1
- P3scan: mail not filtered - Bug NethServer/dev#5114

* Wed Aug 31 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.1-1
- Replace Fetchmail with getmail - NethServer/dev#5021

* Thu Jul 07 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 1.1.0-1
- First NS7 release

* Tue Nov 10 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.6-1
- spamd for pop3 scan - Enhancement #3285 [NethServer]

* Tue Sep 29 2015 Davide Principi <davide.principi@nethesis.it> - 1.0.5-1
- Make Italian language pack optional - Enhancement #3265 [NethServer]

* Thu Aug 27 2015 Davide Principi <davide.principi@nethesis.it> - 1.0.4-1
- POP3 Proxy spam tag level - Feature #3239 [NethServer]

* Wed Jul 15 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.3-1
- POP3 proxy antispam white and black list - Feature #3217 [NethServer]

* Tue May 19 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.2-1
- Add maxchild option to p3scan.conf - Enhancement #3160 [NethServer]

* Mon Apr 13 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.1-1
- POP3 proxy do not check for spam - Bug #3114 [NethServer]

* Fri Oct 03 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.0-1.ns6
- First release - Feature #2865

