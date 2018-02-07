<?php

/* @var $view \Nethgui\Renderer\Xhtml */

/*
 * The jsCode below updates the mail address list with values from the
 * Group_create_groupname field.
 */
$jsCode = <<<"EOJSCODE"
jQuery(document).ready(function($) {

    // The update view handler, invoked when the tab Service is shown:
    var updateMailAddresses = function() {
        var addressPart = $('#Group_create_groupname').val().toLowerCase();

        $('.CreateMailAddresses li').each(function(index, node) {
            var address = $(node).text();
            $(node).text(addressPart + address.substr(address.lastIndexOf('@')));
        });

    }

    $('.CreateMailAddresses').parents('.Tabs').first().bind('tabsshow', updateMailAddresses);

    updateMailAddresses();
});
EOJSCODE;

if ($view->getModule()->getPluggableActionIdentifier() === 'create') {
    $view->includeJavascript($jsCode);
    $mailAddresses = $view->fieldsetSwitch('CreateMailAddresses', 'enabled', $view::FIELDSETSWITCH_CHECKBOX | $view::FIELDSETSWITCH_EXPANDABLE)
        ->setAttribute('uncheckedValue', 'disabled')
        ->insert($view->textList('MailAddressList')->setAttribute('tag', 'div.CreateMailAddresses labeled-control/ul/li'));
} else {
    $mailAddresses = $view->fieldset()->setAttribute('template', $T('MailAddressList_label'))
        ->insert($view->textList('MailAddressList'));
}


echo $view->fieldsetSwitch('MailStatus', 'enabled', $view::FIELDSETSWITCH_CHECKBOX | $view::FIELDSETSWITCH_EXPANDABLE)
    ->setAttribute('uncheckedValue', 'disabled')
    ->insert($view->radioButton('MailDeliveryType', 'copy'))
    ->insert($view->radioButton('MailDeliveryType', 'shared'))
    ->insert($mailAddresses)
;


