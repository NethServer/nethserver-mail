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
    <h2>{{ $t('settings.title') }}</h2>
    <doc-info
      :placement="'top'"
      :title="$t('docs.settings')"
      :chapter="'mail'"
      :section="'settings'"
      :inline="false"
    ></doc-info>

    <div v-if="!view.isLoaded" class="spinner spinner-lg"></div>
    <div v-if="view.isLoaded">
      <h3>{{$t('settings.always_bcc')}}</h3>
      <form class="form-horizontal" v-on:submit.prevent="saveSettings()">
        <div :class="['form-group', errors.AlwaysBccStatus.hasError ? 'has-error' : '']">
          <label
            class="col-sm-2 control-label"
            for="textInput-modal-markup"
          >{{$t('settings.always_bcc_switch_label')}}</label>
          <div class="col-sm-5">
            <toggle-button
              class="min-toggle"
              :width="40"
              :height="20"
              :color="{checked: '#0088ce', unchecked: '#bbbbbb'}"
              :value="settings.AlwaysBccStatus"
              :sync="true"
              @change="toggleSettingsAlwaysBcc()"
            />
            <span v-if="errors.AlwaysBccStatus.hasError" class="help-block">
              {{$t('validation.validation_failed')}}:
              {{$t('validation.'+errors.AlwaysBccStatus.message)}}
            </span>
          </div>
        </div>

        <div
          v-if="settings.AlwaysBccStatus"
          :class="['form-group', errors.AlwaysBccAddress.hasError ? 'has-error' : '']"
        >
          <label
            class="col-sm-2 control-label"
            for="textInput-modal-markup"
          >{{$t('settings.always_bcc_address_label')}}</label>
          <div class="col-sm-5">
            <input
              :placeholder="$t('domain.always_bcc_help')"
              type="email"
              v-model="settings.AlwaysBccAddress"
              class="form-control"
            >
            <span v-if="errors.AlwaysBccAddress.hasError" class="help-block">
              {{$t('validation.validation_failed')}}:
              {{$t('validation.'+errors.AlwaysBccAddress.message)}}
            </span>
          </div>
        </div>

        <h3>{{$t('settings.message_size_max')}}</h3>
        <div :class="['form-group', errors.MessageSizeMax.hasError ? 'has-error' : '']">
          <label class="col-sm-2 control-label" for="textInput-modal-markup">{{$t('settings.size')}}</label>
          <div class="col-sm-5">
            <vue-slider
              v-model="settings.MessageSizeMax"
              :data="[10,20,50,100,200,500,1000]"
              :use-keyboard="true"
              :tooltip="'always'"
            ></vue-slider>
            <span v-if="errors.MessageSizeMax.hasError" class="help-block">
              {{$t('validation.validation_failed')}}:
              {{$t('validation.'+errors.MessageSizeMax.message)}}
            </span>
          </div>
        </div>

        <h3>{{$t('settings.message_queue_lifetime')}}</h3>
        <div :class="['form-group', errors.MessageQueueLifetime.hasError ? 'has-error' : '']">
          <label class="col-sm-2 control-label" for="textInput-modal-markup">
            {{$t('settings.lifetime')}}
            <doc-info
              :placement="'top'"
              :title="$t('settings.lifetime_doc')"
              :chapter="'lifetime'"
              :inline="true"
            ></doc-info>
          </label>
          <div class="col-sm-5">
            <vue-slider
              v-model="settings.MessageQueueLifetime"
              :data="['0','1','2','4','7','15','30']"
              :use-keyboard="true"
              :tooltip="'always'"
            ></vue-slider>
            <span v-if="errors.MessageQueueLifetime.hasError" class="help-block">
              {{$t('validation.validation_failed')}}:
              {{$t('validation.'+errors.MessageQueueLifetime.message)}}
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
</template>

<script>
import VueSlider from "vue-slider-component";
import "vue-slider-component/theme/default.css";

export default {
  name: "Settings",
  components: {
    VueSlider
  },
  mounted() {
    this.getSettings();
  },
  data() {
    return {
      view: {
        isLoaded: false,
        isSaving: false
      },
      settings: {
        AlwaysBccStatus: false,
        MessageSizeMax: 0,
        MessageQueueLifetime: 0,
        AlwaysBccAddress: null
      },
      errors: this.initErrors()
    };
  },
  methods: {
    toggleSettingsAlwaysBcc() {
      this.settings.AlwaysBccStatus = !this.settings.AlwaysBccStatus;
    },
    initErrors() {
      return {
        AlwaysBccStatus: {
          hasError: false,
          message: ""
        },
        MessageSizeMax: {
          hasError: false,
          message: ""
        },
        MessageQueueLifetime: {
          hasError: false,
          message: ""
        },
        AlwaysBccAddress: {
          hasError: false,
          message: ""
        }
      };
    },
    getSettings() {
      var context = this;

      context.view.isLoaded = false;
      nethserver.exec(
        ["nethserver-mail/settings/read"],
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
          context.settings.AlwaysBccStatus =
            success.AlwaysBccStatus == "enabled";

          context.settings.MessageSizeMax = success.MessageSizeMax;

          context.settings.MessageQueueLifetime = success.MessageQueueLifetime;

          context.settings.AlwaysBccAddress = success.AlwaysBccAddress;

          context.view.isLoaded = true;
        },
        function(error) {
          console.error(error);
        }
      );
    },
    saveSettings() {
      var context = this;
      var settingsObj = {
        AlwaysBccStatus: context.settings.AlwaysBccStatus
          ? "enabled"
          : "disabled",
        AlwaysBccAddress: context.settings.AlwaysBccAddress,
        MessageSizeMax: context.settings.MessageSizeMax,
        MessageQueueLifetime: context.settings.MessageQueueLifetime
      };

      context.view.isSaving = true;
      context.errors = context.initErrors();
      nethserver.exec(
        ["nethserver-mail/settings/validate"],
        settingsObj,
        null,
        function(success) {
          context.view.isSaving = false;

          // notification
          nethserver.notifications.success = context.$i18n.t(
            "settings.settings_updated_ok"
          );
          nethserver.notifications.error = context.$i18n.t(
            "settings.settings_updated_error"
          );

          // update values
          nethserver.exec(
            ["nethserver-mail/settings/update"],
            settingsObj,
            function(stream) {
              console.info("settings", stream);
            },
            function(success) {
              context.getSettings();
            },
            function(error, data) {
              console.error(error, data);
            }
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
        }
      );
    }
  }
};
</script>

<style>
.adjust-top-loader {
  margin-top: -4px;
}
</style>
