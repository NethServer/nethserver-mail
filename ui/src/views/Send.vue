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
    <doc-info
      :placement="'top'"
      :title="$t('docs.send')"
      :chapter="'send'"
      :section="''"
      :inline="false"
    ></doc-info>

    <h3>{{ $t('send.configuration') }}</h3>
    <div class="panel panel-default">
      <div class="panel-heading">
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
              <textarea v-model="configuration.AccessBypassList" class="form-control access-bypass"></textarea>
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
  </div>
</template>

<script>
export default {
  name: "Send",
  mounted() {
    this.getConfiguration();
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
      errors: this.initErrors()
    };
  },
  methods: {
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
    toggleDetails() {
      this.view.opened = !this.view.opened;
    },
    getConfiguration() {
      var context = this;

      context.view.isLoaded = false;
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

          context.view.isLoaded = true;
        },
        function(error) {
          console.error(error);
        },
        false
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
            false
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
        false
      );
    }
  }
};
</script>

<style scoped>
.access-bypass {
  min-height: 100px;
}
</style>