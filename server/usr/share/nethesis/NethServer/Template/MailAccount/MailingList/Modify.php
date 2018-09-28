<?php

/* @var $view \Nethgui\Renderer\Xhtml */
echo $view->header('MailingList')->setAttribute('template', $T('Modify_header'));

echo $view->checkBox('MailingList', 'enabled')->setAttribute('uncheckedValue', 'disabled');

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_CANCEL);
