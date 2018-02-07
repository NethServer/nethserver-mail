<?php

echo $view->slider('MessageSizeMax', $view::SLIDER_ENUMERATIVE | $view::LABEL_ABOVE)
    ->setAttribute('label', $T('Queue message max size (${0})'));

echo $view->slider('MessageQueueLifetime', $view::SLIDER_ENUMERATIVE | $view::LABEL_ABOVE)
    ->setAttribute('label', $T('Queue message lifetime (${0})'));

echo $view->fieldsetSwitch('AlwaysBccStatus', 'enabled', $view::FIELDSETSWITCH_CHECKBOX | $view::FIELDSETSWITCH_EXPANDABLE)
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($view->textInput('AlwaysBccAddress', $view::LABEL_NONE))
;

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);
