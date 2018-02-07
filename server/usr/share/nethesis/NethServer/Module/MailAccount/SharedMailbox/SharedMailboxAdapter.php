<?php
namespace NethServer\Module\MailAccount\SharedMailbox;

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
 * Description of SharedMailboxAdapter
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class SharedMailboxAdapter extends \Nethgui\Adapter\LazyLoaderAdapter
{
    /**
     *
     * @var \Nethgui\System\PlatformInterface
     */
    private $platform;

    public function __construct(\Nethgui\System\PlatformInterface $p)
    {
        $this->platform = $p;
        parent::__construct(array($this, 'getSharedMailboxList'));
    }

    public function getSharedMailboxList()
    {
        $mailboxes = array();
        $proc = $this->platform->exec('/usr/bin/sudo /usr/bin/doveadm ${@}', explode(' ', 'mailbox list -u vmail Public/*'));        
        if($proc->getExitCode() !== 0 || $proc->getOutput() === NULL) {
            return new \ArrayObject();
        }
        foreach(array_filter($proc->getOutputArray()) as $mb) {
            $key = substr($mb, 7); // trim Public/ prefix
            if(strpos($key, '/') !== FALSE) {
                continue;
            }
            $mailboxes[$key] = array('name' => $key);
        }
        ksort($mailboxes);
        return new \ArrayObject($mailboxes);
    }

    public function flush()
    {
        $this->data = NULL;
        return $this;
    }
}
