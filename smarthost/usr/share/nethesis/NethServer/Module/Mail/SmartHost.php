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
 * Change queue SmartHost properties
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class SmartHost extends \Nethgui\Controller\AbstractController
{

    public function initialize()
    {
        $this->declareParameter('SmartHostStatus', Validate::SERVICESTATUS, array('configuration', 'postfix', 'SmartHostStatus'));
        $this->declareParameter('SmartHostName', Validate::HOSTNAME, array('configuration', 'postfix', 'SmartHostName'));
        $this->declareParameter('SmartHostPort', Validate::PORTNUMBER, array('configuration', 'postfix', 'SmartHostPort'));
        $this->declareParameter('SmartHostUsername', Validate::ANYTHING, array('configuration', 'postfix', 'SmartHostUsername'));
        $this->declareParameter('SmartHostPassword', Validate::ANYTHING, array('configuration', 'postfix', 'SmartHostPassword'));
        $this->declareParameter('SmartHostTlsStatus', Validate::SERVICESTATUS, array('configuration', 'postfix', 'SmartHostTlsStatus'));
        parent::initialize();
    }


    protected function onParametersSaved($changedParameters)
    {
        $this->getPlatform()->signalEvent('nethserver-mail-smarthost-save &');
    }

}
