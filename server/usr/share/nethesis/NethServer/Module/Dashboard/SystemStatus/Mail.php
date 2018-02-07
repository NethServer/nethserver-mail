<?php
namespace NethServer\Module\Dashboard\SystemStatus;

/*
 * Copyright (C) 2013 Nethesis S.r.l.
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

/**
 * Retrieve mail domains statistics
 *
 * @author Giacomo Sanchietti
 */
class Mail extends \Nethgui\Controller\AbstractController
{

    public $sortId = 40;
 
    private $domains = array();

    private function readDomains()
    {
        $records = $this->getPlatform()->getDatabase('domains')->getAll('domain');
        return array_keys($records);
    }

    public function process()
    {
        $this->domains = $this->readDomains();
    }
 
    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        if (!$this->domains) {
            $this->domains = $this->readDomains();
        }

        $view['domains'] = $this->domains;
    }
}
