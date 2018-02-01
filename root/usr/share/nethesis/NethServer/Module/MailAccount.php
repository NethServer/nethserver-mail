<?php

namespace NethServer\Module;

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
 * Description of MailAccount
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 */
class MailAccount extends \Nethgui\Controller\TabsController
{
    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return new \NethServer\Tool\CustomModuleAttributesProvider($base, array(            
            'category' => 'Management')
        );
    }

    public function initialize()
    {
        parent::initialize();
        $this->addChild(new \NethServer\Module\MailAccount\User());
        $this->addChild(new \NethServer\Module\MailAccount\SharedMailbox());
        $this->addChild(new \NethServer\Module\MailAccount\Pseudonym());
    }

}
