<?php
namespace NethServer\Module\MailAccount\User;

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
 * List mailboxes
 *
 * @author Giacomo Sanchietti <giacomo.sanchietti@nethesis.it>
 */
class MailboxAdapter extends \Nethgui\Adapter\LazyLoaderAdapter
{
    private $platform;
    private $provider;
    private $accounts = array();
    
    private $defaults = array (
        'MailStatus' => 'enabled',
        'MailAccess' => 'public',
        'MailQuotaType' => 'default',
        'MailQuotaCustom' => '1',
        'MailForwardStatus'=> 'disabled',
        'MailForwardAddress' => '',
        'MailForwardKeepMessageCopy' => 'no',
        'MailSpamRetentionStatus' => 'disabled',
        'MailSpamRetentionTime' => '180d'
    );

    private function getValue($user, $prop)
    {
        if (isset($this->accounts[$user][$prop])) {
            return $this->accounts[$user][$prop];
        } else {
            return $this->defaults[$prop];
        }
    }


    public function __construct(\Nethgui\System\PlatformInterface $platform)
    {
        $this->platform = $platform;
        $this->provider = new \NethServer\Tool\UserProvider($this->platform);
        $this->users = $this->provider->getUsers();
        $this->defaults['MailQuotaCustom'] = $platform->getDatabase('configuration')->getProp('dovecot', 'QuotaDefaultSize');
        parent::__construct(array($this, 'readMailboxes'));
    }

    public function flush()
    {
        $this->data = NULL;
        return $this;
    }

    public function readMailboxes()
    {
        $loader = new \ArrayObject();
        $this->accounts = $this->platform->getDatabase('accounts')->getAll('user');
        foreach ($this->users as $user => $values) {
            $loader[$user]['username'] = $user;
            foreach (array_keys($this->defaults) as $prop) {
                $loader[$user][$prop] = $this->getValue($user, $prop);
            }
        }

        // Add root mailbox configuration:
        $loader['root'] = $this->defaults;
        $loader['root']['username'] = 'root';
        $confDb = $this->platform->getDatabase('configuration');
        $rootEmailAddress = $confDb->getProp('root', 'EmailAddress');
        if($rootEmailAddress) {
            $loader['root']['MailForwardStatus'] = 'enabled';
            $loader['root']['MailForwardAddress'] = $rootEmailAddress;
        }
        $loader['root']['MailForwardKeepMessageCopy'] = $confDb->getProp('root', 'KeepMessageCopy');

        return $loader;
    }

}
