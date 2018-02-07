<?php
namespace NethServer\Module;

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

/**
 * Manage the mail queue
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class MailQueue extends \Nethgui\Controller\TableController
{

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $attributes)
    {
        return new \NethServer\Tool\CustomModuleAttributesProvider($attributes, array(
            'category' => 'Status')
        );
    }

    public function initialize()
    {
        $columns = array(
            'Id',
            'Sender',
            'Size',
            'Timestamp',
            'Recipients',
            'Actions',
        );

        $this
            ->setTableAdapter(new MailQueue\MailQueueAdapter($this->getPlatform()))
            ->setColumns($columns)
            ->addTableAction(new MailQueue\Refresh())
            ->addTableAction(new MailQueue\Flush())
            ->addTableAction(new MailQueue\DeleteAll())
            ->addRowAction(new MailQueue\Delete())
        ;

        parent::initialize();
    }

    public function prepareViewForColumnId(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        $rowMetadata['rowCssClass'] .= ' valign-top padicon';
        if ($values['Status'] === 'HOLD') {
            $rowMetadata['rowCssClass'] = trim($rowMetadata['rowCssClass'] . ' locked');
        } elseif ($values['Status'] === 'ACTIVE') {
            $rowMetadata['rowCssClass'] = trim($rowMetadata['rowCssClass'] . ' sync');
        }
        return $values['Id'];
    }

    public function prepareViewForColumnRecipients(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        $recipients = $values['Recipients'];
        if (count($recipients) <= 3) {
            return implode(', ', $recipients);
        }
        return implode(', ', array_merge(array_slice($recipients, 0, 2), array($view->translate('AndXMore', array(count($recipients) - 2)))));
    }

}
