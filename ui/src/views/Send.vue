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
    <h2>{{ $t('send.title') }}</h2>

    <h3>{{ $t('send.configuration') }}</h3>
    <div class="panel panel-default">
      <div class="panel-heading">
        <a target="_blank" href="/nethserver#/settings" class="right external-smarthost">
          {{$t('send.default_smarthost')}}
          <span class="fa fa-external-link"></span>
        </a>
        <span class="panel-title">{{$t('send.configuration')}}</span>
        <span
          class="provider-details margin-left-md"
          data-toggle="collapse"
          data-parent="#provider-markup"
          href="#providerDetails"
          @click="toggleDetails()"
        >
          <span :class="['fa', view.opened ? 'fa-angle-down' : 'fa-angle-right']"></span>
          {{$t('send.details')}}
        </span>
      </div>
      <div id="providerDetails" class="panel-collapse collapse list-group list-view-pf">
        <form class="form-horizontal" v-on:submit.prevent="saveConfiguration()">
          <div :class="['form-group', errors.AccessBypassList.hasError ? 'has-error' : '']">
            <label
              class="col-sm-2 control-label"
              for="textInput-modal-markup"
            >{{$t('send.access_bypass_list')}}</label>
            <div class="col-sm-5">
              <textarea
                v-model="configuration.AccessBypassList"
                class="form-control min-textarea-height"
              ></textarea>
              <span v-if="errors.AccessBypassList.hasError" class="help-block">
                {{$t('validation.validation_failed')}}:
                {{$t('validation.'+errors.AccessBypassList.message)}}
              </span>
            </div>
          </div>

          <div :class="['form-group', errors.SenderValidation.hasError ? 'has-error' : '']">
            <label
              class="col-sm-2 control-label"
              for="textInput-modal-markup"
            >{{$t('send.sender_validation')}}</label>
            <div class="col-sm-5">
              <input type="checkbox" v-model="configuration.SenderValidation" class="form-control">
              <span v-if="errors.SenderValidation.hasError" class="help-block">
                {{$t('validation.validation_failed')}}:
                {{$t('validation.'+errors.SenderValidation.message)}}
              </span>
            </div>
          </div>

          <div :class="['form-group', errors.AccessPoliciesSmtpauth.hasError ? 'has-error' : '']">
            <label
              class="col-sm-2 control-label"
              for="textInput-modal-markup"
            >{{$t('send.access_policy_smtp')}}</label>
            <div class="col-sm-5">
              <input
                type="checkbox"
                v-model="configuration.AccessPoliciesSmtpauth"
                class="form-control"
              >
              <span v-if="errors.AccessPoliciesSmtpauth.hasError" class="help-block">
                {{$t('validation.validation_failed')}}:
                {{$t('validation.'+errors.AccessPoliciesSmtpauth.message)}}
              </span>
            </div>
          </div>

          <div
            :class="['form-group', errors.AccessPoliciesTrustednetworks.hasError ? 'has-error' : '']"
          >
            <label
              class="col-sm-2 control-label"
              for="textInput-modal-markup"
            >{{$t('send.access_policy_trusted')}}</label>
            <div class="col-sm-5">
              <input
                type="checkbox"
                v-model="configuration.AccessPoliciesTrustednetworks"
                class="form-control"
              >
              <span v-if="errors.AccessPoliciesTrustednetworks.hasError" class="help-block">
                {{$t('validation.validation_failed')}}:
                {{$t('validation.'+errors.AccessPoliciesTrustednetworks.message)}}
              </span>
            </div>
          </div>

          <div :class="['form-group', errors.HeloHost.hasError ? 'has-error' : '']">
            <label
              class="col-sm-2 control-label"
              for="textInput-modal-markup"
            >{{$t('send.helohost')}}</label>
            <div class="col-sm-5">
              <input type="text" v-model="configuration.HeloHost" class="form-control">
              <span v-if="errors.HeloHost.hasError" class="help-block">
                {{$t('validation.validation_failed')}}:
                {{$t('validation.'+errors.HeloHost.message)}}
              </span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="textInput-modal-markup">
              <div
                v-if="view.isSaving"
                class="spinner spinner-sm form-spinner-loader adjust-top-loader"
              ></div>
            </label>
            <div class="col-sm-5">
              <button class="btn btn-primary" type="submit">{{$t('save')}}</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div v-if="smarthosts.length == 0 && view.isLoaded" class="blank-slate-pf white">
      <div class="blank-slate-pf-icon">
        <span class="fa fa-envelope"></span>
      </div>
      <h1>{{$t('send.no_smarthost_found')}}</h1>
      <div class="blank-slate-pf-main-action">
        <button
          @click="openCreateSmarthost()"
          class="btn btn-primary btn-lg"
        >{{$t('send.create_smarthost')}}</button>
      </div>
    </div>

    <h3 v-if="view.isLoaded && smarthosts.length > 0">{{ $t('actions') }}</h3>
    <button
      v-if="view.isLoaded && smarthosts.length > 0"
      @click="openCreateSmarthost()"
      class="btn btn-primary btn-lg"
    >{{$t('send.create_smarthost')}}</button>

    <div v-if="!view.isLoaded" class="spinner spinner-lg view-spinner"></div>

    <h3 v-if="view.isLoaded && smarthosts.length > 0">{{ $t('list') }}</h3>
    <div
      v-if="view.isLoaded && smarthosts.length > 0"
      class="list-group list-view-pf list-view-pf-view no-mg-top mg-top-10"
    >
      <div v-for="(m, mk) in smarthosts" v-bind:key="mk" class="list-group-item">
        <div class="list-view-pf-actions">
          <button
            @click="m.props.status == 'disabled' ? toggleStatus(m) : openEditSmarthost(m)"
            :class="['btn btn-default', m.props.status == 'disabled' ? 'btn-primary' : '']"
          >
            <span
              :class="['fa', m.props.status == 'disabled' ? 'fa-check' : 'fa-pencil', 'span-right-margin']"
            ></span>
            {{m.props.status == 'disabled' ? $t('enable') : $t('edit')}}
          </button>
          <div class="dropup pull-right dropdown-kebab-pf">
            <button
              class="btn btn-link dropdown-toggle"
              type="button"
              id="dropdownKebabRight9"
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="true"
            >
              <span class="fa fa-ellipsis-v"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownKebabRight9">
              <li>
                <a @click="toggleStatus(m)">
                  <span
                    :class="['fa', m.props.status == 'enabled' ? 'fa-lock' : 'fa-check', 'span-right-margin']"
                  ></span>
                  {{m.props.status == 'enabled' ? $t('disable') : $t('enable')}}
                </a>
              </li>
              <li role="presentation" class="divider"></li>
              <li @click="openDeleteSmarthost(m)">
                <a>
                  <span class="fa fa-times span-right-margin"></span>
                  {{$t('delete')}}
                </a>
              </li>
            </ul>
          </div>
        </div>

        <div class="list-view-pf-main-info small-list">
          <div class="list-view-pf-left">
            <span
              :class="['fa', 'list-view-pf-icon-sm', m.SenderType == 'domain' ? 'fa-tags' : 'fa-at']"
            ></span>
          </div>
          <div :class="['list-view-pf-body', m.props.status == 'disabled' ? 'gray' : '']">
            <div class="list-view-pf-description">
              <div class="list-group-item-heading">
                <strong class="big-name">{{m.name}}</strong>
              </div>
            </div>
            <div class="list-view-pf-additional-info rules-info">
              <div class="list-view-pf-additional-info-item">
                <span class="fa fa-server col-sm-3"></span>
                <strong>{{m.props.Host}}</strong>
                <strong class="gray">:</strong>
                <strong>{{m.props.Port}}</strong>
              </div>
              <div class="list-view-pf-additional-info-item">
                <span
                  :class="['fa icon-status', m.props.TlsStatus == 'enabled' ? 'fa-check green' : 'fa-times red']"
                ></span>
                <strong>{{$t('send.tls_status')}}</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" id="createSmarthostModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4
              class="modal-title"
            >{{newSmarthost.isEdit ? $t('send.edit_smarthost') : $t('send.create_smarthost')}}</h4>
          </div>
          <form class="form-horizontal" v-on:submit.prevent="saveSmarthost(newSmarthost)">
            <div class="modal-body">
              <div :class="['form-group', newSmarthost.errors.name.hasError ? 'has-error' : '']">
                <label class="col-sm-3 control-label">
                  {{$t('send.sender')}}
                  <doc-info
                    :placement="'top'"
                    :title="$t('send.sender')"
                    :chapter="'sender_info'"
                    :inline="true"
                  ></doc-info>
                </label>
                <div class="col-sm-9">
                  <input
                    :disabled="newSmarthost.isEdit"
                    type="text"
                    class="form-control"
                    required
                    v-model="newSmarthost.name"
                  >
                  <span
                    v-if="newSmarthost.errors.name.hasError"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+newSmarthost.errors.name.message)}}</span>
                </div>
              </div>

              <legend class="fields-section-header-pf" aria-expanded="true">
                <span class="field-section-toggle-pf">{{$t('connectors.connection_details')}}</span>
              </legend>

              <div :class="['form-group', newSmarthost.errors.Host.hasError ? 'has-error' : '']">
                <label class="col-sm-3 control-label">{{$t('send.host')}}</label>
                <div class="col-sm-9">
                  <input
                    type="text"
                    class="form-control"
                    required
                    v-model="newSmarthost.props.Host"
                  >
                  <span
                    v-if="newSmarthost.errors.Host.hasError"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+newSmarthost.errors.Host.message)}}</span>
                </div>
              </div>
              <div :class="['form-group', newSmarthost.errors.Port.hasError ? 'has-error' : '']">
                <label class="col-sm-3 control-label">{{$t('send.port')}}</label>
                <div class="col-sm-9">
                  <input
                    type="number"
                    class="form-control"
                    required
                    v-model="newSmarthost.props.Port"
                  >
                  <span
                    v-if="newSmarthost.errors.Port.hasError"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+newSmarthost.errors.Port.message)}}</span>
                </div>
              </div>

              <div
                :class="['form-group', newSmarthost.errors.Username.hasError ? 'has-error' : '']"
              >
                <label class="col-sm-3 control-label">{{$t('send.username')}}</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" v-model="newSmarthost.props.Username">
                  <span
                    v-if="newSmarthost.errors.Username.hasError"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+newSmarthost.errors.Username.message)}}</span>
                </div>
              </div>
              <div
                :class="['form-group', newSmarthost.errors.Password.hasError ? 'has-error' : '']"
              >
                <label class="col-sm-3 control-label">{{$t('send.password')}}</label>
                <div class="col-sm-6">
                  <input
                    :type="newSmarthost.togglePass ? 'text' : 'password'"
                    class="form-control"
                    v-model="newSmarthost.props.Password"
                  >
                </div>
                <div class="col-sm-3">
                  <button
                    class="btn btn-primary"
                    type="button"
                    @click="newSmarthost.togglePass = !newSmarthost.togglePass"
                  >
                    <span :class="['fa', !newSmarthost.togglePass ? 'fa-eye' : 'fa-eye-slash']"></span>
                  </button>
                </div>
              </div>
              <div
                :class="['form-group', newSmarthost.errors.TlsStatus.hasError ? 'has-error' : '']"
              >
                <label class="col-sm-3 control-label">{{$t('send.tls_status')}}</label>
                <div class="col-sm-9">
                  <input
                    type="checkbox"
                    class="form-control"
                    v-model="newSmarthost.props.TlsStatus"
                  >
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">{{$t('send.check_credentials')}}</label>
                <div class="col-sm-5">
                  <button
                    @click="checkCredentials()"
                    class="btn btn-primary"
                    type="button"
                  >{{$t('send.check')}}</button>
                  <span
                    v-if="!newSmarthost.isChecking && newSmarthost.isChecked"
                    class="fa fa-check green span-left-margin"
                  ></span>
                  <span
                    v-if="!newSmarthost.isChecking && newSmarthost.checkFail"
                    class="fa fa-times red span-left-margin"
                  ></span>
                  <div
                    v-if="newSmarthost.isChecking"
                    class="spinner spinner-sm span-left-margin check-spinner"
                  ></div>
                </div>
              </div>
            </div>
            <div class="modal-footer no-mg-top">
              <div v-if="newSmarthost.isLoading" class="spinner spinner-sm form-spinner-loader"></div>
              <button class="btn btn-default" type="button" data-dismiss="modal">{{$t('cancel')}}</button>
              <button
                :disabled="!newSmarthost.isEdit && !newSmarthost.isChecked"
                class="btn btn-primary"
                type="submit"
              >{{newSmarthost.isEdit ? $t('edit') : $t('save')}}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal" id="deleteSmarthostModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{$t('send.delete_smarthost')}} {{currentSmarthost.name}}</h4>
          </div>
          <form class="form-horizontal" v-on:submit.prevent="deleteSmarthost(currentSmarthost)">
            <div class="modal-body">
              <div class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('are_you_sure')}}?</label>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-default" type="button" data-dismiss="modal">{{$t('cancel')}}</button>
              <button class="btn btn-danger" type="submit">{{$t('delete')}}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Send",
  mounted() {
    this.getConfiguration();
    this.getSmarthosts();
  },
  data() {
    return {
      view: {
        isLoaded: false,
        isSaving: false,
        opened: false
      },
      configuration: {
        AccessBypassList: [],
        SenderValidation: false,
        AccessPoliciesSmtpauth: false,
        AccessPoliciesTrustednetworks: false,
        HeloHost: ""
      },
      errors: this.initErrors(),
      smarthosts: [],
      newSmarthost: this.initSmarthost(),
      currentSmarthost: {}
    };
  },
  methods: {
    togglePassFn(smart) {
      smart.togglePass = !smart.togglePass;
      this.$forceUpdate();
    },
    initErrors() {
      return {
        AccessBypassList: {
          hasError: false,
          message: ""
        },
        SenderValidation: {
          hasError: false,
          message: ""
        },
        AccessPoliciesSmtpauth: {
          hasError: false,
          message: ""
        },
        AccessPoliciesTrustednetworks: {
          hasError: false,
          message: ""
        },
        HeloHost: {
          hasError: false,
          message: ""
        }
      };
    },
    initSmarthost() {
      return {
        name: "",
        props: {
          Password: "",
          TlsStatus: true,
          Username: "",
          status: "enabled",
          Port: "",
          Host: ""
        },
        isChecked: false,
        isChecking: false,
        checkFail: false,
        togglePass: false,
        errors: this.initSmarthostErrors()
      };
    },
    initSmarthostErrors() {
      return {
        name: {
          hasError: false,
          message: ""
        },
        Password: {
          hasError: false,
          message: ""
        },
        TlsStatus: {
          hasError: false,
          message: ""
        },
        Username: {
          hasError: false,
          message: ""
        },
        status: {
          hasError: false,
          message: ""
        },
        Port: {
          hasError: false,
          message: ""
        },
        Host: {
          hasError: false,
          message: ""
        }
      };
    },
    toggleDetails() {
      this.view.opened = !this.view.opened;
    },
    getConfiguration() {
      var context = this;

      nethserver.exec(
        ["nethserver-mail/send/read"],
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
          context.configuration.SenderValidation =
            success.SenderValidation == "enabled";

          context.configuration.AccessPoliciesSmtpauth =
            success.AccessPoliciesSmtpauth == "enabled";

          context.configuration.AccessPoliciesTrustednetworks =
            success.AccessPoliciesTrustednetworks == "enabled";

          context.configuration.AccessBypassList = success.AccessBypassList.join(
            "\n"
          );

          context.configuration.HeloHost = success.HeloHost;
        },
        function(error) {
          console.error(error);
        },
      );
    },
    saveConfiguration() {
      var context = this;
      var settingsObj = {
        action: "configuration",
        AccessBypassList: context.configuration.AccessBypassList.split("\n"),
        SenderValidation: context.configuration.SenderValidation
          ? "enabled"
          : "disabled",
        AccessPoliciesSmtpauth: context.configuration.AccessPoliciesSmtpauth
          ? "enabled"
          : "disabled",
        AccessPoliciesTrustednetworks: context.configuration
          .AccessPoliciesTrustednetworks
          ? "enabled"
          : "disabled",
        HeloHost: context.configuration.HeloHost
      };

      context.view.isSaving = true;
      context.errors = context.initErrors();
      nethserver.exec(
        ["nethserver-mail/send/validate"],
        settingsObj,
        null,
        function(success) {
          context.view.isSaving = false;

          // notification
          nethserver.notifications.success = context.$i18n.t(
            "send.configuration_updated_ok"
          );
          nethserver.notifications.error = context.$i18n.t(
            "send.configuration_updated_error"
          );

          // update values
          nethserver.exec(
            ["nethserver-mail/send/update"],
            settingsObj,
            function(stream) {
              console.info("settings", stream);
            },
            function(success) {
              context.getConfiguration();
            },
            function(error, data) {
              console.error(error, data);
            },
          );
        },
        function(error, data) {
          var errorData = {};
          context.view.isSaving = false;
          context.errors = context.initErrors();

          try {
            errorData = JSON.parse(data);
            for (var e in errorData.attributes) {
              var attr = errorData.attributes[e];
              context.errors[attr.parameter].hasError = true;
              context.errors[attr.parameter].message = attr.error;
            }
          } catch (e) {
            console.error(e);
          }
        },
      );
    },
    getSmarthosts() {
      var context = this;

      context.view.isLoaded = false;
      nethserver.exec(
        ["nethserver-mail/send/read"],
        {
          action: "list"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }

          context.smarthosts = success.smarthosts;
          for (var s in context.smarthosts) {
            context.smarthosts[s].togglePass = false;
          }

          context.view.isLoaded = true;
        },
        function(error) {
          console.error(error);
        },
      );
    },
    openCreateSmarthost() {
      this.newSmarthost = this.initSmarthost();
      $("#createSmarthostModal").modal("show");
    },
    openEditSmarthost(smart) {
      this.newSmarthost = JSON.parse(JSON.stringify(smart));
      this.newSmarthost.isEdit = true;
      this.newSmarthost.isLoading = false;
      this.newSmarthost.togglePass = false;
      this.newSmarthost.TlsStatus = this.newSmarthost.TlsStatus == "enabled";
      this.newSmarthost.errors = this.initSmarthostErrors();
      $("#createSmarthostModal").modal("show");
    },
    openDeleteSmarthost(smart) {
      this.currentSmarthost = Object.assign({}, smart);
      $("#deleteSmarthostModal").modal("show");
    },
    checkCredentials() {
      var context = this;

      context.newSmarthost.isChecking = true;
      context.$forceUpdate();
      nethserver.exec(
        ["nethserver-mail/send/read"],
        {
          action: "check-credentials",
          Host: context.newSmarthost.props.Host,
          Port: context.newSmarthost.props.Port,
          Username: context.newSmarthost.props.Username,
          Password: context.newSmarthost.props.Password,
          TlsStatus: context.newSmarthost.props.TlsStatus
            ? "enabled"
            : "disabled"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
            context.newSmarthost.isChecking = false;
            context.newSmarthost.isChecked = true;
            context.newSmarthost.checkFail = false;
          } catch (e) {
            console.error(e);
            context.newSmarthost.isChecking = false;
            context.newSmarthost.isChecked = false;
            context.newSmarthost.checkFail = true;
          }
          context.$forceUpdate();
        },
        function(error) {
          console.error(error);
          context.newSmarthost.isChecking = false;
          context.newSmarthost.isChecked = false;
          context.newSmarthost.checkFail = true;
          context.$forceUpdate();
        }
      );
    },
    toggleStatus(smarthost) {
      var context = this;
      // notification
      nethserver.notifications.success = context.$i18n.t(
        "send.smarthost_updated_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "send.smarthost_updated_error"
      );

      // update values
      nethserver.exec(
        ["nethserver-mail/send/update"],
        {
          action: smarthost.props.status == "enabled" ? "disable" : "enable",
          name: smarthost.name
        },
        function(stream) {
          console.info("update-status", stream);
        },
        function(success) {
          // get all
          context.getSmarthosts();
        },
        function(error, data) {
          console.error(error, data);
        }
      );
    },
    saveSmarthost(smarthost) {
      var context = this;

      var smarthostObj = {
        Host: smarthost.props.Host,
        Port: smarthost.props.Port,
        Username: smarthost.props.Username,
        Password: smarthost.props.Password,
        TlsStatus: smarthost.props.TlsStatus ? "enabled" : "disabled",

        status: smarthost.isEdit ? smarthost.props.status : "enabled",
        name: smarthost.name,
        action: smarthost.isEdit ? "update" : "create"
      };

      context.newSmarthost.errors = context.initSmarthostErrors();
      context.newSmarthost.isLoading = true;
      context.$forceUpdate();
      nethserver.exec(
        ["nethserver-mail/send/validate"],
        smarthostObj,
        null,
        function(success) {
          context.newSmarthost.isLoading = false;
          $("#createSmarthostModal").modal("hide");

          // notification
          nethserver.notifications.success = context.$i18n.t(
            "send.smarthost_" +
              (context.newSmarthost.isEdit ? "updated" : "created") +
              "_ok"
          );
          nethserver.notifications.error = context.$i18n.t(
            "send.smarthost_" +
              (context.newSmarthost.isEdit ? "updated" : "created") +
              "_error"
          );

          // update values
          if (smarthost.isEdit) {
            nethserver.exec(
              ["nethserver-mail/send/update"],
              smarthostObj,
              function(stream) {
                console.info("smarthosts", stream);
              },
              function(success) {
                // get smarthosts
                context.getSmarthosts();
              },
              function(error, data) {
                console.error(error, data);
              }
            );
          } else {
            nethserver.exec(
              ["nethserver-mail/send/create"],
              smarthostObj,
              function(stream) {
                console.info("smarthosts", stream);
              },
              function(success) {
                // get smarthosts
                context.getSmarthosts();
              },
              function(error, data) {
                console.error(error, data);
              }
            );
          }
        },
        function(error, data) {
          var errorData = {};
          context.newSmarthost.isLoading = false;
          context.newSmarthost.errors = context.initSmarthostErrors();

          try {
            errorData = JSON.parse(data);
            for (var e in errorData.attributes) {
              var attr = errorData.attributes[e];
              context.newSmarthost.errors[attr.parameter].hasError = true;
              context.newSmarthost.errors[attr.parameter].message = attr.error;
            }
          } catch (e) {
            console.error(e);
          }
          context.$forceUpdate();
        }
      );
    },
    deleteSmarthost(smarthost) {
      var context = this;

      // notification
      nethserver.notifications.success = context.$i18n.t(
        "send.smarthost_deleted_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "send.smarthost_deleted_error"
      );

      $("#deleteSmarthostModal").modal("hide");
      nethserver.exec(
        ["nethserver-mail/send/delete"],
        {
          name: smarthost.name,
          action: "delete"
        },
        function(stream) {
          console.info("smarthosts", stream);
        },
        function(success) {
          // get smarthosts
          context.getSmarthosts();
        },
        function(error, data) {
          console.error(error, data);
        }
      );
    }
  }
};
</script>

<style>
.min-textarea-height {
  min-height: 100px;
}

.icon-status {
  font-size: 14px !important;
}

.read-input {
  border: none;
  background: transparent;
  width: 125px;
}

.check-spinner {
  display: inline-block;
  vertical-align: text-top;
}

.external-smarthost {
  line-height: 22px;
}

.big-name {
  font-size: 16px;
}
</style>
