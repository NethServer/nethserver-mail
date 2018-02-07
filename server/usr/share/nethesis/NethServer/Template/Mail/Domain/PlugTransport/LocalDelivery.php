<?php

echo $view->fieldsetSwitch('AlwaysBccStatus', 'enabled', $view::FIELDSETSWITCH_CHECKBOX | $view::FIELDSETSWITCH_EXPANDABLE)
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($view->textInput('AlwaysBccAddress', $view::LABEL_NONE))
;

echo $view->fieldsetSwitch('UnknownRecipientsActionType', 'deliver', $view::FIELDSETSWITCH_CHECKBOX | $view::FIELDSETSWITCH_EXPANDABLE)
    ->setAttribute('uncheckedValue', 'bounce')
    ->insert($view->selector('UnknownRecipientsActionDeliverMailbox', $view::SELECTOR_DROPDOWN))
;
