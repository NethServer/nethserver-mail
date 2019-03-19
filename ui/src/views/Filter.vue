<style>

.modal {
    z-index: 5;
}

.margin-right-md {
    margin-right: 10px;
}

</style>

<template>

<div>
    <h2>{{$t('filter.title')}}</h2>
    <doc-info :placement="'top'" :title="$t('docs.filter')" :chapter="'mail'" :section="'filter'" :inline="false" :lang="'en'"></doc-info>

    <div v-if="!view.isLoaded" class="spinner spinner-lg"></div>
    <div v-if="view.isLoaded">

        <h3>{{$t('filter.stats')}}</h3>

        <div class="container-fluid container-cards-pf">
            <div class="row row-cards-pf">
                <div class="col-xs-6 col-sm-4 col-md-4">
                    <div class="card-pf card-pf-accented card-pf card-pf-utilization">
                        <div class="card-pf-body">
                            <h2 class="card-pf-title">{{$t('filter.scanned_count')}}: <span class="card-pf-utilization-card-details-count">{{stats.counters.scanned ? stats.counters.scanned : "-"}}</span></h2>
                            <h2 class="card-pf-title">{{$t('filter.spam_count')}}: <span class="card-pf-utilization-card-details-count">{{stats.counters.spam_count ? stats.counters.spam_count : "-"}} {{getSpamPercentage()}}</span></h2>
                        </div>
                    </div>
                </div>

                <div class="col-xs-6 col-sm-4 col-md-4">
                    <div class="card-pf card-pf-accented card-pf card-pf-utilization">
                        <div class="card-pf-body">
                            <h2 class="card-pf-title">{{$t('filter.version')}}: <span class="card-pf-utilization-card-details-count">{{stats.info.version}}</span></h2>
                            <span>{{$t('filter.rspamd_web')}}: </span><a target="_blank" :href="getRspamdUrl()">{{$t('filter.rspamd_url')}}</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->


        <h3>{{$t('filter.rules')}}</h3>
        <div id="filter-rules" class="panel panel-default">

            <div class="panel-heading">
                <button @click="openAddRuleModal()" class="btn btn-primary pull-right span-right-margin">{{$t('filter.add_rule')}}</button>
                <span class="panel-title margin-right-md">{{$t('filter.configured_rules')}}: {{filter.WBList.length}}</span>

                <span :class="['margin-left-md fa fa-angle-right field-section-toggle-pf', view.advancedRules ? 'fa-angle-down' : '']"></span>
                <a class="margin-left-md" data-toggle="collapse" data-parent="#filter-rules" href="#ruleDetails" @click="toggleAdvanced('advancedRules')">
                     {{$t('filter.rule_details')}}
                </a>
            </div>

            <div v-if="filter.WBList.length == 0 && view.isLoaded && view.advancedRules" class="blank-slate-pf white collapse">
                <div class="blank-slate-pf-icon">
                    <span class="fa fa-list"></span>
                </div>
                <h1>{{$t('filter.no_rules_found')}}</h1>
                <p>{{$t('filter.no_rules_text')}}.</p>
            </div>

            <div v-if="filter.WBList.length > 0 && view.isLoaded && view.advancedRules" id="ruleDetails" class="collapse panel-collapse collapselist-group no-mg-top">
                <div class="list-group-item" v-for="s in filter.WBList" v-bind:key="s">
                    <div class="list-view-pf-actions">
                        <div v-if="s.isLoading" class="spinner spinner-sm form-spinner-loader"></div>
                        <button :disabled="s.isLoading" data-toggle="modal" @click="openDeleteRuleModal(s)" class="btn btn-danger">
                            {{$t('filter.delete_rule')}}
                        </button>
                    </div>
                    <div class="list-view-pf-main-info">
                        <div class="list-view-pf-left">
                            <span :class="['fa', 'list-view-pf-icon-sm', getRuleIcon(s.type)]"></span>
                        </div>
                        <div class="list-view-pf-body">
                            <div class="list-view-pf-description">
                                <div class="list-group-item-heading">
                                    <span>{{$t('filter.' + s.type + '_list_type')}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="list-view-pf-body">
                            <div class="list-view-pf-description">
                                <div class="list-group-item-heading">
                                    <span>{{s.value}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal" id="deleteRuleModal" tabindex="-1" role="dialog" data-backdrop="static">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{$t('filter.delete_rule')}} - {{$t('filter.' + currentObj.type + '_list_type')}} {{currentObj.value}}</h4>
                        </div>
                        <form class="form-horizontal" v-on:submit.prevent="deleteRule()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="textInput-modal-markup">{{$t('filter.are_you_sure')}}?</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-default" type="button" data-dismiss="modal">{{$t('filter.cancel')}}</button>
                                <button class="btn btn-danger" type="submit">{{$t('filter.delete')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal" id="addRuleModal" tabindex="-1" role="dialog" data-backdrop="static">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{$t('filter.add_rule')}}</h4>
                        </div>
                        <form class="form-horizontal" v-on:submit.prevent="createRule()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="filter">{{$t('filter.rule_action')}}</label>
                                    <div class="col-sm-5">
                                        <select class="form-control" v-model="currentObj.type">
                                            <option value="SW">{{$t('filter.SW_list_type')}}</option>
                                            <option value="RW">{{$t('filter.RW_list_type')}}</option>
                                            <option value="SB">{{$t('filter.SB_list_type')}}</option>
                                        </select>
                                    </div>
                                    <span v-if="errors.currentObj.hasError" class="help-block">{{errors.currentObj.message}}</span>
                                </div>
                                <div :class="['form-group', errors.filter.hasError ? 'has-error' : '']">
                                    <label class="col-sm-3 control-label" for="filter">{{$t('filter.mail_address')}}</label>
                                    <div class="col-sm-5">
                                        <input v-model="currentObj.value" class="form-control">
                                        <span v-if="errors.currentObj.hasError" class="help-block">{{errors.currentObj.message}}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-default" type="button" data-dismiss="modal">{{$t('filter.cancel')}}</button>
                                <button class="btn btn-primary" type="submit">{{$t('filter.add')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        <div class="divider"></div>
        <form class="form-horizontal" v-on:submit.prevent="saveFilter(filter)">
            <h3>{{$t('filter.spam')}}</h3>
            <div :class="['form-group', errors.SpamCheckStatus.hasError ? 'has-error' : '']">
                <label class="col-sm-2 control-label" for="textInput-modal-markup">{{$t('filter.spam_check')}}</label>
                <div class="col-sm-5">
                    <toggle-button class="min-toggle" :width="40" :height="20" :color="{checked: '#0088ce', unchecked: '#bbbbbb'}" :value="filter.SpamCheckStatus == 'enabled'" :sync="true" @change="toggleStatus('SpamCheckStatus')" />
                    <span v-if="errors.SpamCheckStatus.hasError" class="help-block">{{errors.SpamCheckStatus.message}}</span>
                </div>
            </div>

            <div v-if="filter.SpamCheckStatus == 'enabled'" :class="['form-group', errors.SpamTag2Level.hasError ? 'has-error' : '']">
                <label class="col-sm-2 control-label" for="filter">{{$t('filter.spam_tag_level')}}</label>
                <div class="col-sm-5">
                    <span>{{ filter.SpamTag2Level }}</span>
                    <vue-slider v-model="filter.SpamTag2Level" :min="1" :max="25" :use-keyboard="true" :tooltip="'always'"></vue-slider>
                    <span v-if="errors.SpamTag2Level.hasError" class="help-block">{{errors.SpamTag2Level.message}}</span>
                </div>
            </div>

            <div v-if="filter.SpamCheckStatus == 'enabled'" :class="['form-group', errors.SpamKillLevel.hasError ? 'has-error' : '']">
                <label class="col-sm-2 control-label" for="filter">{{$t('filter.spam_kill_level')}}</label>
                <div class="col-sm-5">
                    <div>
                        <span>{{ filter.SpamKillLevel }}</span>
                        <vue-slider v-model="filter.SpamKillLevel" :min="1" :max="25" :use-keyboard="true" :tooltip="'always'" :tooltip-placement="'bottom'"></vue-slider>
                    </div>
                    <span v-if="errors.SpamKillLevel.hasError" class="help-block">{{errors.SpamKillLevel.message}}</span>
                </div>
            </div>

            <legend v-if="filter.SpamCheckStatus == 'enabled'" class="fields-section-header-pf" aria-expanded="true">
                <span :class="['fa fa-angle-right field-section-toggle-pf', view.advancedSpam ? 'fa-angle-down' : '']"></span>
                <a class="field-section-toggle-pf" @click="toggleAdvanced('advancedSpam')">{{$t('filter.advanced_mode')}}</a>
            </legend>

            <div v-if="filter.SpamCheckStatus == 'enabled' && view.advancedSpam" :class="['form-group', errors.SpamTag2Level.hasError ? 'has-error' : '']">
                <label class="col-sm-2 control-label" for="filter">{{$t('filter.spam_grey_level')}}</label>
                <div class="col-sm-5">
                    <div>
                        <span>{{ filter.SpamGreyLevel ? filter.SpamGreyLevel : $t('filter.disabled') }}</span>
                        <vue-slider v-model="filter.SpamGreyLevel" :min="0" :max="25" :use-keyboard="true"></vue-slider>
                    </div>
                    <span v-if="errors.SpamGreyLevel.hasError" class="help-block">{{errors.SpamGreyLevel.message}}</span>
                </div>
            </div>

            <div v-if="filter.SpamCheckStatus == 'enabled' && view.advancedSpam" :class="['form-group', errors.SpamSubjectPrefixStatus.hasError ? 'has-error' : '']">
                <label class="col-sm-2 control-label" for="filter">{{$t('filter.add_spam_header')}}</label>
                <div class="col-sm-5">
                    <input type="checkbox" v-model="filter.SpamSubjectPrefixStatus" true-value="enabled" false-value="disabled" class="form-control">
                    <span v-if="errors.SpamSubjectPrefixStatus.hasError" class="help-block">{{errors.SpamSubjectPrefixStatus.message}}</span>
                </div>
            </div>

            <div v-if="filter.SpamSubjectPrefixStatus == 'enabled'  && view.advancedSpam" :class="['form-group', errors.filter.hasError ? 'has-error' : '']">
                <label class="col-sm-2 control-label" for="filter">{{$t('filter.spam_prefix')}}</label>
                <div class="col-sm-5">
                    <input v-model="filter.SpamSubjectPrefixString" class="form-control">
                    <span v-if="errors.SpamSubjectPrefixStatus.hasError" class="help-block">{{errors.SpamSubjectPrefixStatus.message}}</span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label"></label>
                <div class="col-sm-5">
                    <button class="btn btn-primary">{{$t('filter.save')}}</button>
                </div>
            </div>


            <div class="divider"></div>
            <h3>{{$t('filter.virus')}}</h3>
            <div :class="['form-group', errors.VirusCheckStatus.hasError ? 'has-error' : '']">
                <label class="col-sm-2 control-label" for="filter">{{$t('filter.virus_check')}}</label>
                <div class="col-sm-5">
                    <toggle-button class="min-toggle" :width="40" :height="20" :color="{checked: '#0088ce', unchecked: '#bbbbbb'}" :value="filter.VirusCheckStatus == 'enabled'" :sync="true" @change="toggleStatus('VirusCheckStatus')" />
                    <span v-if="errors.VirusCheckStatus.hasError" class="help-block">{{errors.VirusCheckStatus.message}}</span>
                </div>
            </div>
            <div v-if="filter.VirusCheckStatus == 'enabled'" class="form-group">
                <label class="col-sm-2 control-label" for="filter">{{$t('filter.virus_scan_only_attachments')}}</label>
                <div class="col-sm-5">
                    <input type="checkbox" v-model="filter.VirusScanOnlyAttachment" true-value="enabled" false-value="disabled" class="form-control">
                </div>
            </div>
            <legend v-if="filter.VirusCheckStatus == 'enabled'" class="fields-section-header-pf" aria-expanded="true">
                <span :class="['fa fa-angle-right field-section-toggle-pf', view.advancedVirus ? 'fa-angle-down' : '']"></span>
                <a class="field-section-toggle-pf" @click="toggleAdvanced('advancedVirus')">{{$t('filter.advanced_mode')}}</a>
            </legend>
            <div v-if="filter.VirusCheckStatus == 'enabled' && view.advancedVirus" :class="['form-group', errors.filter.hasError ? 'has-error' : '']">
                <label class="col-sm-2 control-label" for="filter">{{$t('filter.virus_scan_size')}}</label>
                <div class="col-sm-2">
                    <input v-model="filter.VirusScanSize" class="form-control">
                    <span v-if="errors.VirusScanSize.hasError" class="help-block">{{errors.VirusScanSize.message}}</span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label"></label>
                <div class="col-sm-5">
                    <button class="btn btn-primary">{{$t('filter.save')}}</button>
                </div>
            </div>


            <div class="divider"></div>
            <h3>{{$t('filter.attachments')}}</h3>
            <div :class="['form-group', errors.BlockAttachmentStatus.hasError ? 'has-error' : '']">
                <label class="col-sm-2 control-label" for="textInput-modal-markup">{{$t('filter.block_attachment')}}</label>
                <div class="col-sm-5">
                    <toggle-button class="min-toggle" :width="40" :height="20" :color="{checked: '#0088ce', unchecked: '#bbbbbb'}" :value="filter.BlockAttachmentStatus == 'enabled'" :sync="true" @change="toggleStatus('BlockAttachmentStatus')" />
                    <span v-if="errors.BlockAttachmentStatus.hasError" class="help-block">{{errors.BlockAttachmentStatus.message}}</span>
                </div>
            </div>

            <div class="form-group" v-if="filter.BlockAttachmentStatus == 'enabled'">
                <label class="col-sm-2 control-label" for="filter">{{$t('filter.attachments_excutable')}}</label>
                <div class="col-sm-5">
                    <input type="checkbox" v-model="filter.BlockAttachmentExecutable" class="form-control">
                </div>
            </div>

            <div class="form-group" v-if="filter.BlockAttachmentStatus == 'enabled'">
                <label class="col-sm-2 control-label" for="filter">{{$t('filter.attachments_archives')}}</label>
                <div class="col-sm-5">
                    <input type="checkbox" v-model="filter.BlockAttachmentArchives" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <legend v-if="filter.BlockAttachmentStatus == 'enabled'" class="fields-section-header-pf col-sm-1" aria-expanded="true">
                    <span :class="['fa fa-angle-right field-section-toggle-pf', view.advancedAttachments ? 'fa-angle-down' : '']"></span>
                    <a class="field-section-toggle-pf" @click="toggleAdvanced('advancedAttachments')">{{$t('filter.advanced_mode')}}</a>
                </legend>
            </div>

            <div class="form-group" v-if="filter.BlockAttachmentStatus == 'enabled' && view.advancedAttachments">
                <label class="col-sm-2 control-label" for="filter">{{$t('filter.attachments_custom_list_status')}}</label>
                <div class="col-sm-5">
                    <input type="checkbox" v-model="filter.BlockAttachmentCustomStatus" true-value="enabled" false-value="disabled" class="form-control">
                </div>
            </div>

            <div v-if="filter.BlockAttachmentStatus == 'enabled' && view.advancedAttachments && filter.BlockAttachmentCustomStatus == 'enabled'" :class="['form-group', errors.BlockAttachmentCustomList.hasError ? 'has-error' : '']">
                <label class="col-sm-2 control-label" for="filter">{{$t('filter.attachments_custom_list')}}</label>
                <div class="col-sm-5">
                    <input v-model="filter.BlockAttachmentCustomList" class="form-control">
                    <span v-if="errors.BlockAttachmentCustomList.hasError" class="help-block">{{errors.BlockAttachmentCustomList.message}}</span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label"></label>
                <div class="col-sm-5">
                    <button class="btn btn-primary">{{$t('filter.save')}}</button>
                </div>
            </div>

        </form>
    </div>
</div>

</template>

<script>

/* eslint-disable */

import VueSlider from 'vue-slider-component'
import 'vue-slider-component/theme/default.css'

export default {
    name: "Filter",
    components: {
        VueSlider
    },
    mounted() {
        this.getFilter()
        this.initCharts()
    },
    data() {
        return {
            view: {
                isLoaded: false,
                advancedSpam: false,
                advancedAttachments: false,
                advancedRules: false,
                advancedVirus: false
            },
            errors: {
                filter: {
                    hasError: false,
                    message: ""
                },
                SpamTag2Level: {
                    hasError: false,
                    message: ""
                },
                SenderBlackList: {
                    hasError: false,
                    message: ""
                },
                SpamGreyLevel: {
                    hasError: false,
                    message: ""
                },
                status: {
                    hasError: false,
                    message: ""
                },
                RecipientWhiteList: {
                    hasError: false,
                    message: ""
                },
                Password: {
                    hasError: false,
                    message: ""
                },
                VirusAction: {
                    hasError: false,
                    message: ""
                },
                SpamKillLevel: {
                    hasError: false,
                    message: ""
                },
                VirusCheckStatus: {
                    hasError: false,
                    message: ""
                },
                BlockAttachmentCustomStatus: {
                    hasError: false,
                    message: ""
                },
                SpamSubjectPrefixString: {
                    hasError: false,
                    message: ""
                },
                VirusScanSize: {
                    hasError: false,
                    message: ""
                },
                BlockAttachmentCustomList: {
                    hasError: false,
                    message: ""
                },
                VirusScanOnlyAttachment: {
                    hasError: false,
                    message: ""
                },
                BlockAttachmentStatus: {
                    hasError: false,
                    message: ""
                },
                SpamSubjectPrefixStatus: {
                    hasError: false,
                    message: ""
                },
                SpamCheckStatus: {
                    hasError: false,
                    message: ""
                },
                BlockAttachmentClassList: {
                    hasError: false,
                    message: ""
                },
                SenderWhiteList: {
                    hasError: false,
                    message: ""
                },
                currentObj: {
                    hasError: false,
                    message: ""
                }
            },
            filter: {
                isLoading: false,
                SpamTag2Level: "",
                SenderBlackList: [],
                SpamGreyLevel: "",
                status: false,
                RecipientWhiteList: [],
                Password: "",
                VirusAction: "",
                SpamKillLevel: "",
                VirusCheckStatus: false,
                BlockAttachmentCustomStatus: false,
                SpamSubjectPrefixString: "",
                VirusScanSize: "",
                BlockAttachmentCustomList: [],
                VirusScanOnlyAttachment: "",
                BlockAttachmentStatus: false,
                SpamSubjectPrefixStatus: "",
                SpamCheckStatus: false,
                BlockAttachmentClassList: [],
                SenderWhiteList: [],
                BlockAttachmentExecutable: false,
                BlockAttachmentArchives: false,
                WBList: []
            },
            stats: {
                counters: {
                    scanned: 0,
                    ham_count: 0,
                    spam_count: 0,
                    learned: 0,
                },
                info: {
                    version: "-"
                }
            },
            currentObj: {}
        };
    },
    methods: {
        getRspamdUrl() {
                return "https://" + window.location.hostname + ":980/rspamd"
            },
            getSpamPercentage() {
                if (this.stats.counters.spam_count > 0 && this.stats.counters.scanned > 0) {
                    return "(" + this.stats.counters.spam_count / this.stats.counters.scanned + " %)"
                }
                return ""
            },
            openDeleteRuleModal(c) {
                this.currentObj = Object.assign({}, c)
                $("#deleteRuleModal").modal("show")
            },
            openAddRuleModal(c) {
                this.currentObj = {
                    type: "SW",
                    value: ""
                }
                $("#addRuleModal").modal("show")
            },
            initCharts() {
                var context = this;
                nethserver.exec(
                    ["nethserver-mail/filter/read"], {
                        action: "stats"
                    },
                    null,
                    function(success) {
                        try {
                            success = JSON.parse(success);
                        } catch (e) {
                            console.error(e);
                        }
                        if (success.counters) {
                            context.stats.info = success.info;
                            context.stats.counters = success.counters;
                        }
                    },
                    function(error) {
                        console.error(error);
                    }
                );
            },
            getRuleIcon(type) {
                if (type == 'SB') {
                    return 'fa-ban'
                } else {
                    return 'fa-check-circle'
                }
            },
            toggleAdvanced(flag) {
                this.view[flag] = !this.view[flag]
                this.$forceUpdate();
            },
            getFilter() {
                this.view.isLoaded = true;
                var context = this;
                nethserver.exec(
                    ["nethserver-mail/filter/read"], {
                        action: "configuration"
                    },
                    null,
                    function(success) {
                        try {
                            success = JSON.parse(success);
                        } catch (e) {
                            console.error(e);
                        }
                        context.view.isLoaded = true;
                        context.filter = success.props;

                        /* Prepare special attachments categories */
                        if (success.props.BlockAttachmentClassList.indexOf('Arch') >= 0) {
                            context.filter.BlockAttachmentArchives = true
                        } else {
                            context.filter.BlockAttachmentArchives = false
                        }
                        if (success.props.BlockAttachmentClassList.indexOf('Exec') >= 0) {
                            context.filter.BlockAttachmentExecutable = true
                        } else {
                            context.filter.BlockAttachmentExecutable = false
                        }

                        /* Prepare rule list */
                        context.filter.WBList = []
                        success.props.SenderBlackList.forEach(function(el) {
                            context.filter.WBList.push({
                                type: 'SB',
                                value: el
                            })
                        })
                        success.props.RecipientWhiteList.forEach(function(el) {
                            context.filter.WBList.push({
                                type: 'RW',
                                value: el
                            })
                        })
                        success.props.SenderWhiteList.forEach(function(el) {
                            context.filter.WBList.push({
                                type: 'SW',
                                value: el
                            })
                        })

                    },
                    function(error) {
                        console.error(error);
                    }
                );
            },
            toggleStatus(key) {
                this.filter[key] = (this.filter[key] == "enabled") ? "disabled" : "enabled"
            },
            saveFilter(obj) {
                var context = this;

                var tlsObj = {
                    props: {
                        filter: obj.filter
                    },
                    name: "tls",
                    type: "configuration"
                };

                context.filter.isLoading = true;
                context.filter.errors.filter.hasError = false;

                context.exec(
                    ["nethserver-mail/filter/validate"],
                    tlsObj,
                    null,
                    function(success) {
                        context.filter.isLoading = false;

                        // update values
                        context.exec(
                            ["nethserver-mail/filter/update"],
                            tlsObj,
                            function(stream) {
                                console.info("filter", stream);
                            },
                            function(success) {
                                // notification
                                context.$parent.notifications.success.message = context.$i18n.t(
                                    "filter.filter_edit_ok"
                                );

                                // get tls filter
                                context.getFilter();
                            },
                            function(error, data) {
                                // notification
                                context.$parent.notifications.error.message = context.$i18n.t(
                                    "filter.filter_edit_error"
                                );
                            }
                        );
                    },
                    function(error, data) {
                        var errorData = {};
                        context.filter.isLoading = false;
                        context.filter.errors.filter.hasError = false;

                        try {
                            errorData = JSON.parse(data);
                            for (var e in errorData.attributes) {
                                var attr = errorData.attributes[e];
                                context.filter.errors[attr.parameter].hasError = true;
                                context.filter.errors[attr.parameter].message = attr.error;
                            }
                        } catch (e) {
                            console.error(e);
                        }
                    }
                );
            }
    }
};

</script>
