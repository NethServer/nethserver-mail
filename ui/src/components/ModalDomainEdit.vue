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
    <div v-bind:id="id" data-backdrop="static" class="modal modal-domain-edit" tabindex="-1" role="dialog" v-bind:aria-labelledby="id + 'Label'" aria-hidden="true">
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
                    <div class="alert alert-warning alert-dismissable">
                        <span class="pficon pficon-warning-triangle-o"></span>
                        <strong>{{$t('warning')}}. </strong>
                        <i18n path="domain.delete_confirm_message" tag="span">
                            <b>{{ this.domain.name }}</b>
                        </i18n>
                    </div>
                    <form class="form-horizontal">
                        <div class="form-group">
                            <label
                            class="col-sm-3 control-label"
                            for="textInput-modal-markup"
                            >{{$t('are_you_sure')}}?</label>
                        </div>
                    </form>
                </div>

                <div v-else class="modal-body">
                    <form class="form-horizontal">
                        <div v-bind:class="['form-group', vErrors.name ? 'has-error' : '']">
                            <label class="col-sm-3 control-label" v-bind:for="id + '-ni'">{{ $t('domain.name') }}</label>
                            <div class="col-sm-9">
                                <input :disabled="useCase != 'create'" :placeholder="$t('domain.name_help')" type="text" v-model="name" v-bind:id="id + '-ni'" class="form-control">
                                <span v-if="vErrors.name" class="help-block">{{ vErrors.name }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" v-bind:for="id + '-di'">{{ $t('domain.description_label') }}</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="Description" v-bind:id="id + '-di'" class="form-control">
                            </div>
                        </div>

                        <legend class="fields-section-header-pf" aria-expanded="true">
                            <span class="field-section-toggle-pf">{{ $t('domain.destination_fieldset_label', {name}) }}</span>
                        </legend>

                        <div class="form-group">
                            <label
                                class="col-sm-3 control-label"
                                for="textInput-modal-markup"
                                >{{$t('domain.destination')}}
                            </label>
                            <div class="col-sm-9">
                                <input
                                    class="col-sm-2 col-xs-2"
                                    type="radio"
                                    v-model="TransportType"
                                    value="LocalDelivery"
                                    v-bind:id="id + '-ttl'"
                                >
                                <label
                                    class="col-sm-10 col-xs-10 control-label text-align-left"
                                    v-bind:for="id + '-ttl'"
                                >{{$t('domain.transport_local_label')}}</label>
                                <input
                                    class="col-sm-2 col-xs-2"
                                    type="radio"
                                    v-bind:disabled="isPrimaryDomain === true"
                                    v-model="TransportType"
                                    v-bind:id="id + '-transportTypeRelay'"
                                    value="Relay"
                                >
                                <label
                                    class="col-sm-10 col-xs-10 control-label text-align-left"
                                    v-bind:for="isPrimaryDomain === true ? id + '-transportTypeRelay' : undefined"
                                >{{$t('domain.transport_relay_label')}}</label>
                            </div>
                        </div>

                        <div v-if="TransportType == 'LocalDelivery'" class="form-group">
                            <label
                                class="col-sm-3 control-label"
                                for="textInput-modal-markup"
                                >{{$t('domain.always_bcc_label')}}
                            </label>
                            <div class="col-sm-9">
                                <toggle-button
                                    class="min-toggle"
                                    :width="40"
                                    :height="20"
                                    :color="{checked: '#0088ce', unchecked: '#bbbbbb'}"
                                    :value="AlwaysBccStatus == 'enabled'"
                                    :sync="true"
                                    @change="AlwaysBccStatus == 'enabled' ? AlwaysBccStatus = 'disabled' : AlwaysBccStatus = 'enabled'"
                                />
                            </div>
                        </div>
                        <div v-if="TransportType == 'LocalDelivery' && AlwaysBccStatus == 'enabled'"
                            :class="['form-group', vErrors.AlwaysBccAddress ? 'has-error' : '']">
                            <label
                                class="col-sm-3 control-label"
                                for="textInput-modal-markup"
                                >{{$t('domain.address')}}
                            </label>
                            <div class="col-sm-9">
                                <input :placeholder="$t('domain.always_bcc_help')" type="input" v-model="AlwaysBccAddress" name="AlwaysBccAddress" class="form-control">
                                <span v-if="vErrors.AlwaysBccAddress" class="help-block">{{ vErrors.AlwaysBccAddress }}</span>
                            </div>
                        </div>

                        <div v-if="TransportType == 'LocalDelivery'" class="form-group">
                            <label
                                class="col-sm-3 control-label"
                                for="textInput-modal-markup"
                                >{{$t('domain.unknown_recipients_action_label')}}
                            </label>
                            <div class="col-sm-9">
                                <toggle-button
                                    class="min-toggle"
                                    :width="40"
                                    :height="20"
                                    :color="{checked: '#0088ce', unchecked: '#bbbbbb'}"
                                    :value="UnknownRecipientsActionType == 'deliver'"
                                    :sync="true"
                                    @change="UnknownRecipientsActionType == 'deliver' ? UnknownRecipientsActionType = 'bounce' : UnknownRecipientsActionType = 'deliver'"
                                />
                            </div>
                        </div>
                        <div v-if="TransportType == 'LocalDelivery' && UnknownRecipientsActionType == 'deliver'" class="form-group">
                            <label
                                class="col-sm-3 control-label"
                                for="textInput-modal-markup"
                                >{{$t('domain.unknown_recipients_label')}}
                            </label>
                            <div class="col-sm-9">
                                <select v-model="vMailboxKey" v-on:click="onFallbackMailboxClick($event)" v-on:focusin="onFallbackMailboxClick($event)" class="col-sm-9 form-control" v-bind:id="id + '-uradms'" name="UnknownRecipientsActionDeliverMailbox">
                                    <option v-if="vMailboxCallState == 'loading'" disabled>{{ $t('domain.mailbox_loading_message') }}</option>
                                    <option v-else v-for="(el, elk) in vMailboxes.entries()" v-bind:key="elk" v-bind:value="el[0]">{{ getMailboxLabel(el[1]) }}</option>
                                </select>
                            </div>
                        </div>





                        <div v-if="TransportType == 'Relay'"
                            :class="['form-group', vErrors.RelayHost ? 'has-error' : '']">
                            <label
                                class="col-sm-3 control-label"
                                for="textInput-modal-markup"
                                >{{$t('domain.destination_relay')}}
                            </label>
                            <div class="col-sm-9">
                                <input :placeholder="$t('domain.relay_host_help')" type="input" v-model="RelayHost" v-bind:id="id + '-transportRelayServer'" name="RelayHost" class="form-control">
                                <span v-if="RelayHost" class="help-block">{{ vErrors.RelayHost }}</span>
                            </div>
                        </div>


                        <legend v-if="withDisclaimer" class="fields-section-header-pf" aria-expanded="true">
                            <span class="field-section-toggle-pf">{{ $t('domains.disclaimers') }}</span>
                        </legend>

                        <div v-if="withDisclaimer" class="form-group">
                            <label
                                class="col-sm-3 control-label"
                                v-bind:for="id + '-disclaimerCheckbox'"
                                >{{$t('domain.disclaimer_status_label')}}
                            </label>
                            <div class="col-sm-9">
                                <toggle-button
                                    v-bind:id="id + '-disclaimerCheckbox'"
                                    class="min-toggle"
                                    :width="40"
                                    :height="20"
                                    :color="{checked: '#0088ce', unchecked: '#bbbbbb'}"
                                    :value="DisclaimerStatus == 'enabled'"
                                    :sync="true"
                                    @change="DisclaimerStatus == 'enabled' ? DisclaimerStatus = 'disabled' : DisclaimerStatus = 'enabled'"
                                />
                            </div>
                        </div>
                        <div v-show="withDisclaimer && DisclaimerStatus == 'enabled'"
                            :class="['form-group', vErrors.DisclaimerText ? 'has-error' : '']">
                             <label
                                class="col-sm-3 control-label"
                                v-bind:for="id + '-disclaimerCheckbox'"
                                >{{$t('domain.message')}}
                            </label>
                            <div class="col-sm-9">
                                <textarea :placeholder="$t('domain.disclaimer_text_help')" class="form-control min-textarea-height" v-model="DisclaimerText" maxlength='2048'></textarea>
                                <span v-if="vErrors.DisclaimerText" class="help-block">{{ vErrors.DisclaimerText }}</span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div v-if="loader" class="spinner spinner-sm form-spinner-loader"></div>
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ $t('modal.cancel_button') }}</button>
                    <button v-if="useCase == 'delete'" v-on:click="$emit('modal-save')" type="button" class="btn btn-danger" >{{ $t('delete') }}</button>
                    <button v-else-if="useCase == 'create'" v-on:click="$emit('modal-save')" type="button" class="btn btn-primary">{{ $t('save') }}</button>
                    <button v-else v-on:click="$emit('modal-save')" type="button" class="btn btn-primary">{{ $t('edit') }}</button>
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
            loader: false
        }
        for(let i in attrs) {
            obj[attrs[i]] = ""
        }
        return obj
    },
    mounted: function() {
        this.$on('modal-save', (eventData) => {
            this.loader = true
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
                this.loader = false
                return Promise.reject(validationError) // still unresolved
            })
            .then((validationResult) => {
                this.loader = false
                window.jQuery(this.$el).modal('hide') // on successful resolution close the dialog

                nethserver.notifications.success = this.$t(
                    "domain.domain_" +
                    (this.useCase == 'create' ? "created" : "updated") +
                    "_ok"
                );
                nethserver.notifications.error = this.$t(
                    "domain.domain_" +
                    (this.useCase == 'create' ? "created" : "updated") +
                    "_error"
                );

                return execp("nethserver-mail/domains/update", inputData, true) // start another async call
            })
            .finally(() => {
                // stop the spinner
                this.loader = false
            })
            .then(() => {
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
