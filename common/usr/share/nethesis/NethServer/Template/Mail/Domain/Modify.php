<?php


/* @var $view \Nethgui\Renderer\WidgetFactoryInterface */
$view->requireFlag($view::INSET_FORM);

if ($view->getModule()->getIdentifier() == 'update') {
    $headerText = $T('Update domain `${0}`');
    $messagesText = $T('Messages to domain ${0}');
    $dkimDomainSetupText = $T('DkimEditDomainSetup_label');
    $keyStyles = $view::STATE_READONLY;
} else {
    $headerText = $T('Create a new domain');
    $messagesText = $T('Messages to this domain');
    $dkimDomainSetupText = $T('DkimNewDomainSetup_label');
    $keyStyles = 0;
}

echo $view->header('domain')->setAttribute('template', $headerText);

echo $view->textInput('domain', $keyStyles);
echo $view->textInput('Description');

$transportPanel = $view->fieldset('domain')
    ->setAttribute('template', $messagesText)
;

foreach ($view['PlugTransport'] as $pluginView) {
    $value = $pluginView->getModule()->getIdentifier();
    $transportPanel->insert(
        $view->fieldsetSwitch('TransportType', $value, $view::FIELDSETSWITCH_EXPANDABLE)
            ->setAttribute('label', $pluginView->translate('TransportType_' . $value . '_label'))
            ->insert($view->literal($pluginView))
    );
}

$transportTypeTarget = $view->getClientEventTarget('TransportType');
$domainTarget = $view->getClientEventTarget('domain');
if(@file_exists('/etc/e-smith/db/configuration/defaults/dovecot/type')) {
    $jsPrimaryDomain = json_encode(explode('.', gethostname(), 2)[1]);
} else {
    $jsPrimaryDomain = '"/"'; // a domain that never matches
}

echo $transportPanel;

# Check if nethserver-mail-disclaimer is installed
if (@file_exists('/usr/libexec/nethserver/disclaimer-send')) {
    echo $view->fieldsetSwitch('DisclaimerStatus', 'enabled', $view::FIELDSETSWITCH_EXPANDABLE | $view::FIELDSETSWITCH_CHECKBOX)
        ->setAttribute('uncheckedValue', 'disabled')
        ->insert($view->textArea('DisclaimerText', $view::LABEL_NONE)->setAttribute('dimensions', '10x40'));
}

$accordionId = $view->getUniqueId('Accordion');
if(@file_exists('/etc/e-smith/db/configuration/defaults/dovecot/type')) {
    $panelTemplate = '<h3><a href="#">%s</a></h3><div>%s</div>';
    $dkimFlat = sprintf($panelTemplate,
        $view->textLabel('DkimSelector')->setAttribute('template', $T('TxtRecordFlat_label')),
        $view->textLabel('DkimKey')
    );
    $dkimRaw = sprintf($panelTemplate,
        $view->textLabel('DkimSelector')->setAttribute('template', $T('TxtRecordRaw_label')),
        $view->textLabel('DkimKeyRaw')
    );

    echo $view->fieldsetSwitch('OpenDkimStatus', 'enabled', $view::FIELDSETSWITCH_EXPANDABLE | $view::FIELDSETSWITCH_CHECKBOX)
        ->setAttribute('uncheckedValue', 'disabled')
        ->insert($view->textLabel('domain')->setAttribute('tag', 'div')->setAttribute('template', $dkimDomainSetupText))
        ->insert($view->panel()->setAttribute('id', $accordionId)
            ->insert($view->literal($dkimFlat))
            ->insert($view->literal($dkimRaw))
        )
    ;
}

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP | $view::BUTTON_CANCEL);

$openDkimStatusId = $view->getUniqueId('OpenDkimStatus');

$view->includeJavascript("
(function ( \$ ) {
    \$('.${domainTarget}').on('nethguiupdateview', function(ev, domain) {
        \$('.${transportTypeTarget}[value=Relay]').trigger(domain == $jsPrimaryDomain ? 'nethguidisable' : 'nethguienable');
    });
    \$('#${accordionId}').accordion();
    \$(window.document).on('nethguishow', function(ev, value) {
        \$('#${accordionId}').accordion('resize');
    });
    \$('#${openDkimStatusId}').parent().next().on('nethguienable', function(ev, value) {
        window.setTimeout(function(){\$('#${accordionId}').accordion('resize')}, 0);
    });
} ( jQuery ));
");

$view->includeCss("
#${accordionId} {
    margin-top: 0.5em;
    max-width: 80em;
}
#${accordionId} .ui-state-active {
    border-color: #aaa;
}
#${accordionId} .ui-accordion-content {
    word-break: break-all;
    font-family: monospace;
}
");
