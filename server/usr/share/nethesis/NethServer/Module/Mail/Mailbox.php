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
use Nethgui\Controller\Table\Modify as Table;

/**
 * Change mailbox access parameters
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Mailbox extends \Nethgui\Controller\AbstractController
{

    public function initialize()
    {
        $this->declareParameter('ImapStatus', Validate::SERVICESTATUS, array('configuration', 'dovecot', 'ImapStatus'));
        $this->declareParameter('PopStatus', Validate::SERVICESTATUS, array('configuration', 'dovecot', 'PopStatus'));
        $this->declareParameter('TlsSecurity', '/^(required|optional)$/', array('configuration', 'dovecot', 'TlsSecurity'));
        $this->declareParameter('QuotaStatus', Validate::SERVICESTATUS, array('configuration', 'dovecot', 'QuotaStatus'));
        $this->declareParameter('QuotaDefaultSize', Validate::POSITIVE_INTEGER, array('configuration', 'dovecot', 'QuotaDefaultSize'));
        $this->declareParameter('SpamRetentionTime', '/^(\d+[smhdw]|infinite)$/', array('configuration', 'dovecot', 'SpamRetentionTime'));
        $this->declareParameter('SpamFolder', $this->createValidator()->memberOf('', 'Junk', 'junkmail'), array('configuration', 'dovecot', 'SpamFolder'));
        $this->declareParameter('AdminIsMaster',  Validate::SERVICESTATUS, array('configuration', 'dovecot', 'AdminIsMaster'));
        parent::initialize();
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $h = self::getQuotaUiFunction($this->getPlatform()->getDatabase('configuration'));
        $view['QuotaDefaultSizeDatasource'] = \Nethgui\Renderer\AbstractRenderer::hashToDatasource($h);

        // Value correspoding to the "enabled" checkbox state:
        $view['SpamFolderTarget'] = $view['SpamFolder'] ? $view['SpamFolder'] : 'Junk';

        $view['SpamRetentionTimeDatasource'] = \Nethgui\Renderer\AbstractRenderer::hashToDatasource(array(
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
    }

    protected function onParametersSaved($changedParameters)
    {
        $this->getPlatform()->signalEvent('nethserver-mail-server-save@post-process');
    }

    public static function getQuotaUiFunction(\Nethgui\System\DatabaseInterface $configDb)
    {
        $increments = array_unique(array_filter(array(1, 2, 3, 4, 5, 6, 7, 8, 9,
            10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 25, 30, 35, 40, 45, 50,
            100, 150, 200, 250, 300, 350, 400, 450, 500, 600, 700, 800, 900, 1000,
            $configDb->getProp('dovecot', 'QuotaDefaultSize')), function ($x) {
                return (integer) $x >= 1;
            }));
        sort($increments);
        $h = array();
        foreach ($increments as $i) {
            $h[$i] = $i >= 10 ? (($i / 10.0) . ' GB') : (($i * 100) . ' MB');
        }
        return $h;
    }

}