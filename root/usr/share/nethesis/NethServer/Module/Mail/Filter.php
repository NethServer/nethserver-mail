<?php
namespace NethServer\Module\Mail;

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

/**
 * Mail filter properties for Amavis
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Filter extends \Nethgui\Controller\AbstractController
{
    public $spamTagLevel;
    public $spamDsnLevel;
    public $attachmentClasses;

    public function initialize()
    {
        $attachmentClasses = array(
            'Exec',
            'Arch'
        );

        $this->attachmentClasses = $attachmentClasses;

        $this->spamTagLevel = $this->getPlatform()
            ->getDatabase('configuration')
            ->getProp('amavisd', 'SpamTagLevel')
        ;
        $this->spamDsnLevel = $this->getPlatform()
            ->getDatabase('configuration')
            ->getProp('amavisd', 'SpamDsnLevel')
        ;

        $this->declareParameter('VirusCheckStatus', Validate::SERVICESTATUS, array('configuration', 'amavisd', 'VirusCheckStatus'));
        $this->declareParameter('SpamCheckStatus', Validate::SERVICESTATUS, array('configuration', 'amavisd', 'SpamCheckStatus'));
        $this->declareParameter('BlockAttachmentStatus', Validate::SERVICESTATUS, array('configuration', 'amavisd', 'BlockAttachmentStatus'));
        $this->declareParameter('SpamSubjectPrefixStatus', Validate::SERVICESTATUS, array('configuration', 'amavisd', 'SpamSubjectPrefixStatus'));
        $this->declareParameter('SpamSubjectPrefixString', $this->createValidator()->maxLength(16), array('configuration', 'amavisd', 'SpamSubjectPrefixString'));
        $this->declareParameter('SpamTag2Level', $this->createValidator()->lessThan($this->spamDsnLevel)->greatThan($this->spamTagLevel), array('configuration', 'amavisd', 'SpamTag2Level'));
        $this->declareParameter('SpamKillLevel', $this->createValidator()->lessThan($this->spamDsnLevel)->greatThan($this->spamTagLevel), array('configuration', 'amavisd', 'SpamKillLevel'));
        $this->declareParameter('AddressAcl', Validate::ANYTHING, array(
            array('configuration', 'amavisd', 'RecipientWhiteList'),
            array('configuration', 'amavisd', 'SenderWhiteList'),
            array('configuration', 'amavisd', 'SenderBlackList'),
        ));

        $this->declareParameter('BlockAttachmentCustomStatus', Validate::SERVICESTATUS, array('configuration', 'amavisd', 'BlockAttachmentCustomStatus'));
        $this->declareParameter('BlockAttachmentCustomList', '/^[a-z0-9]+(,[a-z0-9]+)*$/', array('configuration', 'amavisd', 'BlockAttachmentCustomList'));
        $this->declareParameter('BlockAttachmentClassList', Validate::ANYTHING_COLLECTION, array('configuration', 'amavisd', 'BlockAttachmentClassList', ','));
    }

    public function readAddressAcl($recipientWhiteList, $senderWhiteList, $senderBlackList)
    {
        $addressAcl = '';

        // Append ACL suffix to each list:
        foreach (array('RW' => $recipientWhiteList, 'SW' => $senderWhiteList, 'SB' => $senderBlackList) as $acl => $list) {
            foreach (explode(',', $list) as $item) {
                $addressAcl .= $item ? ($item . ":" . $acl . "\r\n") : '';
            }
        }

        return $addressAcl;
    }

    public function writeAddressAcl($addressAcl)
    {
        $acls = array();

        foreach (explode("\n", $addressAcl) as $line) {
            $parts = array();
            if (preg_match('/^\s*([^:\s]+)\s*:\s*([^\s]+)\s*$/', $line, $parts) > 0) {
                $acls[$parts[2]][] = $parts[1];
            }
        }

        return array(
            // $recipientWhiteList:
            isset($acls['RW']) ? implode(',', array_unique($acls['RW'])) : '',
            // $senderWhiteList:
            isset($acls['SW']) ? implode(',', array_unique($acls['SW'])) : '',
            // $senderBlackList:
            isset($acls['SB']) ? implode(',', array_unique($acls['SB'])) : ''
        );
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        $this->getValidator('SpamTag2Level')->lessThan($this->parameters['SpamKillLevel']);

        $message = '';
        $args = array();

        if ($this->validateAddressAcl($message, $args) === FALSE) {
            $report->addValidationErrorMessage($this, 'AddressAcl', $message, $args);
        }

        parent::validate($report);
    }

    private function validateAddressAcl(&$message, &$args)
    {
        $lines = explode("\n", $this->parameters['AddressAcl']);

        $addressValidator = $this->createValidator()->orValidator(
            $this->createValidator(Validate::EMAIL), $this->createValidator(Validate::HOSTNAME)
        );

        $typeValidator = $this->createValidator()->memberOf('SW', 'SB', 'RW');

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            $fields = array_map('trim', explode(":", $line));

            if ( ! $addressValidator->evaluate($fields[0])) {
                $message = '"${0}" is not an email address or host name';
                $args[0] = $fields[0];
                return FALSE;
            }

            if ( ! $typeValidator->evaluate($fields[1])) {
                $message = '"${0}" is not a valid record type';
                $args[0] = $fields[1];
                return FALSE;
            }
        }

        return TRUE;
    }

    protected function onParametersSaved($changedParameters)
    {
        $this->getPlatform()->signalEvent('nethserver-mail-filter-save@post-process');
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        $view['BlockAttachmentClassListDatasource'] = array_map(function($ac) use ($view) {
            return array($ac, $view->translate($ac . '_label'));
        }, $this->attachmentClasses);                
        
    }

}
