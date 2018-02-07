<?php
namespace NethServer\Module\MailQueue;

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

use Nethgui\System\PlatformInterface as Validate;

/**
 * Delete a message from the Postfix queue
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Delete extends \Nethgui\Controller\Table\RowAbstractAction
{

    public function initialize()
    {
        parent::initialize();
        $this->setSchema(array(
            array('Id', FALSE, \Nethgui\Controller\Table\RowAbstractAction::KEY),
            array('Sender', FALSE, \Nethgui\Controller\Table\RowAbstractAction::FIELD),
            array('Recipients', FALSE, \Nethgui\Controller\Table\RowAbstractAction::FIELD),
            array('Size', FALSE, \Nethgui\Controller\Table\RowAbstractAction::FIELD),
            array('Timestamp', FALSE, \Nethgui\Controller\Table\RowAbstractAction::FIELD),
        ));
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        $id = \Nethgui\array_head($request->getPath());

        /** @var $recordAdapter \Nethgui\Adapter\RecordAdapter */
        $recordAdapter = $this->getAdapter();

        if ( ! $this->getParent()->getAdapter()->offsetExists($id)) {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1374854464);
        }

        $recordAdapter->setKeyValue($id);
    }

    public function process()
    {

        if ($this->getRequest()->isMutation()) {
            $keyValue = $this->parameters['Id'];

            // Don't call parent's implementation here, only call postsuper -d command
            $process = $this->getPlatform()->exec('/usr/bin/sudo /usr/sbin/postsuper -d ${1}', array(1 => $keyValue));
            if ($process->getExitCode() != 0) {
                $this->getLog()->error(sprintf("%s: postsuper -d %s command failed - %s", __CLASS__, $keyValue, $process->getOutput()));
            }
            $this->getParent()->getAdapter()->flush();
        } else {
            parent::process();
        }
    }

}
