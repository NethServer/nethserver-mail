<?php

/* @var $view Nethgui\Renderer\Xhtml */

$virusCheckbox = $view->checkBox('VirusCheckStatus', 'enabled')
    ->setAttribute('uncheckedValue', 'disabled');

// send some translated strings to the javascript context:
$view->includeTranslations(array(
    'New SB',
    'New RW',
    'New SW',
    'Delete',
    'Done',
    'Update',
    'allow To',
    'allow From',
    'deny To',
    'deny From',
));
$view->includeFile('NethServer/Js/nethserver.collectioneditor.filter.js');
$view->includeFile('NethServer/Css/nethserver.collectioneditor.filter.css');

$spamCheckbox = $view->fieldsetSwitch('SpamCheckStatus', 'enabled', $view::FIELDSETSWITCH_CHECKBOX | $view::FIELDSETSWITCH_EXPANDABLE)
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($view->slider('SpamTag2Level', $view::LABEL_ABOVE)
        ->setAttribute('min', $view->getModule()->spamTagLevel + 0.1)
        ->setAttribute('max', $view->getModule()->spamDsnLevel - 0.1)
        ->setAttribute('step', 0.1)
        ->setAttribute('label', $T('SpamTag2Level ${0}'))
    )
    ->insert($view->slider('SpamKillLevel', $view::LABEL_ABOVE)
        ->setAttribute('min', $view->getModule()->spamTagLevel + 0.1)
        ->setAttribute('max', $view->getModule()->spamDsnLevel - 0.1)
        ->setAttribute('step', 0.1)
        ->setAttribute('label', $T('SpamKillLevel ${0}'))
    )
    ->insert(
        $view->fieldsetSwitch('SpamSubjectPrefixStatus', 'enabled', $view::FIELDSETSWITCH_CHECKBOX | $view::FIELDSETSWITCH_EXPANDABLE)
        ->setAttribute('uncheckedValue', 'disabled')
        ->insert($view->textInput('SpamSubjectPrefixString', $view::LABEL_NONE))
    )
    ->insert(
    $view->fieldset('', $view::FIELDSET_EXPANDABLE)->setAttribute('template', $T('AddressAcl_label'))
    ->insert(
        $view->collectionEditor('AddressAcl', $view::LABEL_NONE)
        ->setAttribute('class', 'Filter')
        ->setAttribute('dimensions', '10x30')
    )
    )
;

$fileTypesCheckbox = $view->fieldsetSwitch('BlockAttachmentStatus', 'enabled', $view::FIELDSETSWITCH_CHECKBOX | $view::FIELDSETSWITCH_EXPANDABLE)
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($view->selector('BlockAttachmentClassList', $view::SELECTOR_MULTIPLE | $view::LABEL_NONE))
    ->insert($view->fieldsetSwitch('BlockAttachmentCustomStatus', 'enabled', $view::FIELDSETSWITCH_CHECKBOX | $view::FIELDSETSWITCH_EXPANDABLE)
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($view->textInput('BlockAttachmentCustomList', $view::LABEL_NONE))
);

echo $view->panel()
    ->insert($fileTypesCheckbox)
    ->insert($virusCheckbox)
    ->insert($spamCheckbox)
;

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);