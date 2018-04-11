<?php
$view->requireFlag($view::INSET_DIALOG);

if ($view->getModule()->getIdentifier() == 'disable') {
    $headerText = 'Disable external mail account';
    $panelText = 'Proceed with account disable?';
} else {
    $headerText = 'Enable external mail account';
    $panelText = 'Proceed with account enable?';
}

echo $view->panel()
    ->insert($view->header()->setAttribute('template', $view->translate($headerText)))
    ->insert($view->literal($view->translate($panelText)))
;

echo $view->buttonList()
    ->insert($view->button('Yes', $view::BUTTON_SUBMIT))
    ->insert($view->button('No', $view::BUTTON_CANCEL)->setAttribute('value', $view['Cancel']))
;

