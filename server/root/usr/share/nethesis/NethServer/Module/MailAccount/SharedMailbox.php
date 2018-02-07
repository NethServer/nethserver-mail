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
class SharedMailbox extends \Nethgui\Controller\TableController
{

    public function initialize()
    {
        $this->setTableAdapter(new \NethServer\Module\MailAccount\SharedMailbox\SharedMailboxAdapter($this->getPlatform()));
        $this->setColumns(array(
            'Key',
            'Actions'
        ));
        $this->addTableAction(new \NethServer\Module\MailAccount\SharedMailbox\Edit('create'));
        $this->addTableAction(new \Nethgui\Controller\Table\Help('Help'));
        $this->addRowAction(new \NethServer\Module\MailAccount\SharedMailbox\Edit('update'));
        $this->addRowAction(new \NethServer\Module\MailAccount\SharedMailbox\Edit('delete'));
        parent::initialize();
    }

}
