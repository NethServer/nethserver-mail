<?php

/* @var $view Nethgui\Renderer\Xhtml */
$view->requireFlag($view::INSET_DIALOG | $view::INSET_FORM);
echo $view->header('messageCount')->setAttribute('template', $T('Flush_Header'));
echo $view->textLabel('messageCount')->setAttribute('template', $T('Flush_Message'));
echo $view->buttonList()
    ->insert($view->button('Flush', $view::BUTTON_SUBMIT))
    ->insert($view->button('Cancel', $view::BUTTON_CANCEL))
;