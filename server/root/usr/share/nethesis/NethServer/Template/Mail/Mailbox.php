<?php

echo $view->fieldset()->setAttribute('template', $T('Mailbox access protocols'))
    ->insert(
        $view->checkbox('ImapStatus', 'enabled')
        ->setAttribute('uncheckedValue', 'disabled')
    )
    ->insert(
        $view->checkbox('PopStatus', 'enabled')
        ->setAttribute('uncheckedValue', 'disabled')
    )
    ->insert(
        $view->checkbox('TlsSecurity', 'optional')
        ->setAttribute('uncheckedValue', 'required')
    )
;

echo $view->fieldset()->setAttribute('template', $T('Disk space'))
    ->insert($view->radioButton('QuotaStatus', 'disabled'))
    ->insert($view->fieldsetSwitch('QuotaStatus', 'enabled', $view::FIELDSETSWITCH_EXPANDABLE)
        ->insert(
            $view->slider('QuotaDefaultSize', $view::SLIDER_ENUMERATIVE | $view::LABEL_ABOVE)
            ->setAttribute('label', $T('Quota default size ${0}'))
        ))
;

echo $view->fieldset()->setAttribute('template', $T('Spam messages handling'))
    ->insert($view->checkBox('SpamFolder', $view['SpamFolderTarget'])
        ->setAttribute('uncheckedValue', '')
        ->setAttribute('label', $T('SpamFolder_label', array($view['SpamFolderTarget']))))
    ->insert(
        $view->slider('SpamRetentionTime', $view::LABEL_ABOVE | $view::SLIDER_ENUMERATIVE)
        ->setAttribute('label', $T('Hold for ${0}'))
    )
;

echo $view->fieldset()->setAttribute('template', $T('Privileged access'))
    ->insert($view->checkBox('AdminIsMaster', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
    ;


echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);

