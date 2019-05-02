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
input[type=radio].form-control, input[type=checkbox].form-control {
    width: 12px !important;
    height: 12px !important;
    display: inline-block;
    vertical-align: -25%;
    margin-right: 1em;
}
.pd-indent {
    margin-left: 28px;
}
.clearer {
    clear: both;
}
.panel-body {
    overflow-y: scroll;
    word-wrap: break-word;
}
</style>

<template>
    <div v-bind:id="id" class="modal" data-backdrop="static" tabindex="-1" role="dialog" v-bind:aria-labelledby="id + 'Label'" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" aria-label="Close">
                        <span class="pficon pficon-close"></span>
                    </button>
                    <h4 class="modal-title" v-bind:id="id + 'Label'">
                        {{ $t('domain.dkim_edit_title', this.domain) }}
                    </h4>
                </div>

                <div class="modal-body">
                    <form class="form-horizontal">
                        <div class="form-group">
                            <label
                            class="col-sm-3 control-label"
                            for="textInput-modal-markup"
                            >{{$t('domain.signature')}}
                            <doc-info
                                :placement="'top'"
                                :title="$t('domain.signature')"
                                :chapter="'signature'"
                                :inline="true"
                            ></doc-info>
                            </label>
                            <div class="col-sm-9">
                            <toggle-button
                                class="min-toggle"
                                :width="40"
                                :height="20"
                                :color="{checked: '#0088ce', unchecked: '#bbbbbb'}"
                                :value="OpenDkimStatus == 'enabled'"
                                :sync="true"
                                @change="OpenDkimStatus == 'enabled' ? OpenDkimStatus = 'disabled' : OpenDkimStatus = 'enabled'"
                            />
                            </div>
                        </div>
                    </form>

                    <div v-show="OpenDkimStatus == 'enabled'" class="pd-indent clearer">

                        <div class="alert alert-warning">
                          <span class="pficon pficon-warning-triangle-o"></span>
                          {{ $t('domain.dkim_instructions_text', this.domain) }}
                        </div>

                        <div id="collapseDkim" class="panel-group">
                          <div class="panel panel-default">
                            <div class="panel-heading">
                              <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#collapseDkim" href="#collapseDkimRawData">
                                  {{ $t('domain.dkim_rawdata_panel_title', this.domain) }}
                                </a>
                              </h4>
                            </div>
                            <div id="collapseDkimRawData" class="panel-collapse collapse in">
                              <div class="panel-body">
                                <tt>{{ dkimRawData }}</tt>
                              </div>
                            </div>
                          </div>
                          <div class="panel panel-default">
                            <div class="panel-heading">
                              <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#collapseDkim" href="#collapseDkimTxtRecord" class="collapsed">
                                  {{ $t('domain.dkim_txtrecord_panel_title', this.domain) }}
                                </a>
                              </h4>
                            </div>
                            <div id="collapseDkimTxtRecord" class="panel-collapse collapse">
                              <div class="panel-body">
                                <tt>{{ dkimTxtRecord }}</tt>
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ $t('cancel') }}</button>
                    <button v-on:click="$emit('modal-save')" type="button" class="btn btn-primary">{{ $t('save') }}</button>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
import execp from '@/execp'

export default {
    name: "ModalDkimEdit",
    props: {
        'id': String,
        'domain': Object,
        'dkimTxtRecord': String,
        'dkimRawData': String,
    },
    data() {
        return {
            "OpenDkimStatus": this.domain['OpenDkimStatus'] || "disabled",
        }
    },
    watch: {
        domain: function(newval) {
            this.OpenDkimStatus = newval.OpenDkimStatus
        }
    },
    mounted: function() {
        this.$on('modal-save', () => {
            var inputData = {
                action: 'edit-dkim',
                domain: {
                    name: this.domain['name'],
                    OpenDkimStatus: this.OpenDkimStatus
                }
            }
            execp("nethserver-mail/domains/validate", inputData)
            .then((validationResult) => {
                window.jQuery(this.$el).modal('hide')
                return execp("nethserver-mail/domains/update", inputData, true) // start another async call
            })
            .then(() => {
                this.$emit('modal-close')
            })
        })
    },

}
</script>
