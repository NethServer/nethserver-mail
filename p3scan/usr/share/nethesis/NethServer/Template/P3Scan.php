<?php

echo $view->header('P3Scan')->setAttribute('template', $T('P3Scan_header'));

echo $view->panel()
    ->insert($view->fieldsetSwitch('status', 'disabled'))
    ->insert($view->fieldsetSwitch('status', 'enabled', $view::FIELDSETSWITCH_EXPANDABLE)
        ->insert($view->fieldset()->setAttribute('template', $T('checks_label'))
            ->insert($view->checkBox('VirusScan', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
            ->insert($view->checkBox('SpamScan', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
        )
        ->insert($view->fieldset()->setAttribute('template', $T('ssl_label'))
            ->insert($view->radioButton('SSLScan', 'enabled'))
            ->insert($view->radioButton('SSLScan', 'disabled'))
        )
        ->insert($view->selector('Template', $view::SELECTOR_DROPDOWN))
    );
        

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);

