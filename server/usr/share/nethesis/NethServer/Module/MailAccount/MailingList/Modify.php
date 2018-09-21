<?php
namespace NethServer\Module\MailAccount\MailingList;


use Nethgui\System\PlatformInterface as Validate;
use Nethgui\Controller\Table\Modify as Table;

/**
 * CRUD actions on group delegation  records
 *
 */
class Modify extends \Nethgui\Controller\Table\Modify
{

    public function initialize()
    {
        $parameterSchema = array(
            array('Group', Validate::ANYTHING, Table::KEY),
            array('MailingList', $this->createValidator()->memberOf('enabled','disabled'), Table::FIELD),
        );
        $this->setSchema($parameterSchema);

        parent::initialize();
    }


    private function saveProps()

    {
        $props = array();
        $db = $this->getPlatform()->getDatabase('accounts');
        $group = $this->parameters['Group'];
        $mailingList = $this->parameters['MailingList'];

        $db->setKey($group, 'group', array(
            'MailingList' => $mailingList
        ));
    }

    public function process()
    {
        if ($this->getRequest()->isMutation()) {
             $this->saveProps();
             $this->getParent()->getAdapter()->flush();
             $this->getPlatform()->signalEvent('nethserver-mailinglist-save');
        }
    }
}
