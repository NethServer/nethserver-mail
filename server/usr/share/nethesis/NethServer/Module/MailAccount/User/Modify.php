<?php
namespace NethServer\Module\MailAccount\User;

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
 * CRUD actions on user mailboxes records
 *
 */
class Modify extends \Nethgui\Controller\Table\Modify
{

    public function initialize()
    {
        $quotaValidator1 = $this->createValidator()->greatThan(0)->lessThan(1001);
        $quotaValidator2 = $this->createValidator()->equalTo('unlimited');

        $parameterSchema = array(
            array('username', Validate::ANYTHING, Table::KEY),
            array('MailStatus', Validate::SERVICESTATUS, Table::FIELD),
            array('MailAccess', $this->createValidator()->memberOf('public', 'private'), Table::FIELD),
            array('MailQuotaType', $this->createValidator()->memberOf('custom', 'default'), Table::FIELD),
            array('MailQuotaCustom', $this->createValidator()->orValidator($quotaValidator1, $quotaValidator2), Table::FIELD),
            array('MailForwardStatus', Validate::SERVICESTATUS, Table::FIELD),
            array('MailForwardAddress', Validate::NOTEMPTY, Table::FIELD),
            array('MailForwardKeepMessageCopy', Validate::YES_NO, Table::FIELD),
            array('MailSpamRetentionStatus', Validate::SERVICESTATUS, Table::FIELD),
            array('MailSpamRetentionTime', '/^(\d+[smhdw]|infinite)$/', Table::FIELD),
        );
        $this->setSchema($parameterSchema);

        parent::initialize();
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        if($request->isMutation() && $request->hasParameter('MailForwardAddress')) {
            $this->parameters['MailForwardAddress'] = implode(",", self::splitLines($request->getParameter('MailForwardAddress')));
        }
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        parent::validate($report);
        if($this->getRequest()->isMutation()) {
            if($this->getPlatform()->getDatabase('accounts')->getType($this->parameters['username']) === 'pseudonym') {
                $report->addValidationErrorMessage($this, 'username',
                    'valid_mailbox_pseudonym_conflict');
            }
            $forwards = $this->parameters['MailForwardAddress'];
            if($forwards) {
                $emailValidator = $this->createValidator(Validate::EMAIL);
                foreach(explode(',', $forwards) as $email) {
                    if( !$emailValidator->evaluate($email)) {
                        $report->addValidationErrorMessage($this, 'MailForwardAddress',
                            'valid_mailforward_address', array($email));
                    }
                }
            }
        }
    }

    private function saveProps()
    {
        $props = array();
        $db = $this->getPlatform()->getDatabase('accounts');
        $user = $this->parameters['username'];
        foreach ($this->parameters as $prop => $value) {
            if ($prop == 'username') {
                continue;
            }
            $props[$prop] = $value;
        }
        $db->setKey($user, 'user', $props);
    }

    public function process()
    {
        if ($this->getRequest()->isMutation()) {
             $this->saveProps();
             $this->getParent()->getAdapter()->flush();
             $this->getPlatform()->signalEvent('mailbox-save', array($this->parameters['username']));
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $h = \NethServer\Module\Mail\Mailbox::getQuotaUiFunction($this->getPlatform()->getDatabase('configuration'));
        $h['unlimited'] = $view->translate('Unlimited_quota');
        $view['MailQuotaCustomDatasource'] = \Nethgui\Renderer\AbstractRenderer::hashToDatasource($h);
        $view['MailSpamRetentionTimeDatasource'] = \Nethgui\Renderer\AbstractRenderer::hashToDatasource(array(
                '1d' => $view->translate('${0} day', array(1)),
                '2d' => $view->translate('${0} days', array(2)),
                '4d' => $view->translate('${0} days', array(4)),
                '7d' => $view->translate('${0} days', array(7)),
                '15d' => $view->translate('${0} days', array(15)),
                '30d' => $view->translate('${0} days', array(30)),
                '60d' => $view->translate('${0} days', array(60)),
                '90d' => $view->translate('${0} days', array(90)),
                '180d' => $view->translate('${0} days', array(180)),
                'infinite' => $view->translate('ever'),
            ));
        if(isset($this->parameters['MailForwardAddress'])) {
            $view['MailForwardAddress'] = implode("\r\n", explode(',', $this->parameters['MailForwardAddress']));
        }
    }

    public static function splitLines($text)
    {
        return array_filter(preg_split("/[,;\s]+/", $text));
    }

}
