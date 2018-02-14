<?php
namespace NethServer\Module\Getmail;

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
 * Modify domain
 *
 * Generic class to create/update/delete getmail accounts
 * 
 * @author Giacomo Sanchietti <giacomo.sanchietti@nethesis.it>
 */
class Modify extends \Nethgui\Controller\Table\Modify
{
    private $retrievers = array('SimplePOP3Retriever','SimplePOP3SSLRetriever', 'SimpleIMAPRetriever', 'SimpleIMAPSSLRetriever');
    private $times = array('5','10','15','30','60');
    private $days = array('-1','0','1','2','3','7','30','90','360');
    private $provider;

    private function getUserProvider()
    {
        if(!$this->provider) {
            $this->provider = new \NethServer\Tool\UserProvider($this->getPlatform());
        }
        return $this->provider;
    }


    public function initialize()
    {
        $parameterSchema = array(
            array('mail', Validate::EMAIL, \Nethgui\Controller\Table\Modify::KEY),
            array('Account', Validate::ANYTHING, \Nethgui\Controller\Table\Modify::FIELD),
            array('Delete', $this->createValidator()->memberOf($this->days), \Nethgui\Controller\Table\Modify::FIELD),
            array('Password', Validate::ANYTHING, \Nethgui\Controller\Table\Modify::FIELD),
            array('Server', Validate::HOSTADDRESS, \Nethgui\Controller\Table\Modify::FIELD),
            array('Username', Validate::ANYTHING, \Nethgui\Controller\Table\Modify::FIELD),
            array('Retriever', $this->createValidator()->memberOf($this->retrievers), \Nethgui\Controller\Table\Modify::FIELD),
            array('Time',  $this->createValidator()->memberOf($this->times), \Nethgui\Controller\Table\Modify::FIELD),
            array('status', Validate::SERVICESTATUS, \Nethgui\Controller\Table\Modify::FIELD),
            array('SpamCheck', Validate::SERVICESTATUS, \Nethgui\Controller\Table\Modify::FIELD),
            array('VirusCheck', Validate::SERVICESTATUS, \Nethgui\Controller\Table\Modify::FIELD),
        );

        $this->setSchema($parameterSchema);
        $this->setDefaultValue('Delete', '0');
        $this->setDefaultValue('status', 'enabled');
        $this->setDefaultValue('SpamCheck', 'enabled');
        $this->setDefaultValue('VirusCheck', 'enabled');
        $this->setDefaultValue('Time', '30');
        $this->setDefaultValue('Retriever', 'SimplePOP3Retriever');

        parent::initialize();
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        if ($this->getRequest()->isMutation()) {
            $this->parameters['status'] = 'enabled';
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $templates = array(
            'create' => 'NethServer\Template\Getmail\Modify',
            'update' => 'NethServer\Template\Getmail\Modify',
            'delete' => 'Nethgui\Template\Table\Delete',
        );
        $view->setTemplate($templates[$this->getIdentifier()]);

        $view['RetrieverDatasource'] = array_map(function($fmt) use ($view) {
                return array($fmt, $view->translate($fmt . '_label'));
            }, $this->retrievers);
        $view['TimeDatasource'] = array_map(function($fmt) use ($view) {
                return array($fmt, $view->translate($fmt . '_label'));
            }, $this->times);
        $view['DeleteDatasource'] = array_map(function($fmt) use ($view) {
                return array($fmt, $view->translate($fmt . 'd_label'));
            }, $this->days);


        if($this->getRequest()->isValidated()) {
            $tmp = array();
            foreach ($this->getUserProvider()->getUsers() as $key => $values) {
                $tmp[] = array($key, $key);
            }
            $view['AccountDatasource'] = $tmp;
        }
    }

    protected function onParametersSaved($changedParameters)
    {
        $this->getPlatform()->signalEvent('nethserver-getmail-save');
    }

}
