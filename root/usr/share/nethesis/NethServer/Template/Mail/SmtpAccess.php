<?php

/* @var $view \Nethgui\Renderer\Xhtml */


echo $view->textArea('AccessBypassList', $view::LABEL_ABOVE)->setAttribute('dimensions', '5x30');

echo $view->fieldset(NULL, count($view['AccessPolicies']) > 0 ? 0 : $view::FIELDSET_EXPANDABLE)->setAttribute('template', $T('Advanced options'))
        ->insert($view->checkBox('AccessPolicyTrustedNetworks', 'yes')->setAttribute('uncheckedValue', 'no'))
        ->insert($view->checkBox('AccessPolicySmtpAuth', 'yes')->setAttribute('uncheckedValue', 'no'))
;

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);
