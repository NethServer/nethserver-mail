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

<div class="list-group list-view-pf list-view-pf-view">

    <div v-bind:key="item.id" v-for="item in items" class="list-group-item">
        <div class="list-view-pf-actions">
            <button class="btn btn-default" v-on:click="$emit('item-edit', item)"><span class="fa fa-edit"></span> {{ $t('domains.item_edit_button')}}</button>
            <div class="dropdown pull-right dropdown-kebab-pf">
                <button class="btn btn-link dropdown-toggle" type="button" v-bind:id="item.id + '-ddm'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="fa fa-ellipsis-v"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" v-bind:aria-labelledby="item.id + '-ddm'">
                    <li><a href="#" v-on:click="$emit('item-edit', item)">{{ $t('domains.item_edit_button') }}</a></li>
                    <li><a href="#" v-on:click="$emit('item-dkim', item)">{{ $t('domains.item_dkim_button') }}</a></li>
                    <li><a href="#" v-on:click="$emit('item-delete', item)">{{ $t('domains.item_delete_button') }}</a></li>
                </ul>
            </div>
        </div>

        <div class="list-view-pf-main-info">
            <div class="list-view-pf-left">
                <span v-bind:class="['fa', 'list-view-pf-icon-sm', smartIcon(item)]" v-bind:title="$t('domains.icon-tooltip-' + smartIcon(item))"></span>
            </div>
            <div class="list-view-pf-body">
                <div class="list-view-pf-description">
                    <div class="list-group-item-heading">
                        {{ item.name }}
                    </div>
                    <div class="list-group-item-text">
                        {{ smartDescription(item) }}
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- end item -->

</div>
<!-- end list -->

</template>

<script>

export default {
    name: "DomainsListView",
    props: {
        'items': Array
    },
    data() {
        return {}
    },
    methods: {
        smartIcon: function (item) {
            if(item.TransportType == 'LocalDelivery') {
                return item.isPrimaryDomain ? 'fa-inbox' : 'fa-at'
            } else {
                return 'fa-paper-plane-o'
            }
        },
        smartDescription: function(item) {
            var parts = []
            if (item.Description) {
                parts.push(item.Description)
            }
            if (item.OpenDkimStatus == 'enabled') {
                parts.push(this.$t('domains.item_dkim'))
            }
            if (item.DisclaimerStatus == 'enabled') {
                parts.push(this.$t('domains.item_disclaimer'))
            }
            if (item.AlwaysBccStatus == 'enabled') {
                parts.push(this.$t('domains.item_bcc', item))
            }
            if (item.UnknownRecipientsActionType == 'deliver') {
                parts.push(this.$t('domains.item_unknown_recipients'))
            }
            return parts.join(', ')
        }
    }
}

</script>
