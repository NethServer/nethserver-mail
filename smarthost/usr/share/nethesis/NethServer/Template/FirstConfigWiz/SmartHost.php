<?php

include 'WizHeader.php';

$smartHostParams = $view->columns()
    ->insert($view->panel()
        ->insert($view->textInput('SmartHostName'))
        ->insert($view->textInput('SmartHostPort'))
    )
    ->insert($view->panel()
    ->insert($view->textInput('SmartHostUsername'))
    ->insert($view->textInput('SmartHostPassword', $view::TEXTINPUT_PASSWORD))
    )

;

echo $view->fieldsetSwitch('SmartHostStatus', 'enabled', $view::FIELDSETSWITCH_CHECKBOX | $view::FIELDSETSWITCH_EXPANDABLE)
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($smartHostParams)
    ->insert($view->checkbox('SmartHostTlsStatus', 'disabled')->setAttribute('uncheckedValue', 'enabled'))
;


include 'WizFooter.php';

