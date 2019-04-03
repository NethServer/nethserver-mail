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

<div class="modal fade" id="ModalBccEdit" tabindex="-1" role="dialog" aria-labelledby="modalBccEditTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" aria-label="Close">
                    <span class="pficon pficon-close"></span>
                </button>
                <h4 class="modal-title" id="modalBccEditTitle">{{ $t('modal_bcc_edit.title') }}</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="modalBccEditSwitch">
                            {{ $t('modal_bcc_edit.switch_label') }}
                        </label>
                        <div class="col-sm-8">
                            <input type="checkbox" id="modalBccEditSwitch" class="form-control" v-model="cStatus" true-value="enabled" false-value="disabled">
                        </div>
                    </div>
                    <div v-show="cStatus == 'enabled'" class="form-group">
                        <label class="col-sm-4 control-label" for="modalBccEditAddressInput">{{ $t('modal_bcc_edit.address_label') }}</label>
                        <div :class="['col-sm-8', vErrors.AlwaysBccAddress ? 'has-error' : '']">
                            <input type="input" id="modalBccEditAddressInput" class="form-control" v-model="cAddress">
                            <span class="help-block">{{ vErrors.AlwaysBccAddress ? $t('modal_bcc_edit.address_error') : $t('modal_bcc_edit.address_help') }}</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <span v-show="vReadStatus != 'idle'" class="floatleft"><span class="spinner spinner-xs spinner-inline"></span> {{ $t('modal.spinner_message') }}</span>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ $t('modal.cancel_button') }}</button>
                <button type="button" class="btn btn-primary" v-on:click="$emit('modal-save')">{{ $t('modal.save_button') }}</button>
            </div>
        </div>
    </div>
</div> <!-- end modal -->

</template>

<script>
import execp from '@/execp'

export default {
    name: "ModalBccEdit",
    props: {
        AlwaysBccStatus: String,
        AlwaysBccAddress: String,
    },
    data: function() {
        return {
            vReadStatus: 'idle',
            vErrors: {},
            cStatus: this.AlwaysBccStatus || 'disabled',
            cAddress: this.AlwaysBccAddress || '',
        }
    },
    mounted: function() {
        var $ = window.jQuery;
        this.$on('modal-save', () => {
            var inputData = {
                action: 'bcc',
                AlwaysBccStatus: this.cStatus,
                AlwaysBccAddress: this.cAddress
            }
            this.vReadStatus = 'running'
            this.vErrors = {}

            execp("nethserver-mail/transport/validate", inputData)
            .catch((validationError) => {
                this.vReadStatus = 'idle'
                let err = {}
                for(let i in validationError.attributes) {
                    let attr = validationError.attributes[i]
                    err[attr.parameter] = this.$t('validation.' + attr.error)
                }
                this.vErrors = err
                return Promise.reject(validationError) // still unresolved
            })
            .then((validationResult) => {
                return execp("nethserver-mail/transport/update", inputData, true) // start another async call
            })
            .finally(() => {
                this.vReadStatus = 'idle' // stop the spinner
            })
            .then(() => {
                $(this.$el).modal('hide') // on successful resolution close the dialog
                this.$emit('modal-close')
            })
        })
    },
}
</script>
