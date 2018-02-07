<?php
namespace NethServer\Module\Mail\Domain\PlugTransport;

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
 * The "LocalDelivery" option delivers message to the local mail server
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class LocalDelivery extends \Nethgui\Controller\Table\RowPluginAction
{

    public function initialize()
    {
        $this->setSchemaAddition(array(
            array('UnknownRecipientsActionType', $this->createValidator()->memberOf('bounce', 'deliver'), Table::FIELD),
            array('UnknownRecipientsActionDeliverMailbox', Validate::ANYTHING, Table::FIELD),
            array('AlwaysBccStatus', Validate::SERVICESTATUS, Table::FIELD),
            array('AlwaysBccAddress', Validate::EMAIL, Table::FIELD),
        ));

        $this->setDefaultValue('UnknownRecipientsActionType', 'bounce');
        $this->setDefaultValue('AlwaysBccStatus', 'disabled');

        parent::initialize();
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        if($this->getRequest()->isMutation() && $this->parameters['UnknownRecipientsActionType'] === 'deliver') {
            $domainName = \Nethgui\array_end(explode('.', \gethostname(), 2));
            $domainKey = $this->getAdapter()->getKeyValue();
            if($domainName === $domainKey) {
                if( ! preg_match("/^[^@]+(@${domainName})?$/", $this->parameters['UnknownRecipientsActionDeliverMailbox']) ) {
                    $report->addValidationErrorMessage($this, 'UnknownRecipientsActionDeliverMailbox', 'valid_catchall_mailbox_primary', array($domainName));
                }
            } elseif( $this->parameters['UnknownRecipientsActionDeliverMailbox'] !== 'root' && substr($this->parameters['UnknownRecipientsActionDeliverMailbox'], 0, 6) !== 'vmail+' ) {
                $v = $this->createValidator()->email();
                if( ! $v->evaluate($this->parameters['UnknownRecipientsActionDeliverMailbox'])) {
                    $report->addValidationError($this, 'UnknownRecipientsActionDeliverMailbox', $v);
                }
            }
        }
        parent::validate($report);
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $mailboxesDatasource = array();
        foreach($this->getLocalMailboxes() as $mbx) {
            if(substr($mbx, 0, 6) === 'vmail+') {
                $mailboxesDatasource[] = array($mbx, $view->translate('SharedMailbox_selector_label', array(substr($mbx, 6))));
            } else {
                $mailboxesDatasource[] = array($mbx, $mbx);
            }
        }
        $view['UnknownRecipientsActionDeliverMailboxDatasource'] = $mailboxesDatasource;
    }

    private function getLocalMailboxes()
    {
        $userProvider = new \NethServer\Tool\UserProvider($this->getPlatform());
        $mbxProvider = new \NethServer\Module\MailAccount\SharedMailbox\SharedMailboxAdapter($this->getPlatform());
        $mailboxes = array('root');

        $users = $userProvider->getUsers();
        ksort($users);

        foreach ($users as $key => $prop) {
            $mailboxes[] = $key;
        }

        foreach ($mbxProvider->getSharedMailboxList() as $mbx) {
            $mailboxes[] = 'vmail+' . $mbx['name'];
        }

        if(isset($this->parameters['UnknownRecipientsActionDeliverMailbox']) && $this->parameters['UnknownRecipientsActionDeliverMailbox'] && ! in_array($this->parameters['UnknownRecipientsActionDeliverMailbox'], $mailboxes))  {
            array_unshift($mailboxes, $this->parameters['UnknownRecipientsActionDeliverMailbox']);
        }

        return $mailboxes;
    }
}
