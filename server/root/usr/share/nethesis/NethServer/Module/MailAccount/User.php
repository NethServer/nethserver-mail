<?php

namespace NethServer\Module\MailAccount;

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
 * Description of SharedMailbox
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class User extends \Nethgui\Controller\TableController
{
    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return new \NethServer\Tool\CustomModuleAttributesProvider($base, array(
            'languageCatalog' => array('NethServer_Module_MailAccount_User'))
        );
    }

    public function initialize()
    {
        $columns = array(
            'Key',
            'MailQuotaCustom',
            'MailSpamRetentionTime',
            'MailForwardAddress',
            'MailAccess',
            'Actions'
        );

        $this
            ->setTableAdapter(new User\MailboxAdapter($this->getPlatform()))
            ->setColumns($columns)
            ->addRowAction(new User\Modify('update'))
            ->addTableAction(new \Nethgui\Controller\Table\Help('Help'))

        ;
        parent::initialize();
    }

    public function prepareViewForColumnKey(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        if ($values['MailStatus'] == 'disabled' ) {
            $rowMetadata['rowCssClass'] = trim($rowMetadata['rowCssClass'] . ' user-locked');
        }
        return strval($key);
    }

    public function prepareViewForColumnMailAccess(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        if ($values['MailAccess'] == 'private' ) {
            return $view->translate('Yes');
        }
        return $view->translate('No');
    }

    public function prepareViewForColumnActions(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        if ($key != 'root') {
            return $action->prepareViewForColumnActions($view, $key, $values, $rowMetadata);
        }
        $cellView = $view->spawnView($this->getParent());
        $self = $this;
        $cellView->setTemplate(function(\Nethgui\Renderer\Xhtml $view) use ($self, $view) {
            return sprintf('<button class="Button" onclick="window.location=%s">%s</button>', htmlspecialchars(json_encode($view->getModuleUrl('/UserProfile'))), $view->translate('EditProfile_label'));
        });
        return $cellView;
    }

   public function prepareViewForColumnMailForwardAddress(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        if ($values['MailForwardStatus'] == 'enabled' ) {
            return str_replace(',', ', ', $values['MailForwardAddress']);
        }
        return '-';
    }

    public function prepareViewForColumnMailQuotaCustom(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        if ($values['MailQuotaType'] == 'custom') {
             $i = $values['MailQuotaCustom'];
             if ($i == 0) {
                 return $view->translate('Unlimited_quota');
             }
             return $i >= 10 ? (($i / 10.0) . ' GB') : (($i * 100) . ' MB');
        }
        return '-';
    }

    public function prepareViewForColumnMailSpamRetentionTime(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        if ($values['MailSpamRetentionStatus'] == 'enabled') {
             if ($values['MailSpamRetentionTime'] == 'infinite') {
                 return $view->translate('ever');
             } else {
                 return $view->translate('${0} days', array(substr($values['MailSpamRetentionTime'],0,-1)));
             }
        }
        return '-';
    }

}
