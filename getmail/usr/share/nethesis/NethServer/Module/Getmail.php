<?php
namespace NethServer\Module;

/*
 * Copyright (C) 2016 Nethesis S.r.l.
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
 * Configure port forward
 */
class Getmail extends \Nethgui\Controller\TableController
{

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return \Nethgui\Module\SimpleModuleAttributesProvider::extendModuleAttributes($base, 'Management');
    }

    public function initialize()
    {

        $columns = array(
            'Key',
	    'Account',
            'Retriever',
            'Actions'
        );

        $this
            ->setTableAdapter($this->getPlatform()->getTableAdapter('getmail','getmail'))
            ->setColumns($columns)
            ->addTableAction(new \NethServer\Module\Getmail\Modify('create'))            
            ->addTableAction(new \Nethgui\Controller\Table\Help('Help'))
            ->addRowAction(new \NethServer\Module\Getmail\ToggleEnable('disable'))
            ->addRowAction(new \NethServer\Module\Getmail\ToggleEnable('enable'))
            ->addRowAction(new \NethServer\Module\Getmail\Modify('update'))
            ->addRowAction(new \NethServer\Module\Getmail\Modify('delete'))
        ;

        parent::initialize();
    }


    public function prepareViewForColumnRetriever(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        return $view->translate($values['Retriever']."_label");

    }

    public function prepareViewForColumnKey(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        if (!isset($values['status']) || ($values['status'] == "disabled")) {
            $rowMetadata['rowCssClass'] = trim($rowMetadata['rowCssClass'] . ' user-locked');
        }
        return strval($key);
    }

    /**
     * Override prepareViewForColumnActions to hide/show enable/disable actions
     * @param \Nethgui\View\ViewInterface $view
     * @param string $key The data row key
     * @param array $values The data row values
     * @return \Nethgui\View\ViewInterface 
     */
    public function prepareViewForColumnActions(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        $cellView = $action->prepareViewForColumnActions($view, $key, $values, $rowMetadata);

        $killList = array();

        $state = isset($values['status']) ? $values['status'] : 'disabled';

        switch ($state) {
            case 'enabled':
                $remove = 'enable';
                break;
            case 'disabled';
                $remove = 'disable';
                break;
            default:
                break;
        }

        unset($cellView[$remove]);

        return $cellView;
    }
        
}

