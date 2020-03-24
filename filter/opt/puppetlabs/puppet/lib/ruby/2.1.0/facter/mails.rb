#
# mails
# Count the number of mails
#

require 'date'

Facter.add('mails') do
    confine osfamily: 'RedHat'
    setcode do
        file = File.foreach("/var/log/maillog")
        yesterday = Time.now - (24*60*60)
        mails = { "total" => 0, "attachments" => 0, "received" => 0, "sent" => 0}
        file.each_entry do |line|
            date = DateTime.parse(line[0..14])
            if date.to_time >= yesterday
                if line.include? "HAS_ATTACHMENT"
                    mails['attachments'] = mails['attachments'] + 1;
                end

                if line.include? "status=sent"
                    mails['total'] = mails['total']+1
                    if line.match('postfix\/lmtp\[.*status=sent')
                        mails['received'] = mails['received']+1
                    elsif line.match('postfix\/smtp\[.*status=sent')
                        mails['sent'] = mails['sent']+1
                    end
                end
            end
        end
        mails
    end
end
