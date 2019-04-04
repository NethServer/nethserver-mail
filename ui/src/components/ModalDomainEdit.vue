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
    margin-left: 8px;
}
textarea {
    width: 100%;
    min-height: 7em;
    font-family: monospace;
}
select {
    padding: 0;
}
</style>

<template>
    <div v-bind:id="id" class="modal fade modal-domain-edit" tabindex="-1" role="dialog" v-bind:aria-labelledby="id + 'Label'" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" aria-label="Close">
                        <span class="pficon pficon-close"></span>
                    </button>
                    <h4 class="modal-title" v-bind:id="id + 'Label'">
                        <span v-if="useCase == 'delete'"      >{{ $t('domain.delete_title', this.domain) }}</span>
                        <span v-else-if="useCase == 'create'" >{{ $t('domain.create_title', this.domain) }}</span>
                        <span v-else                          >{{ $t('domain.edit_title', this.domain) }}</span>
                    </h4>
                </div>

                <div v-if="useCase == 'delete'" class="modal-body">
                    <form class="form-horizontal">
                        <i18n path="domain.delete_confirm_message" tag="p">
                            <b>{{ this.domain.name }}</b>
                        </i18n>
                    </form>
                </div>

                <div v-else class="modal-body">
                    <form class="form-horizontal">
                        <div v-if="useCase == 'create'" v-bind:class="['form-group', vErrors.name ? 'has-error' : '']">
                            <label class="col-sm-3 control-label" v-bind:for="id + '-ni'">{{ $t('domain.name_label') }}</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="name" v-bind:id="id + '-ni'" class="form-control">
                                <span class="help-block">{{ vErrors.name ? vErrors.name : $t('domain.name_help') }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" v-bind:for="id + '-di'">{{ $t('domain.description_label') }}</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="Description" v-bind:id="id + '-di'" class="form-control">
                            </div>
                        </div>

                        <fieldset>
                            <legend>{{ $t('domain.destination_fieldset_label', {name}) }}</legend>
                            <div class="form-group col-sm-12">
                                <input type="radio" v-model="TransportType" value="LocalDelivery" v-bind:id="id + '-ttl'" name="TransportType" class="form-control">
                                <label class="control-label" v-bind:for="id + '-ttl'">{{ $t('domain.transport_local_label')}}</label>
                            </div>

                            <div v-show="TransportType == 'LocalDelivery'" class="pd-indent">
                                <div class="form-group col-sm-12">
                                    <input type="checkbox" v-model="AlwaysBccStatus" true-value="enabled" false-value="disabled" v-bind:id="id + '-absc'" name="AlwaysBccStatus" class="form-control">
                                    <label class="control-label" v-bind:for="id + '-absc'">{{ $t('domain.always_bcc_label')}}</label>
                                </div>
                                <div v-show="AlwaysBccStatus == 'enabled'" v-bind:class="['form-group', 'col-sm-12', 'pd-indent', vErrors.AlwaysBccAddress ? 'has-error' : '']">
                                    <input type="input" v-model="AlwaysBccAddress" name="AlwaysBccAddress" class="form-control">
                                    <span class="help-block">{{ vErrors.AlwaysBccAddress ? vErrors.AlwaysBccAddress : $t('domain.always_bcc_help') }}</span>
                                </div>
                                <div class="form-group col-sm-12">
                                    <input type="checkbox" v-model="UnknownRecipientsActionType" true-value="deliver" false-value="bounce" v-bind:id="id + '-uratc'" name="UnknownRecipientsActionType" class="form-control">
                                    <label class="control-label" v-bind:for="id + '-uratc'">{{ $t('domain.unknown_recipients_action_label')}}</label>
                                </div>
                                <div v-show="UnknownRecipientsActionType == 'deliver'" class="form-group col-sm-12 pd-indent">
                                    <label class="control-label col-sm-3" v-bind:for="id + '-uradms'">{{ $t('domain.unknown_recipients_label') }}</label>
                                    <select v-model="vMailboxKey" v-on:click="onFallbackMailboxClick($event)" v-on:focusin="onFallbackMailboxClick($event)" class="col-sm-9" v-bind:id="id + '-uradms'" name="UnknownRecipientsActionDeliverMailbox">
                                      <option v-if="vMailboxCallState == 'loading'" disabled>{{ $t('domain.mailbox_loading_message') }}</option>
                                      <option v-else v-for="el in vMailboxes.entries()" v-bind:value="el[0]">{{ getMailboxLabel(el[1]) }}</option>
                                    </select>
                                </div>
                            </div>

                            <div v-bind:class="['form-group', 'col-sm-12', vErrors.TransportType ? 'has-error' : '']" v-bind:title="isPrimaryDomain === true ? $t('domain.relay_disabled_reason_tooltip') : ''">
                                <input v-bind:disabled="isPrimaryDomain === true" v-model="TransportType" v-bind:id="id + '-transportTypeRelay'" class="form-control" type="radio" value="Relay" name="TransportType" >
                                <label class="control-label" v-bind:for="id + '-transportTypeRelay'">{{ $t('domain.transport_relay_label')}}</label>
                                <span v-show="vErrors.TransportType" class="help-block">{{ vErrors.TransportType }}</span>
                            </div>
                            <div v-show="TransportType == 'Relay'" v-bind:class="['form-group', 'col-sm-12', 'pd-indent', vErrors.RelayHost ? 'has-error' : '']">
                                <input type="input" v-model="RelayHost" v-bind:id="id + '-transportRelayServer'" name="RelayHost" class="form-control">
                                <span class="help-block">{{ vErrors.RelayHost ? vErrors.RelayHost : $t('domain.relay_host_help') }}</span>
                            </div>
                        </fieldset>

                        <div v-if="withDisclaimer">
                            <div class="divider"></div>
                            <div class="form-group col-sm-12">
                                <input type="checkbox" v-model="DisclaimerStatus" true-value="enabled" false-value="disabled" v-bind:id="id + '-disclaimerCheckbox'" name="DisclaimerStatus" class="form-control">
                                <label class="control-label" v-bind:for="id + '-disclaimerCheckbox'">{{ $t('domain.disclaimer_status_label')}}</label>
                            </div>
                            <div v-show="DisclaimerStatus == 'enabled'" class="form-group col-sm-12 pd-indent">
                                <textarea v-model="DisclaimerText" maxlength='2048'></textarea>
                                <span class="help-block">{{ vErrors.DisclaimerText ? vErrors.DisclaimerText : $t('domain.disclaimer_text_help') }}</span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ $t('modal.cancel_button') }}</button>
                    <button v-if="useCase == 'delete'"       v-on:click="$emit('modal-save')" type="button" class="btn btn-danger" >{{ $t('modal.delete_button') }}</button>
                    <span v-else-if="useCase == 'create'">
                        <button v-on:click="$emit('modal-save', {nextAction: 'open-dkim-modal', id: name})" type="button" class="btn btn-default">{{ $t('domain.create_and_dkim_button') }}</button>
                        <button v-on:click="$emit('modal-save')" type="button" class="btn btn-primary">{{ $t('modal.create_button') }}</button>
                    </span>
                    <button v-else                           v-on:click="$emit('modal-save')" type="button" class="btn btn-primary">{{ $t('modal.apply_button') }}</button>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
import execp from '@/execp'

var attrs = [
    'DisclaimerStatus',
    'TransportType',
    'RelayHost',
    'name',
    'Description',
    'AlwaysBccStatus',
    'AlwaysBccAddress',
    'UnknownRecipientsActionType',
    'DisclaimerText',
    'isPrimaryDomain',
];

export default {
    name: "ModalDomainEdit",
    props: {
        'id': String,
        'useCase': String,
        'domain': Object,
        'withDisclaimer': Boolean,
    },
    watch: {
        domain: function(newval) {
            this.vErrors = {}
            for(let i in attrs) {
                this[attrs[i]] = newval[attrs[i]] || "";
            }
            let k = this.getMailboxKey(newval.unknownRecipientMailbox)
            this.vMailboxes = new Map([[k, newval.unknownRecipientMailbox]])
            this.vMailboxKey = k
            this.vMailboxCallState = 'empty'
        },
    },
    data() {
        var obj = {
            vMailboxes: new Map(),
            vErrors: {},
            vMailboxKey: '',
            vMailboxCallState: 'loading',
            unknownRecipientMailbox: {},
        }
        for(let i in attrs) {
            obj[attrs[i]] = ""
        }
        return obj
    },
    mounted: function() {
        this.$on('modal-save', (eventData) => {
            var inputData = {
                action: this.$props['useCase'],
                domain: {}
            }
            for(let i in attrs) {
                inputData.domain[attrs[i]] = this[attrs[i]]
            }
            inputData.domain['unknownRecipientMailbox'] = this.vMailboxes.get(this.vMailboxKey)
            this.vErrors = {}
            execp("nethserver-mail/domains/validate", inputData)
            .catch((validationError) => {
                let err = {}
                for(let i in validationError.attributes) {
                    let attr = validationError.attributes[i]
                    err[attr.parameter] = this.$t('validation.' + attr.error)
                }
                this.vErrors = err
                return Promise.reject(validationError) // still unresolved
            })
            .then((validationResult) => {
                return execp("nethserver-mail/domains/update", inputData, true) // start another async call
            })
            .finally(() => {
                // stop the spinner
            })
            .then(() => {
                window.jQuery(this.$el).modal('hide') // on successful resolution close the dialog
                this.$emit('modal-close', eventData)
            })
        })
    },
    methods: {
        getMailboxKey: function(item) {
            return item.type + '/' + item.name;
        },
        getMailboxLabel: function(item) {
            if(! item.displayname) {
                item.displayname = item.name
            }
            if(item.type == 'group') {
                return this.$t('domain.mailbox_group_label', item);
            } else if (item.type == 'user') {
                return this.$t('domain.mailbox_user_label', item);
            } else if (item.type == 'public') {
                return this.$t('domain.mailbox_public_label', item);
            } else if (item.type == 'builtin') {
                return this.$t('domain.mailbox_builtin_label', item);
            } else {
                return item.displayname;
            }
        },
        onFallbackMailboxClick: function(event) {
            if(this.vMailboxCallState != 'empty') {
                return
            }
            this.vMailboxCallState = 'loading'
            execp("nethserver-mail/mailbox/read", {action:"list", expand:false})
            .then((readObject) => {
                this.vMailboxes = new Map([].concat(
                    readObject.builtin,
                    readObject.users,
                    readObject.groups,
                    readObject.public
                )
                .sort((a, b) => a.name < b.name ? -1 : a.name > b.name ? 1 : 0)
                .map( x => [x.type + '/' + x.name, x]))
                this.vMailboxCallState = 'done'
            })
        }
    },
}
</script>
