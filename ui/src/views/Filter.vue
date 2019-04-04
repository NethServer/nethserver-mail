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
    <doc-info
      :placement="'top'"
      :title="$t('docs.filter')"
      :chapter="'mail'"
      :section="'filter'"
      :inline="false"
      :lang="'en'"
    ></doc-info>

    <div v-if="!view.isLoaded" class="spinner spinner-lg"></div>
    <div v-if="view.isLoaded">
      <h3>{{$t('filter.stats')}}</h3>

      <div class="row row-stat">
        <div class="row-inline-block">
          <div class="stats-container card-pf-utilization-details">
            <span
              class="card-pf-utilization-card-details-count"
            >{{stats.counters.scanned ? stats.counters.scanned : 0}}</span>
            <span class="card-pf-utilization-card-details-description">
              <span
                class="card-pf-utilization-card-details-line-2 stats-text"
              >{{$t('filter.scanned_count')}}</span>
            </span>
          </div>
          <div class="stats-container card-pf-utilization-details">
            <span
              class="card-pf-utilization-card-details-count"
            >{{stats.counters.spam_count ? stats.counters.spam_count : 0}} {{getSpamPercentage()}}</span>
            <span class="card-pf-utilization-card-details-description">
              <span
                class="card-pf-utilization-card-details-line-2 stats-text"
              >{{$t('filter.spam_count')}}</span>
            </span>
          </div>
        </div>
      </div>

      <div class="col-xs-6 col-sm-4 col-md-4">
        <h2 class="card-pf-title">
          RSpamD {{$t('filter.version')}}:
          <span
            class="card-pf-utilization-card-details-count"
          >{{stats.info.version}}</span>
        </h2>
        <span>{{$t('filter.rspamd_web')}}:</span>
        <a class="mg-left-5" target="_blank" :href="getRspamdUrl(filter)">{{$t('filter.rspamd_url')}}</a>
      </div>

      <div class="row row-cards-pf"></div>

      <h3>{{$t('filter.rules')}}</h3>
      <div id="filter-rules" class="panel panel-default">
        <div class="panel-heading">
          <button
            @click="openAddRuleModal()"
            class="btn btn-primary pull-right span-right-margin"
          >{{$t('filter.add_rule')}}</button>
          <span
            class="panel-title margin-right-md"
          >{{$t('filter.configured_rules')}}: {{filter.WBList.length}}</span>

          <a id="rule_details" class="margin-left-md" @click="toggleAdvanced('advancedRules')">
            <span
              :class="['margin-left-md fa fa-angle-right field-section-toggle-pf', view.advancedRules ? 'fa-angle-down' : '']"
            ></span>
            {{$t('filter.rule_details')}}
          </a>
        </div>

        <div
          v-if="filter.WBList.length == 0 && view.isLoaded"
          :class="['collapse panel-collapse blank-slate-pf white collapse', view.advancedRules ? 'in' : '']"
        >
          <div class="blank-slate-pf-icon">
            <span class="fa fa-list"></span>
          </div>
          <h1>{{$t('filter.no_rules_found')}}</h1>
          <p>{{$t('filter.no_rules_text')}}.</p>
          <button
            @click="openAddRuleModal()"
            class="btn btn-primary span-right-margin"
          >{{$t('filter.add_rule')}}</button>
        </div>

        <div
          v-if="filter.WBList.length > 0 && view.isLoaded"
          id="ruleDetails"
          :class="['collapse panel-collapse collapselist-group no-mg-top', view.advancedRules ? 'in' : '']"
        >
          <div class="list-group-item" v-for="s in filter.WBList" :key="s.value">
            <div class="list-view-pf-actions">
              <div v-if="s.isLoading" class="spinner spinner-sm form-spinner-loader"></div>
              <button
                :disabled="s.isLoading"
                data-toggle="modal"
                @click="openDeleteRuleModal(s)"
                class="btn btn-danger"
              >{{$t('filter.delete_rule')}}</button>
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
                <h4
                  class="modal-title"
                >{{$t('filter.delete_rule')}} - {{$t('filter.' + currentObj.type + '_list_type')}} {{currentObj.value}}</h4>
              </div>
              <form class="form-horizontal" v-on:submit.prevent="deleteRule(currentObj)">
                <div class="modal-body">
                  <div class="form-group">
                    <label
                      class="col-sm-3 control-label"
                      for="textInput-modal-markup"
                    >{{$t('filter.are_you_sure')}}?</label>
                  </div>
                </div>
                <div class="modal-footer">
                  <button
                    class="btn btn-default"
                    type="button"
                    data-dismiss="modal"
                  >{{$t('filter.cancel')}}</button>
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
              <form class="form-horizontal" v-on:submit.prevent="addRule(currentObj)">
                <div class="modal-body">
                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="filter">{{$t('filter.rule_action')}}</label>
                    <div class="col-sm-9">
                      <select class="form-control" v-model="currentObj.type">
                        <option value="SW">{{$t('filter.SW_list_type')}}</option>
                        <option value="RW">{{$t('filter.RW_list_type')}}</option>
                        <option value="SB">{{$t('filter.SB_list_type')}}</option>
                      </select>
                    </div>
                  </div>
                  <div :class="['form-group', errors.currentObj.hasError ? 'has-error' : '']">
                    <label
                      class="col-sm-3 control-label"
                      for="filter"
                    >{{$t('filter.mail_address_or_domain')}}</label>
                    <div class="col-sm-9">
                      <input v-model="currentObj.value" class="form-control">
                      <span
                        v-if="errors.currentObj.hasError"
                        class="help-block"
                      >{{$t('validation.valid_rule')}}</span>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button
                    class="btn btn-default"
                    type="button"
                    data-dismiss="modal"
                  >{{$t('filter.cancel')}}</button>
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
          <label
            class="col-sm-2 control-label"
            for="textInput-modal-markup"
          >{{$t('filter.spam_check')}}</label>
          <div class="col-sm-5">
            <toggle-button
              class="min-toggle"
              :width="40"
              :height="20"
              :color="{checked: '#0088ce', unchecked: '#bbbbbb'}"
              :value="filter.SpamCheckStatus == 'enabled'"
              :sync="true"
              @change="toggleStatus('SpamCheckStatus')"
            />
            <span
              v-if="errors.SpamCheckStatus.hasError"
              class="help-block"
            >{{errors.SpamCheckStatus.message}}</span>
          </div>
        </div>

        <div
          v-if="filter.SpamCheckStatus == 'enabled'"
          :class="['form-group', errors.SpamTag2Level.hasError ? 'has-error' : '']"
        >
          <label class="col-sm-2 control-label" for="filter">{{$t('filter.spam_tag_level')}}</label>
          <div class="col-sm-5">
            <span>{{ filter.SpamTag2Level }}</span>
            <vue-slider
              v-model="filter.SpamTag2Level"
              :min="1"
              :max="25"
              :use-keyboard="true"
              :tooltip="'always'"
            ></vue-slider>
            <span
              v-if="errors.SpamTag2Level.hasError"
              class="help-block"
            >{{$t('validation.valid_spamtag_level')}}</span>
          </div>
        </div>

        <div
          v-if="filter.SpamCheckStatus == 'enabled'"
          :class="['form-group', errors.SpamKillLevel.hasError ? 'has-error' : '']"
        >
          <label class="col-sm-2 control-label" for="filter">{{$t('filter.spam_kill_level')}}</label>
          <div class="col-sm-5">
            <div>
              <span>{{ filter.SpamKillLevel }}</span>
              <vue-slider
                v-model="filter.SpamKillLevel"
                :min="1"
                :max="25"
                :use-keyboard="true"
                :tooltip="'always'"
                :tooltip-placement="'bottom'"
              ></vue-slider>
            </div>
            <span
              v-if="errors.SpamKillLevel.hasError"
              class="help-block"
            >{{$t('validation.valid_spamkill_level')}}</span>
          </div>
        </div>

        <legend
          v-if="filter.SpamCheckStatus == 'enabled' || errors.SpamGreyLevel.hasError"
          class="fields-section-header-pf"
          aria-expanded="true"
        >
          <span
            :class="['fa fa-angle-right field-section-toggle-pf', view.advancedSpam ? 'fa-angle-down' : '']"
          ></span>
          <a
            class="field-section-toggle-pf"
            @click="toggleAdvanced('advancedSpam')"
          >{{$t('filter.advanced_mode')}}</a>
        </legend>

        <div
          v-if="filter.SpamCheckStatus == 'enabled' && view.advancedSpam"
          :class="['form-group', errors.SpamGreyLevel.hasError ? 'has-error' : '']"
        >
          <label class="col-sm-2 control-label" for="filter">{{$t('filter.spam_grey_level')}}</label>
          <div class="col-sm-5">
            <div>
              <span>{{ filter.SpamGreyLevel ? filter.SpamGreyLevel : $t('filter.disabled') }}</span>
              <vue-slider v-model="filter.SpamGreyLevel" :min="0" :max="25" :use-keyboard="true"></vue-slider>
            </div>
            <span
              v-if="errors.SpamGreyLevel.hasError"
              class="help-block"
            >{{$t('validation.valid_greylist_level')}}</span>
          </div>
        </div>

        <div
          v-if="filter.SpamCheckStatus == 'enabled' && view.advancedSpam"
          :class="['form-group', errors.SpamSubjectPrefixStatus.hasError ? 'has-error' : '']"
        >
          <label class="col-sm-2 control-label" for="filter">{{$t('filter.add_spam_header')}}</label>
          <div class="col-sm-5">
            <input
              type="checkbox"
              v-model="filter.SpamSubjectPrefixStatus"
              true-value="enabled"
              false-value="disabled"
              class="form-control"
            >
            <span
              v-if="errors.SpamSubjectPrefixStatus.hasError"
              class="help-block"
            >{{errors.SpamSubjectPrefixStatus.message}}</span>
          </div>
        </div>

        <div
          v-if="filter.SpamSubjectPrefixStatus == 'enabled'  && view.advancedSpam"
          :class="['form-group', errors.filter.hasError ? 'has-error' : '']"
        >
          <label class="col-sm-2 control-label" for="filter">{{$t('filter.spam_prefix')}}</label>
          <div class="col-sm-5">
            <input v-model="filter.SpamSubjectPrefixString" class="form-control">
            <span
              v-if="errors.SpamSubjectPrefixStatus.hasError"
              class="help-block"
            >{{errors.SpamSubjectPrefixStatus.message}}</span>
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
            <toggle-button
              class="min-toggle"
              :width="40"
              :height="20"
              :color="{checked: '#0088ce', unchecked: '#bbbbbb'}"
              :value="filter.VirusCheckStatus == 'enabled'"
              :sync="true"
              @change="toggleStatus('VirusCheckStatus')"
            />
            <span
              v-if="errors.VirusCheckStatus.hasError"
              class="help-block"
            >{{errors.VirusCheckStatus.message}}</span>
          </div>
        </div>
        <div v-if="filter.VirusCheckStatus == 'enabled'" class="form-group">
          <label
            class="col-sm-2 control-label"
            for="filter"
          >{{$t('filter.virus_scan_only_attachments')}}</label>
          <div class="col-sm-5">
            <input
              type="checkbox"
              v-model="filter.VirusScanOnlyAttachment"
              true-value="true"
              false-value="false"
              class="form-control"
            >
          </div>
        </div>
        <legend
          v-if="filter.VirusCheckStatus == 'enabled'"
          class="fields-section-header-pf"
          aria-expanded="true"
        >
          <span
            :class="['fa fa-angle-right field-section-toggle-pf', view.advancedVirus ? 'fa-angle-down' : '']"
          ></span>
          <a
            class="field-section-toggle-pf"
            @click="toggleAdvanced('advancedVirus')"
          >{{$t('filter.advanced_mode')}}</a>
        </legend>
        <div
          v-if="filter.VirusCheckStatus == 'enabled' && view.advancedVirus"
          :class="['form-group', errors.VirusScanSize.hasError ? 'has-error' : '']"
        >
          <label class="col-sm-2 control-label" for="filter">{{$t('filter.virus_scan_size')}}</label>
          <div class="col-sm-2">
            <input v-model="filter.VirusScanSize" class="form-control">
            <span
              v-if="errors.VirusScanSize.hasError"
              class="help-block"
            >{{$t('validation.'+errors.VirusScanSize.message)}}</span>
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
          <label
            class="col-sm-2 control-label"
            for="textInput-modal-markup"
          >{{$t('filter.block_attachment')}}</label>
          <div class="col-sm-5">
            <toggle-button
              class="min-toggle"
              :width="40"
              :height="20"
              :color="{checked: '#0088ce', unchecked: '#bbbbbb'}"
              :value="filter.BlockAttachmentStatus == 'enabled'"
              :sync="true"
              @change="toggleStatus('BlockAttachmentStatus')"
            />
            <span
              v-if="errors.BlockAttachmentStatus.hasError"
              class="help-block"
            >{{errors.BlockAttachmentStatus.message}}</span>
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
          <legend
            v-if="filter.BlockAttachmentStatus == 'enabled'"
            class="fields-section-header-pf col-sm-1"
            aria-expanded="true"
          >
            <span
              :class="['fa fa-angle-right field-section-toggle-pf', view.advancedAttachments ? 'fa-angle-down' : '']"
            ></span>
            <a
              class="field-section-toggle-pf"
              @click="toggleAdvanced('advancedAttachments')"
            >{{$t('filter.advanced_mode')}}</a>
          </legend>
        </div>

        <div
          class="form-group"
          v-if="filter.BlockAttachmentStatus == 'enabled' && view.advancedAttachments"
        >
          <label
            class="col-sm-2 control-label"
            for="filter"
          >{{$t('filter.attachments_custom_list_status')}}</label>
          <div class="col-sm-5">
            <input
              type="checkbox"
              v-model="filter.BlockAttachmentCustomStatus"
              true-value="enabled"
              false-value="disabled"
              class="form-control"
            >
          </div>
        </div>

        <div
          v-if="filter.BlockAttachmentStatus == 'enabled' && view.advancedAttachments && filter.BlockAttachmentCustomStatus == 'enabled'"
          :class="['form-group', errors.BlockAttachmentCustomList.hasError ? 'has-error' : '']"
        >
          <label
            class="col-sm-2 control-label"
            for="filter"
          >{{$t('filter.attachments_custom_list')}}</label>
          <div class="col-sm-5">
            <input v-model="filter.BlockAttachmentCustomList" class="form-control">
            <span
              v-if="errors.BlockAttachmentCustomList.hasError"
              class="help-block"
            >{{errors.BlockAttachmentCustomList.message}}</span>
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

import VueSlider from "vue-slider-component";
import "vue-slider-component/theme/default.css";

export default {
  name: "MailFilter",
  components: {
    VueSlider
  },
  beforeRouteLeave(to, from, next) {
    $(".modal").modal("hide");
    next();
  },
  mounted() {
    this.getFilter();
    this.initCharts();
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
      errors: this.initErrors(),
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
          learned: 0
        },
        info: {
          version: "-"
        }
      },
      currentObj: {}
    };
  },
  methods: {
    initErrors() {
      return {
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
      };
    },
    getRspamdUrl(filter) {
      return 'https://rspamd:' + filter.Password + '@' + window.location.hostname + ':980/rspamd/';
    },
    getSpamPercentage() {
      if (
        this.stats.counters.spam_count > 0 &&
        this.stats.counters.scanned > 0
      ) {
        return (
          "(" +
          Math.round(this.stats.counters.spam_count / this.stats.counters.scanned * 100) +
          " %)"
        );
      }
      return "";
    },
    openDeleteRuleModal(c) {
      this.currentObj = Object.assign({}, c);
      $("#deleteRuleModal").modal("show");
    },
    openAddRuleModal(c) {
      this.currentObj = {
        type: "SW",
        value: ""
      };
      $("#addRuleModal").modal("show");
    },
    initCharts() {
      var context = this;
      nethserver.exec(
        ["nethserver-mail/filter/read"],
        {
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
      if (type == "SB") {
        return "fa-ban";
      } else {
        return "fa-check-circle";
      }
    },
    toggleAdvanced(flag) {
      this.view[flag] = !this.view[flag];
      this.$forceUpdate();
    },
    getFilter() {
      this.view.isLoaded = true;
      var context = this;
      nethserver.exec(
        ["nethserver-mail/filter/read"],
        {
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
          if (success.props.BlockAttachmentClassList.indexOf("Arch") >= 0) {
            context.filter.BlockAttachmentArchives = true;
          } else {
            context.filter.BlockAttachmentArchives = false;
          }
          if (success.props.BlockAttachmentClassList.indexOf("Exec") >= 0) {
            context.filter.BlockAttachmentExecutable = true;
          } else {
            context.filter.BlockAttachmentExecutable = false;
          }

          /* Prepare rule list */
          context.filter.WBList = [];
          success.props.SenderBlackList.forEach(function(el) {
            context.filter.WBList.push({
              type: "SB",
              value: el
            });
          });
          success.props.RecipientWhiteList.forEach(function(el) {
            context.filter.WBList.push({
              type: "RW",
              value: el
            });
          });
          success.props.SenderWhiteList.forEach(function(el) {
            context.filter.WBList.push({
              type: "SW",
              value: el
            });
          });
        },
        function(error) {
          console.error(error);
        }
      );
    },
    toggleStatus(key) {
      this.filter[key] = this.filter[key] == "enabled" ? "disabled" : "enabled";
    },
    deleteRule(obj) {
      this.filter.WBList.splice(this.filter.WBList.indexOf(obj), 1);
      $("#deleteRuleModal").modal("hide");

      this.saveRules();
    },
    addRule(obj) {
      var context = this;

      nethserver.exec(
        ["nethserver-mail/filter/validate"],
        {
          action: "rule",
          value: obj.value
        },
        null,
        function(success) {
          context.errors.currentObj.hasError = false;
          context.filter.WBList.push(context.currentObj);
          $("#addRuleModal").modal("hide");
          context.view.advancedRules = true;

          context.saveRules();
        },
        function(error, data) {
          var errorData = {};
          context.errors.currentObj.hasError = true;

          try {
            errorData = JSON.parse(data);
            for (var e in errorData.attributes) {
              var attr = errorData.attributes[e];
              context.errors.currentObj.message = attr.error;
            }
          } catch (e) {
            console.error(e);
          }
        }
      );
    },
    saveRules() {
      var context = this;
      this.$forceUpdate(); // redraw the list
      var filter = {};
      filter.SenderBlackList = [];
      filter.SenderWhiteList = [];
      filter.RecipientWhiteList = [];
      this.filter.WBList.forEach(function(el) {
        switch (el.type) {
          case "SW":
            filter.SenderWhiteList.push(el.value);
            break;
          case "SB":
            filter.SenderBlackList.push(el.value);
            break;
          case "RW":
            filter.RecipientWhiteList.push(el.value);
            break;
        }
      });

      this.filter.isLoading = true;
      this.errors = context.initErrors();

      nethserver.notifications.success = context.$i18n.t("filter.edit_ok");
      nethserver.notifications.error = context.$i18n.t("filter.edit_error");
      nethserver.exec(
        ["nethserver-mail/filter/update"],
        {
          action: "rule",
          props: filter
        },
        function(stream) {
          console.info("filter", stream);
        },
        function(success) {
          // get filter
          context.getFilter();
        },
        function(error, data) {}
      );
    },
    saveFilter(obj) {
      var context = this;

      var filter = Object.assign({}, obj);

      filter.BlockAttachmentClassList = [];
      delete filter.WBList;
      if (filter.BlockAttachmentArchives) {
        filter.BlockAttachmentClassList.push("Arch");
      }
      if (filter.BlockAttachmentExecutable) {
        filter.BlockAttachmentClassList.push("Exec");
      }
      delete filter.BlockAttachmentExecutable;
      delete filter.BlockAttachmentArchives;

      context.filter.isLoading = true;
      context.errors = context.initErrors();

      nethserver.notifications.success = context.$i18n.t("filter.edit_ok");
      nethserver.notifications.error = context.$i18n.t("filter.edit_error");
      nethserver.exec(
        ["nethserver-mail/filter/validate"],
        {
          action: "filter",
          props: filter
        },
        null,
        function(success) {
          context.filter.isLoading = false;

          // update values
          nethserver.exec(
            ["nethserver-mail/filter/update"],
            {
              action: "filter",
              props: filter
            },
            function(stream) {
              console.info("filter", stream);
            },
            function(success) {
              // get filter
              context.getFilter();
            },
            function(error, data) {}
          );
        },
        function(error, data) {
          var errorData = {};
          context.filter.isLoading = false;
          context.errors = context.initErrors();

          try {
            errorData = JSON.parse(data);
            for (var e in errorData.attributes) {
              var attr = errorData.attributes[e];
              context.errors[attr.parameter].hasError = true;
              context.errors[attr.parameter].message = attr.error;
              if (attr.parameter == "SpamGreyLevel") {
                context.view.advancedSpam = true;
              }
              if (attr.parameter == "VirusScanSize") {
                context.view.advancedVirus = true;
              }
              if (attr.parameter == "BlockAttachmentCustomList") {
                context.view.advancedAttachments = true;
              }
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

<style>
.stats-container {
  display: table-cell !important;
  width: initial !important;
  padding: 20px !important;
  border: none !important;
}

.mg-left-5 {
  margin-left: 5px;
}
</style>
