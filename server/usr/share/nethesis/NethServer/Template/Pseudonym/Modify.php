<?php

/* @var $view \Nethgui\Renderer\Xhtml */
if ($view->getModule()->getIdentifier() == 'update') {
    $headerText = 'Pseudonym_update_header';
    $keyWidgets = '';
} else {
    $headerText = 'Pseudonym_create_header';

    $keyWidgets = $view->panel()->setAttribute('class', 'labeled-control label-above');

    $keyWidgets
            ->insert($view->literal('<label>' . $T('pseudonym_label') . '</label>'))
            ->insert($view->textInput('localAddress', $view::LABEL_NONE))
            ->insert($view->literal(' @ '))
            ->insert($view->selector('domainAddress', $view::SELECTOR_DROPDOWN | $view::LABEL_NONE))
    ;
}

echo $view->header('pseudonym')->setAttribute('template', $T($headerText));
echo $keyWidgets;
echo $view->textInput('Description');
echo $view->fieldset()->setAttribute('template', $T('Destinations_label'))
        ->insert($view->objectPicker('Account')
            ->setAttribute('objects', 'AccountDatasource')
            ->setAttribute('objectLabel', 1))
;
echo $view->textArea('ExtAddresses', $view::LABEL_ABOVE)->setAttribute('dimensions', '5x30');
echo $view->checkbox('Access', 'private')->setAttribute('uncheckedValue', 'public');

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_CANCEL | $view::BUTTON_HELP);



