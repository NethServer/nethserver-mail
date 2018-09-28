<?php

namespace NethServer\Module\MailAccount;

/**
 * Description of nethserver-MailingList
 *
 * @author stephane de Labrusse <stephdl@de-labrusse.fr>
 */

class MailingList extends \Nethgui\Controller\TableController
{
    public function initialize()
    {
        $columns = array(
            'Key',
            'MailingList',
            'Actions'
        );

        $this
            ->setTableAdapter(new MailingList\ListGroups($this->getPlatform()))
            ->setColumns($columns)
            ->addRowAction(new MailingList\Modify('update'))
            ->addTableAction(new \Nethgui\Controller\Table\Help('Help'))

        ;
        parent::initialize();
    }


    public function prepareViewForColumnMailingList(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        if ($values['MailingList'] == 'enabled') {
            return $view->translate('Enabled_label');
        }
        return $view->translate('Disabled_label');
    }

}
