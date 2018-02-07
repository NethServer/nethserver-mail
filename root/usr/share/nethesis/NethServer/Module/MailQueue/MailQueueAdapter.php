<?php
namespace NethServer\Module\MailQueue;

/*
 * Copyright (C) 2016 Nethesis Srl
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of MailQueueAdapter
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class MailQueueAdapter extends \Nethgui\Adapter\LazyLoaderAdapter
{
    /**
     *
     * @var \Nethgui\System\PlatformInterface
     */
    private $platform;

    public function __construct(\Nethgui\System\PlatformInterface $platform)
    {
        $this->platform = $platform;
        parent::__construct(array($this, 'readMailQueue'));
    }

    public function flush()
    {
        $this->data = NULL;
        return $this;
    }

    public function readMailQueue()
    {
        $messages = array();

        $process = $this->platform->exec('/usr/bin/sudo /usr/sbin/postqueue -p | head -n 4000 | /usr/libexec/nethserver/mailq2json');
        if ($process->getExitCode() == 0) {
            $messages = json_decode($process->getOutput(), TRUE);
        } else {
            $this->getLog()->error(sprintf("%s: postqueue -f command failed - %s", __CLASS__, $process->getOutput()));
        }

        $data = new \ArrayObject();

        foreach ($messages as $message) {

            $recipients = $this->getAllRecipients($message);

            $row = array(
                'Id' => $message['id'],
                'Sender' => $message['sender'],
                'Status' => $message['status'],
                'Size' => $this->formatSize($message['size']),
                'Timestamp' => $message['time'],
                'Recipients' => $recipients,
                'RecipientsCount' => (string) count($recipients),
                'Problems' => array_keys($message['reasons'])
            );

            $data[$message['id']] = $row;
        }

        return $data;
    }

    private function getAllRecipients($message)
    {
        $recipients = $message['recipients'];
        foreach ($message['reasons'] as $r) {
            $recipients = array_merge($recipients, $r);
        }
        return $recipients;
    }

    private function formatSize($size)
    {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size > 1024; $i ++ ) {
            $size /= 1024;
        }
        return round($size, 2) . $units[$i];
    }

}
