<?php

namespace NethServer\Module\MailAccount\Pseudonym;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

use Nethgui\System\PlatformInterface as Validate;
use Nethgui\Controller\Table\Modify as Table;

/**
 * CRUD actions on Pseudonym records
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Modify extends \Nethgui\Controller\Table\Modify
{

    public function initialize()
    {
        $keyValidator = $this->createValidator()
                        ->orValidator(
                    $this->createValidator()->regexp('/^[A-Za-z0-9_-](\.?[A-Za-z0-9_-]+)*@$/i'),
                    $this->createValidator()->email()
                        )->maxLength(196);

        $parameterSchema = array(
            array('pseudonym', $keyValidator, Table::KEY),
            array('Description', Validate::ANYTHING, Table::FIELD),
            array('Account', Validate::ANYTHING, Table::FIELD, 'Account', ','),
            array('Access', $this->createValidator()->memberOf('public',
                    'private'), Table::FIELD),
        );

        $this->setSchema($parameterSchema);

        $this->declareParameter('localAddress', Validate::NOTEMPTY);
        $this->declareParameter('domainAddress', Validate::ANYTHING);
        $this->declareParameter('ExtAddresses', '/^[^\|]*$/');
        $this->setDefaultValue('localAddress', '');
        $this->setDefaultValue('domainAddress', '');

        parent::initialize();
    }

    protected function calculateKeyFromRequest(\Nethgui\Controller\RequestInterface $request)
    {
        return trim($request->getParameter('localAddress')) . '@' . $request->getParameter('domainAddress');
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        $extAddresses = $this->getExtAddresses();
        if(count($extAddresses) > 0) {
            $this->parameters['Account'] = array_merge($this->parameters['Account']->getArrayCopy(), $extAddresses);
        }
    }

    public function mailboxExists($pseudonym)
    {
        $process = $this->getPlatform()->exec(sprintf('/usr/bin/sudo /usr/libexec/nethserver/list-users %s', escapeshellarg($pseudonym)));
        if($process->getExitCode() !== 0) {
            throw new \RuntimeException(sprintf('%s: user account existence check failed', __CLASS__), 1485948162);
        }
        $output = trim($process->getOutput());
        return $output !== '{}' && $output !== '';
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        parent::validate($report);
        // we must explicitly validate the pseudonym parameter because is not posted with create request
        if ($this->getRequest()->isMutation() && $this->getIdentifier() === 'create') {
            if ($this->getValidator('pseudonym')->evaluate($this->parameters['pseudonym']) !== TRUE) {
                $report->addValidationErrorMessage($this, 'localAddress',
                    'valid_email,malformed-localpart');
            } elseif ($this->getIdentifier() === 'create' && $this->getParent()->getAdapter()->offsetExists($this->parameters['pseudonym'])) {
                $report->addValidationErrorMessage($this, 'localAddress',
                    'valid_pseudonym_unique');
            } elseif ($this->mailboxExists($this->parameters['pseudonym'])) {
                $report->addValidationErrorMessage($this, 'localAddress',
                    'valid_pseudonym_mailbox_conflict');
            }
        }
        if($this->getRequest()->isMutation()) {
            if(count($this->parameters['Account']) === 0) {
                $report->addValidationErrorMessage($this, 'Account',
                    'valid_pseudonym_empty_destination');
            }
        }
    }

    public function process()
    {
        if($this->getRequest()->isMutation()) {
            // Expand the member list of each group
            $groupProvider = new \NethServer\Tool\GroupProvider($this->getPlatform());
            $groupList = $groupProvider->getGroups();
            $pseudonyms = $this->getParent()->getAdapter();
            $destinations = array();
            foreach($this->parameters['Account'] as $destination) {
                $isGroup = in_array($destination, array_keys($groupList));
                $isPseudonym = isset($pseudonyms[$destination])
                    || isset($pseudonyms[substr($destination, 0, 1 + strpos($destination, '@'))]);
                if( !$isPseudonym && $isGroup) {
                    $destinations = array_merge($destinations, $groupProvider->getGroupMembers($destination));
                } else {
                    $destinations[] = $destination;
                }
            }
            $this->parameters['Account'] = array_unique($destinations);
        }
        parent::process();
    }

    public function getExtAddresses()
    {
        $addresses = array();
        foreach (preg_split("/[,;\s]+/", $this->parameters['ExtAddresses']) as $line) {
            $address = trim($line);
            if($address) {
                $addresses[] = $address;
            }
        }
        return $addresses;
    }

    public function getAccountDatasource(\Nethgui\View\ViewInterface $view)
    {
        $userProvider = new \NethServer\Tool\UserProvider($this->getPlatform());
        $groupProvider = new \NethServer\Tool\GroupProvider($this->getPlatform());
        $mbxProvider = new \NethServer\Module\MailAccount\SharedMailbox\SharedMailboxAdapter($this->getPlatform());

        $users = $userProvider->getUsers();

        $hash = array(
            'root' => 'root'
        );

        if ($this->parameters['Account'] instanceof \Traversable) {
            foreach ($this->parameters['Account'] as $a) {
                $hash[$a] = $a;
            }
        }

        foreach ($users as $key => $prop) {
            $hash[$key] = $key;
        }

        foreach ($groupProvider->getGroups() as $key => $prop) {
            $hash[$key] = $key;
        }

        foreach ($mbxProvider->getSharedMailboxList() as $mbx) {
            $hash['vmail+' . $mbx['name']] = $view->translate('SharedMailbox_selector_label', array($mbx['name']));
        }

        return \Nethgui\Widget\XhtmlWidget::hashToDatasource($hash, TRUE);
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $templates = array(
            'create' => 'NethServer\Template\Pseudonym\Modify',
            'update' => 'NethServer\Template\Pseudonym\Modify',
            'delete' => 'Nethgui\Template\Table\Delete',
        );
        $view->setTemplate($templates[$this->getIdentifier()]);

        if ( ! $this->getRequest()->isMutation() && $this->getRequest()->isValidated()) {
            $view['AccountDatasource'] = $this->getAccountDatasource($view);
            if ($this->getIdentifier() === 'create') {
                $view['domainAddressDatasource'] = $this->readDomainAddressDatasource($view);
            }
        }
    }

    /**
     * Do not really delete the record, but change its type.
     * @param string $key
     */
    protected function processDelete($key)
    {
        $accountDb = $this->getPlatform()->getDatabase('accounts');
        $accountDb->setType($key, 'pseudonym-deleted');
        $deleteProcess = $this->getPlatform()->signalEvent('pseudonym-delete',
            array($key));
        if ($deleteProcess->getExitCode() === 0) {
            parent::processDelete($key);
        }
    }

    public function onParametersSaved($changedParameters)
    {
        if ($this->getIdentifier() === 'update') {
            $event = 'modify';
        } elseif ($this->getIdentifier() === 'delete') {
            // pseudonym-delete event is rasied by processDelete() method
            return;
        } else {
            $event = $this->getIdentifier();
        }
        $this->getPlatform()->signalEvent(sprintf('pseudonym-%s@post-process',
                $event), array($this->parameters['pseudonym']));
    }

    private function readDomainAddressDatasource(\Nethgui\View\ViewInterface $view)
    {
        $domains = array(array('', $view->translate('ANY_DOMAIN')));

        foreach ($this->getPlatform()->getDatabase('domains')->getAll('domain') as $key => $prop) {
            $domains[] = array($key, $key);
        }

        return $domains;
    }

}
