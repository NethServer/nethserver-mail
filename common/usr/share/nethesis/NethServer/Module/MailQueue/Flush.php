<?php
namespace NethServer\Module\MailQueue;

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
 * Flush mail queue, trying to deliver all queued messages
 *
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Flush extends \Nethgui\Controller\Table\AbstractAction
{
    /**
     *
     * @var integer
     */
    private $messageCount = 0;

    public function process()
    {
        if ($this->getRequest()->isMutation()) {
            $process = $this->getPlatform()->exec('/usr/bin/sudo /usr/sbin/postqueue -f');
            if ($process->getExitCode() != 0) {
                $this->getLog()->error(sprintf("%s: postqueue -f command failed - %s", __CLASS__, $process->getOutput()));
            }
        }
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        parent::bind($request);
        if ( ! $this->getRequest()->isMutation()) {
            $this->messageCount = count($this->getAdapter());
        }
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['messageCount'] = $this->messageCount;
    }

}
