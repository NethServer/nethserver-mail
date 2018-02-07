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

use Nethgui\System\PlatformInterface as Validate;

/**
 * Create and modify a SharedMailbox
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class Edit extends \Nethgui\Controller\Table\AbstractAction
{

    private $others = array();

    public function initialize()
    {
        parent::initialize();
        $keyValidator = $this->createValidator()
                        ->orValidator(
                    $this->createValidator()->regexp('/^[A-Za-z0-9_-](\.?[A-Za-z0-9_-]+)*@$/i'),
                    $this->createValidator()->email()
                        )->maxLength(196);

        $ownersAdapter = $this->getPlatform()->getMapAdapter(array($this, 'readOwners'), NULL, array());

        $this->declareParameter('Name', Validate::NOTEMPTY);
        $this->declareParameter('NewName', FALSE);
        $this->declareParameter('CreateAlias', Validate::SERVICESTATUS);
        $this->declareParameter('localAddress', Validate::ANYTHING);
        $this->declareParameter('domainAddress', Validate::ANYTHING);
        $this->declareParameter('pseudonym',$keyValidator);
        $this->declareParameter('Owners', $this->createValidator()->collectionValidator($this->createValidator(Validate::NOTEMPTY)), $ownersAdapter);
    }

    protected function calculateKeyFromRequest(\Nethgui\Controller\RequestInterface $request)
    {
        return trim($request->getParameter('localAddress')) . '@' . $request->getParameter('domainAddress');
    }

    public function readOwners()
    {
        static $owners;

        if (isset($owners)) {
            return $owners;
        }

        $proc = $this->getPlatform()->exec('/usr/bin/sudo /usr/bin/doveadm -f tab acl get -u vmail ${@}', array($this->parameters['Name']));

        if ($proc->getExitCode() !== 0 || $proc->getOutput() === NULL) {
            return array();
        }

        $owners = array();
        $others = array();
        foreach (\Nethgui\array_rest($proc->getOutputArray()) as $line) {
            list($id, $global, $rights) = explode("\t", $line);
            if ($rights === 'create expunge insert lookup read write write-deleted write-seen') {
                $owners[] = str_replace('group=', '', $id);
            } else {
                $this->others[] = preg_replace('/^(group=|user=)/', '', $id) . sprintf(' (%s)', $rights);
            }
        }

        return $owners;
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        if ($this->getIdentifier() !== 'create') {
            $this->parameters['Name'] = \Nethgui\array_end($request->getPath());
        }
        if ($this->getRequest()->isMutation()) {
            $this->parameters['NewName'] = $this->getRequest()->getParameter('Name');
            $this->parameters['pseudonym'] = trim($this->getRequest()->getParameter('localAddress')) . '@' . $this->getRequest()->getParameter('domainAddress');
        }
    }

    private function getChangeEventArgs()
    {
        $args = array($this->parameters['Name'], $this->parameters['NewName']);

        $owners = $this->readOwners();

        foreach ($this->parameters['Owners'] as $o) {
            $args[] = 'group=' . $o;
            $args[] = 'OWNER';
        }

        foreach (array_diff($owners, $this->parameters['Owners']) as $o) {
            $args[] = 'group=' . $o;
            $args[] = 'CLEAR';
        }

        return $args;
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        parent::validate($report);
        // we must explicitly validate the pseudonym parameter because is not posted with create request
        if ($this->getRequest()->isMutation() && $this->getIdentifier() === 'create' && $this->parameters['CreateAlias'] === 'enabled') {
            if ($this->getValidator('pseudonym')->evaluate($this->parameters['pseudonym']) !== TRUE) {
                $report->addValidationErrorMessage($this, 'localAddress',
                    'valid_email,malformed-localpart');
            } elseif ($this->getIdentifier() === 'create' && $this->getParent()->getAdapter()->offsetExists($this->parameters['pseudonym'])) {
                $report->addValidationErrorMessage($this, 'localAddress',
                    'valid_pseudonym_unique');
            }
        }
    }

    public function process()
    {
        if ( ! $this->getRequest()->isMutation()) {
            return;
        }
        if ($this->getIdentifier() === 'create') {
            $this->getPlatform()->signalEvent('sharedmailbox-create', $this->getChangeEventArgs());
            /*Create Alias*/
            if ($this->parameters['CreateAlias']==='enabled')
            {
                $this->getPlatform()->getDatabase('accounts')->setKey($this->parameters['pseudonym'],'pseudonym',array(
                   'Access' => 'public',
                   'Account' => 'vmail+'.$this->parameters['Name'],
                   'Description' => 'Shared mailbox'
               ));
               $this->getPlatform()->signalEvent('pseudonym-create', array($this->parameters['pseudonym']));
            }
        } elseif ($this->getIdentifier() === 'update') {
            $this->getPlatform()->signalEvent('sharedmailbox-modify', $this->getChangeEventArgs());
        } elseif ($this->getIdentifier() === 'delete') {
            $this->getPlatform()->signalEvent('sharedmailbox-delete', array($this->parameters['Name']));
        }

        // re-read the shared mailbox list:
        $this->getParent()->getAdapter()->flush();
    }

    private function getOwnersDatasource(\Nethgui\View\ViewInterface $view)
    {
        $gp = new \NethServer\Tool\GroupProvider($this->getPlatform());
        return array_map(function ($x) {
            return array($x, $x);
        }, array_keys($gp->getGroups()));
    }

    private function readDomainAddressDatasource(\Nethgui\View\ViewInterface $view)
    {
        $domains = array(array('', $view->translate('ANY_DOMAIN')));

        foreach ($this->getPlatform()->getDatabase('domains')->getAll('domain') as $key => $prop) {
            $domains[] = array($key, $key);
        }

        return $domains;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if ($this->getIdentifier() === 'create') {
            $view['OwnersDatasource'] = $this->getOwnersDatasource($view);
            $view->setTemplate('NethServer\Template\MailAccount\SharedMailbox\Edit');
            $view['domainAddressDatasource'] = $this->readDomainAddressDatasource($view);
        } elseif ($this->getIdentifier() === 'delete') {
            $view['__key'] = 'Name';  // tell what is the key parameter
            $view->setTemplate('Nethgui\Template\Table\Delete');
        } elseif ($this->getIdentifier() === 'update') {
            $view['OwnersDatasource'] = $this->getOwnersDatasource($view);
            $view['Others'] = $this->others ? $this->others : $view->translate("no_special_access_label");
            $view->setTemplate('NethServer\Template\MailAccount\SharedMailbox\Edit');
        }
        if ($this->getRequest()->isMutation()) {
            if ($this->getRequest()->isValidated() && $this->getIdentifier() === 'create' && $this->parameters['CreateAlias'] === 'enabled') {
                $view->getCommandList()->sendQuery($view->getModuleUrl('/MailAccount/Pseudonym'));
            }
        } else {
            $this->parameters['CreateAlias'] = 'enabled';
        }
    }

}
