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
 * Change queue Message properties
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Message extends \Nethgui\Controller\AbstractController
{

    public function initialize()
    {
        $messageSizeMaxAdapter = $this->getPlatform()->getMapAdapter(
            function($s) {
                return intval($s / 1000000);
            }, function($v) {
                return array($v * 1000000);
            }, array(array('configuration', 'postfix', 'MessageSizeMax'))
        );

        $this->declareParameter('MessageSizeMax', $this->createValidator(Validate::POSITIVE_INTEGER)->lessThan(1001), $messageSizeMaxAdapter);
        $this->declareParameter('MessageQueueLifetime', $this->createValidator(Validate::POSITIVE_INTEGER)->lessThan(31), array('configuration', 'postfix', 'MessageQueueLifetime'));
        $this->declareParameter('AlwaysBccStatus', Validate::SERVICESTATUS, array('configuration', 'postfix', 'AlwaysBccStatus'));
        $this->declareParameter('AlwaysBccAddress', Validate::EMAIL, array('configuration', 'postfix', 'AlwaysBccAddress'));
        parent::initialize();
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        $sz = array(
            '10' => '10 MB',
            '20' => '20 MB',
            '50' => '50 MB',
            '100' => '100 MB',
            '200' => '200 MB',
            '500' => '500 MB',
            '1000' => '1 GB',
            $this->parameters['MessageSizeMax'] => sprintf('%s MB', $this->parameters['MessageSizeMax'])
        );
        ksort($sz, SORT_NUMERIC);

        $view['MessageSizeMaxDatasource'] = \Nethgui\Renderer\AbstractRenderer::hashToDatasource($sz);

        $lt = array(
            '1' => $view->translate('${0} day', array(1)),
            '2' => $view->translate('${0} days', array(2)),
            '4' => $view->translate('${0} days', array(4)),
            '7' => $view->translate('${0} days', array(7)),
            '15' => $view->translate('${0} days', array(15)),
            '30' => $view->translate('${0} days', array(30)),
            $this->parameters['MessageQueueLifetime'] => $view->translate('${0} days', array($this->parameters['MessageQueueLifetime']))
        );
        ksort($lt, SORT_NUMERIC);

        $view['MessageQueueLifetimeDatasource'] = \Nethgui\Renderer\AbstractRenderer::hashToDatasource($lt);

        parent::prepareView($view);
    }

    protected function onParametersSaved($changedParameters)
    {
        $this->getPlatform()->signalEvent('nethserver-mail-common-save &');
    }

}
