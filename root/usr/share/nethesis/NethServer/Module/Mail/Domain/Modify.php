<?php
namespace NethServer\Module\Mail\Domain;

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
use Nethgui\Controller\Table\Modify as Table;

/**
 * Modify domain
 *
 * Generic class to create/update/delete Domain records
 * 
 * @author Davide Principi <davide.principi@nethesis.it>
 * @since 1.0
 */
class Modify extends \Nethgui\Controller\Table\Modify
{
    const DISCLAIMER_MAX_LENGTH = 2048;
    const DISCLAIMER_PATH = '/var/lib/nethserver/mail-disclaimers/';

    public function initialize()
    {
        $parameterSchema = array(
            array('domain', Validate::HOSTNAME_FQDN, Table::KEY),
            array('Description', Validate::ANYTHING, Table::FIELD),
            array('TransportType', Validate::ANYTHING, Table::FIELD),
            array('DisclaimerStatus', Validate::SERVICESTATUS, Table::FIELD),
        );

        $this->declareParameter('DisclaimerText', $this->createValidator()->maxLength(self::DISCLAIMER_MAX_LENGTH), $this->getPlatform()->getMapAdapter(
                array($this, 'readDisclaimerFile'), array($this, 'writeDisclaimerFile'), array()
            ));

        $this->setSchema($parameterSchema);
        $this->setDefaultValue('TransportType', 'Relay');

        parent::initialize();
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        parent::validate($report);
        $primaryDomain = explode('.', gethostname(), 2)[1];
        if($this->getRequest()->isMutation() && $primaryDomain === $this->parameters['domain'] && $this->parameters['TransportType'] === 'Relay') {
            $report->addValidationErrorMessage($this, 'domain', 'valid_relay_notprimarydomain');
        }
    }

    public function readDisclaimerFile()
    {
        if ( ! isset($this->parameters['domain'])) {
            return '';
        }

        $fileName = self::DISCLAIMER_PATH . $this->parameters['domain'] . '.raw';
        $value = $this->getPhpWrapper()->file_get_contents($fileName, FALSE, NULL, -1, self::DISCLAIMER_MAX_LENGTH);

        if ($value === FALSE) {
            $value = '';
        }

        return $value;
    }

    public function writeDisclaimerFile($value)
    {

        $fileName = self::DISCLAIMER_PATH . $this->parameters['domain'];

        // Prepare the RAW value
        $valueRaw = trim($value) . "\n";
        $retvalRaw = $this->getPhpWrapper()->file_put_contents($fileName . '.raw', $valueRaw);
        if ($retvalRaw === FALSE) {
            $this->getLog()->error(sprintf('%s: file_put_contents failed to write data to %s', __CLASS__, $fileName . '.raw'));
            return FALSE;
        }

        // Prepare ASCII encoding, prepending an empty line
        $valueTxt = "\n" . $this->transliterateLatin($valueRaw);
        $valueTxt = wordwrap($valueRaw);
        $valueTxt = \iconv('UTF-8', 'ASCII//TRANSLIT', $valueTxt);
        $retvalTxt = $this->getPhpWrapper()->file_put_contents($fileName . '.txt', $valueTxt);
        if ($retvalTxt === FALSE) {
            $this->getLog()->error(sprintf('%s: file_put_contents failed to write data to %s', __CLASS__, $fileName . '.txt'));
            return FALSE;
        }

        // Prepare HTML encoding, prepending a line-break
        $wikiText = new \NethServer\Tool\WikiText();
        $valueHtml = "  \n" . htmlentities($valueRaw, ENT_COMPAT, 'UTF-8');
        $valueHtml = wordwrap($valueHtml, 60);
        $valueHtml = $wikiText->convert($valueHtml);
        $retvalHtml = $this->getPhpWrapper()->file_put_contents($fileName . '.html', $valueHtml);
        if ($retvalHtml === FALSE) {
            $this->getLog()->error(sprintf('%s: file_put_contents failed to write data to %s', __CLASS__, $fileName . '.html'));
            return FALSE;
        }

        return TRUE;
    }

    private function transliterateLatin($text)
    {
        static $transTable = array(
        'à' => 'a\'',
        'è' => 'e\'',
        'é' => 'e\'',
        'ì' => 'i\'',
        'ò' => 'o\'',
        'ù' => 'u\'',
        'À' => 'A\'',
        'È' => 'E\'',
        'É' => 'E\'',
        'Ì' => 'I\'',
        'Ò' => 'O\'',
        'Ù' => 'U\'',
        );

        return strtr($text, $transTable);
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $templates = array(
            'create' => 'NethServer\Template\Mail\Domain\Modify',
            'update' => 'NethServer\Template\Mail\Domain\Modify',
            'delete' => 'Nethgui\Template\Table\Delete',
        );
        $view->setTemplate($templates[$this->getIdentifier()]);
    }

    /**
     * Delete the record after the event has been successfully completed
     * @param string $key
     */
    protected function processDelete($key)
    {
        $accountDb = $this->getPlatform()->getDatabase('domains');
        $accountDb->setType($key, 'domain-deleted');
        $deleteProcess = $this->getPlatform()->signalEvent('domain-delete', array($key));
        if ($deleteProcess->getExitCode() === 0) {
            parent::processDelete($key);
        }
    }

    protected function onParametersSaved($changedParameters)
    {
        if ($this->getIdentifier() === 'delete') {
            // delete case is handled in "processDelete()" method:
            // signalEvent() is invoked there.
            return;
        } elseif ($this->getIdentifier() === 'update') {
            $event = 'modify';
        } else {
            $event = $this->getIdentifier();
        }
        $this->getPlatform()->signalEvent(sprintf('domain-%s@post-process', $event), array($this->parameters['domain']));
    }

}
