<!--
#
# Copyright (C) 2019 Nethesis S.r.l.
# http://www.nethesis.it - nethserver@nethesis.it
#
# This script is part of NethServer.
#
# NethServer is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License,
# or any later version.
#
# NethServer is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with NethServer.  If not, see COPYING.
#
-->

<template>
    <h1>{{ $t('transport.title') }}</h1>
    <div>
        <h3>{{ $t('transport.domains_list_title')}}</h3>
        <button class="btn btn-primary btn-lg">Create rule</button>
        <ul class="list-group list-view-pf list-view-pf-view no-mg-top mg-top-10"><li class="green-list list-group-item"><div class="drag-size"><span class="gray mg-right-5">1</span></div><div class="list-view-pf-checkbox drag-here"><span class="fa fa-bars"></span></div><div class="list-view-pf-actions"><button class="btn btn-default"><span class="fa fa-edit span-right-margin"></span>
                Edit
              </button><div class="dropdown pull-right dropdown-kebab-pf"><button type="button" id="dropdownKebabRight9" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" class="btn btn-link dropdown-toggle"><span class="fa fa-ellipsis-v"></span></button><ul aria-labelledby="dropdownKebabRight9" class="dropdown-menu dropdown-menu-right"><li><a><span class="fa fa-lock span-right-margin"></span>
                      Disable
                    </a></li><li><a><span class="fa fa-clone span-right-margin"></span>
                      Duplicate
                    </a></li><li role="separator" class="divider"></li><li><a><span class="fa fa-times span-right-margin"></span>
                      Delete
                    </a></li></ul></div></div><div class="list-view-pf-main-info small-list"><div class="list-view-pf-left"><span data-toggle="tooltip" data-placement="top" data-html="true" title="" class="fa fa-check green border-green list-view-pf-icon-sm" data-original-title="<b>ACCEPT</b><br>"></span><span data-toggle="tooltip" data-placement="top" data-html="true" title="" class="fa fa-bookmark-o log-icon" style="display: none;" data-original-title="Log enabled"></span></div><div class="list-view-pf-body"><div class="list-view-pf-description rules-src-dst"><div class="list-group-item-heading"><span data-toggle="tooltip" data-placement="top" data-html="true" title="" class="handle-overflow" data-original-title="<b>ufohost</b> | <span class=&quot;square-RED mg-right-5 mg-top-5 detail-icon&quot;></span><span>RED</span><br><div><span class=&quot;pficon pficon-screen mg-right-5 mg-top-5 detail-icon&quot;></span><span>192.168.123.1</span></div><span class=&quot;type-info&quot;><b>Host</b></span>"><span class="fa fa-desktop"></span><span class="ufohost mg-left-5"><span class="pficon pficon-warning-triangle-o mg-right-5" style="display: none;"></span>
                        ufohost
                        <a style="display: none;">Create objects.undefined</a></span></span></div><div class="list-group-item-text"><span class="gray fa fa-arrow-right mg-right-10 big-icon"></span><span data-toggle="tooltip" data-placement="top" data-html="true" title="" class="handle-overflow" data-original-title="<b>FW</b><br><span class=&quot;type-info&quot;><b>Firewall</b></span>"><span class="fa fa-fire"></span><span class="fw mg-left-5"><span class="pficon pficon-warning-triangle-o mg-right-5" style="display: none;"></span>
                        FW
                        <a style="display: none;">Create objects.undefined</a></span></span></div></div><div class="list-view-pf-additional-info rules-info"><div data-toggle="tooltip" data-placement="top" data-html="true" title="" class="list-view-pf-additional-info-item" data-original-title="<b>postfix</b><br><div><span class=&quot;fa fa-arrows-h mg-right-5 mg-top-5 detail-icon&quot;></span><span>TCP</span></div><div><span class=&quot;pficon pficon-template mg-right-5 mg-top-5 detail-icon&quot;></span><span>25, 25, 465, 587</span></div><span class='type-info'><b>Service</b></span>"><span class="fa fa-cogs"></span><strong>postfix</strong></div><div data-toggle="tooltip" data-placement="top" data-html="true" title="" class="list-view-pf-additional-info-item" style="display: none;" data-original-title=""><span class="fa fa-clock-o"></span><strong></strong></div><div class="list-view-pf-additional-info-item">First rule</div></div></div></div></li>
        </ul>
    </div>
</template>
