# p3scan_clients
# Count the number of POP3 clients

Facter.add('p3scan_clients') do
    confine osfamily: 'RedHat'
    setcode do
        tmp = Facter::Core::Execution.exec('grep p3scan /var/log/messages | grep USER | awk \'{print $7}\' | sort | uniq | wc -l')
        tmp.to_i
    end
end
