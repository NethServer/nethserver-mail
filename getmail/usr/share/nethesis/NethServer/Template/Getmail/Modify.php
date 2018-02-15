<?php

if ($view->getModule()->getIdentifier() == 'update') {
    $headerText = 'update_header_label';
    $mail = $view->textInput('mail',$view::STATE_READONLY);
} else {
    $headerText = 'create_header_label';
    $mail = $view->textInput('mail');
}

echo $view->header()->setAttribute('template',$T($headerText));

echo $view->panel()
    ->insert($mail)
    ->insert($view->selector('Retriever'))
    ->insert($view->textInput('Server'))
    ->insert($view->textInput('Username'))
    ->insert($view->textInput('Password'))
    ->insert($view->selector('Account',  $view::SELECTOR_DROPDOWN))
    ->insert($view->selector('Time', $view::SELECTOR_DROPDOWN));

echo $view->fieldset('')->setAttribute('template', $T('Advanced_label'))
    ->insert($view->selector('Delete', $view::SELECTOR_DROPDOWN))
    ->insert($view->checkbox('SpamCheck', 'enabled')->setAttribute('uncheckedValue', 'disabled'))
    ->insert($view->checkbox('VirusCheck', 'enabled')->setAttribute('uncheckedValue', 'disabled'));
    

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_CANCEL | $view::BUTTON_HELP);

