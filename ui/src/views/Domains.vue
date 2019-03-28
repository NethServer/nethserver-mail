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
        <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#modalEditDomain">{{ $t('domains.create_domain_label') }}</button>
        <domains-list-view :items="domains"></domains-list-view>
    </div>
</div>

</template>

<script>


import execp from '@/execp'
import DomainsListView from '@/components/DomainsListView.vue'

export default {
    name: "Domains",
    components: {
        DomainsListView,
    },
    mounted() {
        this.read()
    },
    data() {
        return {
            vReadStatus: 'running',
            domains: [],
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
}

</script>
