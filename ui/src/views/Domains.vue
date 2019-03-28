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

<style scoped>

.panel-heading {
    display: flex;
    align-items: center;
}

.panel-value {
    flex-grow: 1;
    margin-left: 1em;
}

</style>

<template>

<div>
    <h1>{{ $t('domains.title') }}</h1>
    <div v-if="vReadStatus == 'running'" class="spinner spinner-lg view-spinner"></div>
    <div v-else-if="vReadStatus == 'error'">
        <div class="alert alert-danger">
            <span class="pficon pficon-error-circle-o"></span>
            <strong>OOOPS!</strong> An unexpected error has occurred:<pre>{{ vReadError }}</pre>
        </div>
    </div>
    <div v-else>

        <div class="panel panel-default">
            <div class="panel-heading">
                <span class="panel-title">
                    {{ $t('domains.configure_alwaysbcc_title') }}
                </span>
                <span class="panel-value">
                    <i v-if="bcc.AlwaysBccStatus == 'disabled'">{{ $t('domains.alwaysbcc_disabled') }}</i>
                    <span v-else>{{ bcc.AlwaysBccAddress }}</span>
                </span>
                <button class="btn btn-default" data-toggle="modal" data-target="#ModalBccEdit">{{ $t('domains.configure_alwaysbcc_button') }}</button>
            </div>
        </div>

        <modal-bcc-edit v-bind="bcc" v-on:modal-close="read"></modal-bcc-edit>

        <div class="panel panel-default">
            <div class="panel-heading">
                <span class="panel-title">
                    {{ $t('domains.configure_access_title') }}
                </span>
                <span class="panel-value">{{ policies.join(', ') }}</span>
                <button class="btn btn-default" data-toggle="modal" data-target="#ModalAccessEdit">{{ $t('domains.configure_access_button') }} </button>
            </div>
        </div>

        <modal-access-edit v-on:modal-close="read"></modal-access-edit>

        <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#modalEditDomain">{{ $t('domains.create_domain_label') }}</button>
        <domains-list-view :items="domains"></domains-list-view>
    </div>
</div>

</template>

<script>

import DomainsListView from '@/components/DomainsListView.vue'
import ModalAccessEdit from '@/components/ModalAccessEdit.vue'
import ModalBccEdit from '@/components/ModalBccEdit.vue'
import execp from '@/execp'

export default {
    name: "Domains",
    components: {
        DomainsListView,
        ModalBccEdit,
        ModalAccessEdit
    },
    mounted() {
        this.read()
    },
    data() {
        return {
            vReadStatus: 'running',
            domains: [],
            bcc: {
                AlwaysBccStatus: '',
                AlwaysBccAddress: '',
            },
            access: {
                bypass: [],
                policies: [],
            },
        }
    },
    methods: {
        read() {
            this.vReadStatus = 'running'
            execp("nethserver-mail/domains/read")
            .then(result => {
                for (var k in result) {
                    this[k] = result[k]
                }
                this.vReadStatus = 'success'
            })
            .catch(error => {
                this.vReadStatus = 'error'
                this.vReadError = error
            })
        }
    },
    computed: {
        policies: function() {
            var policies = []
            policies.push(this.$tc('domains.access_bypassrules_label', this.access.bypass.length, {
                count: this.access.bypass.length,
                ip: this.access.bypass[0]
            }))
            if (this.access.policies.indexOf('trustednetworks') != -1) {
                policies.push(this.$t('domains.access_trustednetworks_label'))
            }
            if (this.access.policies.indexOf('smtpauth') != -1) {
                policies.push(this.$t('domains.access_smtpauth_label'))
            }
            return policies
        }
    }
}

</script>
