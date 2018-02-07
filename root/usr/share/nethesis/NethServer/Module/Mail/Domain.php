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

/**
 * Domain management for mail server module
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Domain extends \Nethgui\Controller\TableController
{

    public function initialize()
    {
        $columns = array(
            'Key',
            'Description',
            'Actions'
        );

        $this
            ->setTableAdapter($this->getPlatform()->getTableAdapter('domains', 'domain'))
            ->setColumns($columns)
            ->addRowActionPluggable(new \NethServer\Module\Mail\Domain\Modify('update'), 'PlugTransport')
            ->addRowAction(new \NethServer\Module\Mail\Domain\Modify('delete'))
            ->addTableActionPluggable(new \NethServer\Module\Mail\Domain\Modify('create'), 'PlugTransport')
            ->addTableAction(new \Nethgui\Controller\Table\Help('Help'))
        ;
                
        parent::initialize();
    }

}
