<?php
/* @var $view \Nethgui\Renderer\Xhtml */

if ($view->getModule()->getIdentifier() === 'create') {
    $headerText = $T('SharedMailbox_create_header');
} else {
    $special_access = $view->fieldset()->setAttribute('template', $T('Others_label'))
        ->insert($view->textList('Others'));
    $headerText = $T('SharedMailbox_modify_header');
}

echo $view->header('Name')->setAttribute('template', $headerText);

echo $view->textInput('Name');

echo $view->fieldset()->setAttribute('template', $T('OwnersSelector_label'))
        ->insert($view->objectPicker('Owners')->setAttribute('objects', 'OwnersDatasource'))
;

if ($view->getModule()->getIdentifier() === 'create'){
echo	$view->fieldset()->setAttribute('template', $T('ExtraFields_label'))
                ->insert($view->fieldsetSwitch('CreateAlias', 'enabled', $view::FIELDSETSWITCH_CHECKBOX | $view::FIELDSETSWITCH_EXPANDABLE)->setAttribute('uncheckedValue', 'disabled')
                    ->insert($view->textInput('localAddress', $view::LABEL_NONE))
                    ->insert($view->literal(' @ '))
                    ->insert($view->selector('domainAddress', $view::SELECTOR_DROPDOWN | $view::LABEL_NONE)))
    ;
}

if ($view->getModule()->getIdentifier() === 'update') {
    echo $special_access;
}

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_CANCEL | $view::BUTTON_HELP);
