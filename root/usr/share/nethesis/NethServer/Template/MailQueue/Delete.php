<?php

/* @var $view Nethgui\Renderer\Xhtml */
$view->requireFlag($view::INSET_DIALOG | $view::INSET_FORM);

echo $view->header('Id')->setAttribute('template', $T('Delete_Header'));

echo $view->textLabel('Id')->setAttribute('template', $T('Delete_Message'))->setAttribute('tag', 'div');

echo "<ul>";
foreach(array('Sender', 'Recipients', 'Size', 'Timestamp') as $field) {
    echo '<li>' . $T($field . '_label') . ': ' . $view->textLabel($field)->setAttribute('tag', 'em') . '</li>';
}
echo "</ul>";

echo $view->buttonList()
    ->insert($view->button('Delete', $view::BUTTON_SUBMIT))
    ->insert($view->button('Cancel', $view::BUTTON_CANCEL))
;