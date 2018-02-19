<?php
namespace NethServer\Module;

/*
 * Copyright (C) 2011 Nethesis S.r.l.
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
 * Change configuration of P3Scan
 *
 * @author Giacomo Sanchietti<giacomo.sanchietti@nethesis.it>
 */
class P3Scan extends \Nethgui\Controller\AbstractController
{
    private $templates = NULL;

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return \Nethgui\Module\SimpleModuleAttributesProvider::extendModuleAttributes($base, 'Configuration', 10);
    }

    public function initialize()
    {
        parent::initialize();

        if (!$this->templates) {
            $this->templates = $this->readTemplates();
        }
        $this->declareParameter('status', Validate::SERVICESTATUS, array('configuration', 'p3scan','status'));
        $this->declareParameter('SSLScan', Validate::SERVICESTATUS, array('configuration', 'p3scan','SSLScan'));
        $this->declareParameter('VirusScan', Validate::SERVICESTATUS, array('configuration', 'p3scan','VirusScan'));
        $this->declareParameter('SpamScan', Validate::SERVICESTATUS, array('configuration', 'p3scan','SpamScan'));
        $this->declareParameter('Template', $this->createValidator()->memberOf($this->templates), array('configuration', 'p3scan','Template'));
    }

    protected function onParametersSaved($changes)
    {
        $this->getPlatform()->signalEvent('nethserver-p3scan-update');
    }

    private function readTemplates() 
    {
        return glob("/etc/p3scan/p3scan-??.mail"); 
    }

    public function prepareView(\Nethgui\View\ViewInterface $view) 
    {
        parent::prepareView($view);
        if (!$this->templates) {
            $this->templates = $this->readTemplates();
        }
        $view['TemplateDatasource'] = array_map(function($fmt) use ($view) {
            $lang = substr($fmt,19,2);
            return array($fmt, $view->translate('lang_'. $lang . '_label'));
        }, $this->templates);

        
    }

}

