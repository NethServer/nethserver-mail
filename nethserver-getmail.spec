%define getmail_home /var/lib/getmail

Name:		nethserver-getmail
Version: 1.0.3
Release: 1%{?dist}
Summary:	NethServer getmail
Group:		Networking/Daemons
License:	GPLv2
Source0:	%{name}-%{version}.tar.gz
BuildArch: 	noarch
URL: %{url_prefix}/%{name} 

BuildRequires:	nethserver-devtools
Requires:	nethserver-mail-server, nethserver-spamd, nethserver-mail-filter
Requires:	getmail

%description
Getmail add-on for NethServer

%prep
%setup

%build
%{makedocs}
perl createlinks

%install
rm -rf %{buildroot}
(cd root   ; find . -depth -not -name '*.orig' -print  | cpio -dump %{buildroot})
mkdir -p %{buildroot}/%{getmail_home}
%{genfilelist} %{buildroot} \
    --dir '%{getmail_home}' 'attr(0750,vmail,vmail)' \
    > %{name}-%{version}-%{release}-filelist

%clean 
rm -rf %{buildroot}

%files -f %{name}-%{version}-%{release}-filelist
%defattr(-,root,root)
%dir %{_nseventsdir}/%{name}-update
%doc COPYING

%changelog
* Wed Jun 14 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.3-1
- restore-config: defer migration until the system is configured - NethServer/nethserver-getmail#5

* Thu Apr 20 2017 Davide Principi <davide.principi@nethesis.it> - 1.0.2-1
- Upgrade from NS 6 via backup and restore - NethServer/dev#5234 

* Mon Mar 06 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.1-1
- Migration from sme8 - NethServer/dev#5196

* Fri Aug 05 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.0-1
- Replace Fetchmail with getmail - NethServer/dev#5021

