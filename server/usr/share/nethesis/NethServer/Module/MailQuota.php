<?php
namespace NethServer\Module;

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
 * Show mail quota stats
 *
 * @author Giacomo Sanchietti <giacomo.sanchietti@nethesis.it>
 * @since 1.0
 */
class MailQuota extends \Nethgui\Controller\TableController
{

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $attributes)
    {
        return new \NethServer\Tool\CustomModuleAttributesProvider($attributes, array(
            'languageCatalog' => array('NethServer_Module_Dashboard_MailQuota'),
            'category' => 'Status')
        );
    }

    
    public function initialize()
    {

        $columns = array(
            'Key',
            array('name' => 'size', 'columnDefs' => array('type' => 'file-size')),
            array('name' => 'max_size', 'columnDefs' => array('type' => 'file-size')),
            array('name' => 'percentage', 'columnDefs' => array('type' => 'percent')),
            'msg_number',
        );

        $this
            ->setTableAdapter(new \Nethgui\Adapter\LazyLoaderAdapter(array($this, 'readQuota')))
            ->setColumns($columns)
        ;

        parent::initialize();
    }

    private function formatSize($bytes, $precision=2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow]; 
    }

    public function readQuota()
    {
        $loader = new \ArrayObject();

        $users = json_decode($this->getPlatform()->exec('/usr/bin/sudo /usr/libexec/nethserver/mail-quota')->getOutput(), true); 
        if (isset($users['result']) && $users['result'] == 'ERROR') {
            return $loader;
        }
        foreach ($users as $user => $values) {
            $loader[$user] = array(
                'user' => $user, 
                'size' => $this->formatSize($values['size']*1024), 
                'max_size' => $values['max'] === NULL ? '-' : $this->formatSize($values['max']*1024),
                'percentage' => $values['perc'] === NULL ? '-' : ($values['perc'] . '%'),
                'msg_number' => $values['msg']
            );
        }
        return $loader;
    }

}
