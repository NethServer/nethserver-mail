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
    <h2>{{ $t('imapsync.title') }}</h2>
    <doc-info
      :placement="'top'"
      :title="$t('docs.imapsync')"
      :chapter="'mail'"
      :section="'imap-synchronization'"
      :inline="false"
    ></doc-info>

    <div v-show="!view.isLoaded" class="spinner spinner-lg"></div>
    <div v-show="!view.menu.installed && view.isLoaded">
      <div class="blank-slate-pf" id>
        <div class="blank-slate-pf-icon">
          <span class="pficon pficon pficon-add-circle-o"></span>
        </div>
        <h1>{{$t('package_required')}}</h1>
        <p>{{$t('package_required_desc')}}.</p>
        <pre>{{view.menu.packages.join(' ')}}</pre>
        <div class="blank-slate-pf-main-action">
          <button
            :disabled="view.isInstalling"
            @click="installPackages()"
            class="btn btn-primary btn-lg"
          >{{view.menu.packages.length == 1 ? $t('install_package') : $t('install_packages')}}</button>
          <div v-if="view.isInstalling" class="spinner spinner-sm"></div>
        </div>
      </div>
    </div>

    <div v-show="view.menu.installed && view.isLoaded">
      <div
        v-if="status['imapsync'] > 0"
        class="alert alert-warning"
      >
        <button type="button" class="close">
          <div class="spinner"></div>
        </button>
        <span class="pficon pficon-warning-triangle-o"></span>
        <strong>{{$t('imapsync.task_in_progress')}}:</strong>
        {{$t('imapsync.imapsync_in_progress')}}.
      </div>
      <h3>{{$t('list')}}</h3>
      <div v-if="!view.isLoaded" class="spinner spinner-lg"></div>
      <button
        v-if="view.isLoaded && Configured"
        @click="syncAll()"
        class="btn btn-primary syncAll"
        >{{$t('imapsync.syncronizeAllUser')}}
      </button>
      <vue-good-table
        v-show="view.isLoaded"
        :customRowsPerPageDropdown="[25,50,100]"
        :perPage="25"
        :columns="usersColumns"
        :rows="usersRows"
        :lineNumbers="false"
        :defaultSortBy="{field: 'name', type: 'asc'}"
        :globalSearch="true"
        :paginate="true"
        styleClass="table"
        :nextText="tableLangsTexts.nextText"
        :prevText="tableLangsTexts.prevText"
        :rowsPerPageText="tableLangsTexts.rowsPerPageText"
        :globalSearchPlaceholder="tableLangsTexts.globalSearchPlaceholder"
        :ofText="tableLangsTexts.ofText"
      >
        <template slot="table-row" slot-scope="props">
          <td :class="['fancy', props.row.props.MailStatus == 'enabled' ? '' : 'gray']">
            <span class="fa fa-user span-right-icon"></span>
            <a
              @click="props.row.props.MailStatus == 'enabled' ? openEditUser(props.row) : undefined"
              :class="[props.row.props.MailStatus == 'enabled' ? '' : 'gray']"
            >
              <strong>{{ props.row.displayname || props.row.name}}</strong>
            </a>
          </td>
          <td :class="['fancy', props.row.props.MailStatus == 'enabled' ? '' : 'gray']">
            <span>
              {{ (props.row.props.hostname) ? props.row.props.hostname : '-'}}
            </span>
          </td>
          <td :class="['fancy', props.row.props.MailStatus == 'enabled' ? '' : 'gray']">
            <span>
              {{ (props.row.props.username) ? props.row.props.username : '-' }}
            </span>
          </td>
          <td>
            <div v-if="props.row.props.password !== '' && props.row.service == 'active'">
              <span class="pficon pficon-on-running span-right-icon"></span>
              {{$t('imapsync.Sync_is_running')}}</div>
            <div v-if="props.row.props.password !== '' && props.row.service == 'stopped'">
              <span :class="['span-right-icon', (props.row.props.LastSyncStatus == 0) ? 'fa fa-check green': 'fa fa-remove red']" ></span>
              {{$t('imapsync.Last_sync_is')}} {{ props.row.props.LastSync | dateFormat }}
              </div>
            <div v-if="props.row.props.password === ''" >
              <span class="pficon pficon-off span-right-icon"></span>
              {{$t('imapsync.Sync_is_not_configured')}}
            </div>
          </td>
          <td>
            <button
              v-if="props.row.service == 'stopped' && props.row.props.MailStatus !== 'disabled'"
              @click="openEditUser(props.row)"
              class="btn btn-default span-right-margin"
            >
              <span class="fa fa-pencil span-right-margin"></span>
              {{$t('edit')}}
            </button>
            <button
              v-if="props.row.service == 'stopped' && props.row.props.MailStatus !== 'disabled' && props.row.props.password !== ''"
              @click="startSyncUser(props.row)"
              class="btn btn btn-primary"
            >
              <span class="pficon pficon-on-running span-right-margin"></span>
              {{$t('imapsync.start')}}
            </button>
            <button
              v-if="props.row.service == 'active'"
              @click="toggleServiceStop(props.row)"
              class="btn btn-danger"
            >
              <span class="fa fa-stop span-right-margin"></span>
              {{$t('imapsync.stop')}}
            </button>
            <button
              v-if="props.row.props.MailStatus == 'disabled'"
              @click="toggleUserStatus(props.row)"
              class="btn btn-primary"
            >
              <span class="fa fa-check span-right-margin"></span>
              {{$t('imapsync.enable')}}
            </button>
            <div
              v-if="props.row.props.MailStatus == 'enabled' && props.row.props.password !== ''"
              class="dropup pull-right dropdown-kebab-pf">
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
                  <li v-if="props.row.service !== 'active'" @click="openDeleteImapsync(props.row)">
                    <a>
                      <span class="fa fa-times span-right-margin"></span>
                      {{$t('delete')}}
                    </a>
                  </li>
                  <li @click="openCountMessages(props.row)">
                    <a>
                      <span class="pficon pficon-migration span-right-margin"></span>
                      {{$t('imapsync.countMessages')}}
                    </a>
                  </li>
                </ul>
            </div>
          </td>
        </template>
      </vue-good-table>
    </div>
    <div class="modal" id="deleteImapSyncModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{$t('imapsync.delete_imapsync_configuration')}} {{currentUser.name}}</h4>
          </div>
          <form class="form-horizontal" v-on:submit.prevent="deleteImasync(currentUser)">
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
    <div class="modal" id="openCountMessagesModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div  class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{$t('imapsync.Synchronization_info')}}</h4>
          </div>
          <h4 v-show="stat.isLoading" class="center">{{$t('imapsync.PleaseWait')}}</h4>
          <div v-show="stat.isLoading" class="spinner spinner-lg"></div>
          <form  class="form-horizontal">
            <div class="modal-body">
              <div v-show="!stat.isLoading">
                <h4>{{$t("imapsync.RemoteAccount")}}: {{stat.remoteName}}</h4>
                <div class="row">
                  <b class="col-sm-4">{{$t("imapsync.Messages")}}</b>
                  <span class="gray">{{stat.host1Messages}}</span>
                </div>
                <div class="row">
                  <b class="col-sm-4">{{$t("imapsync.Size")}}</b>
                  <span class="gray">{{stat.host1Sizes | byteFormat}}</span>
                </div>
                <div class="row">
                  <b class="col-sm-4">{{$t("imapsync.Folders")}}</b>
                  <span class="gray">{{stat.host1Folders}}</span>
                </div>
                <h4>{{$t("imapsync.LocalAccount")}}: {{stat.localName}}</h4>
                <div class="row">
                  <b class="col-sm-4">{{$t("imapsync.Messages")}}</b>
                  <span class="gray">{{stat.host2Messages}}</span>
                </div>
                <div class="row">
                  <b class="col-sm-4">{{$t("imapsync.Size")}}</b>
                  <span class="gray">{{stat.host2Sizes | byteFormat}}</span>
                </div>
                <div class="row">
                  <b class="col-sm-4">{{$t("imapsync.Folders")}}</b>
                  <span class="gray">{{stat.host2Folders}}</span>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-default" type="button" data-dismiss="modal">{{$t('close')}}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal" id="editUserModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{$t('imapsync.edit_user')}} {{currentUser.name}}</h4>
          </div>
          <form class="form-horizontal" v-on:submit.prevent="editUser(currentUser)">
              <div class="modal-body">
                <div class="form-group">
                  <label
                    class="col-sm-3 control-label"
                    for="textInput-modal-markup"
                  >{{$t('imapsync.username')}}</label>
                  <div class="col-sm-9">
                    <input
                      :placeholder="$t('imapsync.remote_email_account_to_fetch')"
                      v-model="currentUser.props.username"
                      class="form-control"
                    >
                  </div>
                </div>

                <div class="form-group">
                  <label
                    class="col-sm-3 control-label"
                    for="textInput-modal-markup"
                  >{{$t('imapsync.password')}}</label>
                  <div class="col-sm-6">
                    <input
                      v-model="currentUser.props.password"
                      :type="togglePass ? 'password' : 'text'"
                      class="form-control"
                    >
                  </div>
                  <div class="col-sm-2">
                    <button class="btn btn-primary" type="button" @click="togglePassHidden()">
                        <span :class="['fa', togglePass ? 'fa-eye-slash' : 'fa-eye']"></span>
                    </button>
                  </div>
                </div>

                <div class="form-group">
                  <label
                    class="col-sm-3 control-label"
                    for="textInput-modal-markup"
                  >{{$t('imapsync.hostname')}}</label>
                  <div class="col-sm-9">
                    <input
                      :placeholder="$t('imapsync.remote_email_server_hostname')"
                      v-model="currentUser.props.hostname"
                      class="form-control"
                    >
                  </div>
                </div>
                <div class="form-group">
                    <label
                        class="col-sm-3 control-label"
                        for="textInput-modal-markup"
                        >{{$t('imapsync.Port')}}
                    </label>
                    <div class="col-sm-9">
                        <select v-model="currentUser.props.Port" class="form-control">
                            <option selected value="143">143</option>
                            <option value="993">993</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label
                        class="col-sm-3 control-label"
                        for="textInput-modal-markup"
                        >{{$t('imapsync.Security')}}
                    </label>
                    <div class="col-sm-9">
                        <select v-model="currentUser.props.Security" class="form-control">
                            <option selected value="tls">{{ $t('imapsync.Security_tls')}}</option>
                            <option value="ssl">{{ $t('imapsync.Security_ssl')}}</option>
                            <option value="no">{{ $t('imapsync.Security_no')}}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                  <form v-on:submit.prevent="testImap()">
                    <label
                        class="col-sm-3 control-label"
                        for="textInput-modal-markup"
                        >{{$t('imapsync.configuration')}}
                    </label>
                    <div class="col-sm-9">
                      <button class="btn btn-primary" type="submit">{{$t('imapsync.check')}}</button>
                      <div v-if="!view.authentication && view.isWaitingAuth" class="spinner spinner-sm form-spinner"></div>
                      <span v-if="view.authentication && view.credential && !view.isWaitingAuth" class="fa fa-check green check-ok"></span>
                      <span v-if="view.authentication && !view.credential && !view.isWaitingAuth" class="fa fa-remove red check-ok"></span>
                    </div>
                  </form>
                </div>
                <div>
                  <legend class="fields-section-header-pf" aria-expanded="true">
                    <span
                      :class="['fa fa-angle-right field-section-toggle-pf', view.advanced ? 'fa-angle-down' : '']"
                    ></span>
                    <a
                      class="field-section-toggle-pf"
                      @click="toggleAdvancedMode()"
                    >{{$t('advanced_mode')}}</a>
                  </legend>
                </div>
                <div v-if="view.advanced" class="form-group">
                  <label
                    class="col-sm-3 control-label"
                    for="textInput-modal-markup"
                  >{{$t('imapsync.DeleteDestination')}}
                  <doc-info
                    :placement="'top'"
                    :title="$t('imapsync.DeleteDestination')"
                    :chapter="'DeleteDestination'"
                    :inline="true"
                  ></doc-info>
                  </label>
                  <div class="col-sm-9">
                    <input
                      type="checkbox"
                      true-value="enabled"
                      false-value="disabled"
                      v-model="currentUser.props.DeleteDestination"
                      class="form-control"
                    >
                  </div>
                </div>
            </div>
            <div class="modal-footer">
              <div v-if="currentUser.isLoading" class="spinner spinner-sm form-spinner-loader"></div>
              <button class="btn btn-default" type="button" data-dismiss="modal">{{$t('cancel')}}</button>
              <button :disabled="!view.credential" class="btn btn-primary" type="submit">{{$t('imapsync.start')}}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "imapsync",
  components: {
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.view.isLoaded = false;
      nethserver.exec(
        ["nethserver-mail/feature/read"],
        {
          name: vm.$route.path.substr(1)
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }

          vm.view.menu = success;
        },
        function(error) {
          console.error(error);
        },
        false
      );
    });
  },
  beforeRouteLeave(to, from, next) {
    $(".modal").modal("hide");
    clearInterval(this.pollingIntervalId);
    next();
  },
  mounted() {
    this.getAll();
    this.getImapStatus();
    this.pollingStatus();
  },
  data() {
    return {
      togglePass: true,
      previousValues:{
        Port: "",
        Security: "",
        hostname: ""
      },
      view: {
        isLoaded: false,
        isInstalling: false,
        advanced: false,
        opened: false,
        credential: false,
        authentication: false,
        isWaitingAuth: false,
        menu: {
          installed: false,
          packages: []
        }
      },
      stat:{
          isLoading: false,
          host1Messages: 0,
          host1Sizes: 0,
          host1Folders: 0,
          host2Messages: 0,
          host2Sizes: 0,
          host2Folders: 0,
          localName: '',
          remoteName: ''
      },
      tableLangsTexts: this.tableLangs(),
      usersColumns: [
        {
          label: this.$i18n.t("imapsync.name"),
          field: "name",
          filterable: true
        },
        {
          label: this.$i18n.t("imapsync.remote_hostname"),
          field: "Hostname",
          filterable: true,
          sortable: false
        },
        {
          label: this.$i18n.t("imapsync.imap_account"),
          field: "username",
          filterable: true,
          sortable: false
        },
        {
          label: this.$i18n.t("imapsync.imapsync_service"),
          field: "services",
          filterable: true,
          sortable: false
        },
        {
          label: this.$i18n.t("action"),
          field: "",
          filterable: true,
          sortable: false
        }
      ],
      usersRows: [],
      Configured: false,
      currentUser: {
        isLoading: false,
        props: {
          DeleteDestination: "disabled",
          Port: 143,
          Security: "tls",
          hostname: "",
          username: "",
          password: ""
        },
        name: "",
        service:"stopped"
      },
      toDelete: {
        name: ""
      },
      status: {
        "imapsync": 0,
      },
      pollingIntervalId: null,
      statusFlag: false,
    };
  },
  methods: {
    getImapStatus() {
      var context = this;
      nethserver.exec(
        ["nethserver-mail/imapsync/read"],
        {
          action: "running-info"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          context.status.imapsync = success.imapsync;
          if (
            context.status.imapsync > 0
          ) {
            context.statusFlag = true;
          }
          if (
            context.statusFlag &&
            context.status.imapsync == 0
          ) {
            context.getImapSyncInfo();
            context.statusFlag = false;
          }
        },
        function(error) {
          console.error(error);
        }
      );
    },
    getImapSyncInfo() {
      var context = this;
      nethserver.exec(
        ["nethserver-mail/imapsync/read"],
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
          context.usersRows = success["users"];
          context.Configured = success.Configured;
          context.view.isLoaded = true;
        },
        function(error) {
          console.error(error);
        }
      );
    },
    pollingStatus() {
      var context = this;
      context.pollingIntervalId = setInterval(function() {
        context.getImapStatus();
      }, 2500);
    },
    togglePassHidden() {
      this.togglePass = !this.togglePass;
      this.$forceUpdate();
    },
    installPackages() {
      this.view.isInstalling = true;
      // notification
      nethserver.notifications.success = this.$i18n.t("packages_installed_ok");
      nethserver.notifications.error = this.$i18n.t("packages_installed_error");

      nethserver.exec(
        ["nethserver-mail/feature/update"],
        {
          name: this.$route.path.substr(1)
        },
        function(stream) {
          console.info("install-package", stream);
        },
        function(success) {
          // reload page
          window.parent.location.reload();
        },
        function(error) {
          console.error(error);
        }
      );
    },
    getIcon(account) {
      switch (account.type) {
        case "user":
          return "fa-user";
      }
    },
    getAll() {
      var context = this;
      context.view.authentication = false;
      context.view.credential = false;
      context.view.isLoaded = false;
      nethserver.exec(
        ["nethserver-mail/imapsync/read"],
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
          context.usersRows = success["users"];
          context.Configured = success.Configured;
          context.view.isLoaded = true;
        },
        function(error) {
          console.error(error);
        }
      );
    },
    openDeleteImapsync(user) {
      this.currentUser = Object.assign({}, user);
      $("#deleteImapSyncModal").modal("show");
    },
    openCountMessages(account) {
      var context = this;

      context.stat.isLoading = true;
      context.stat.host1Messages = 0;
      context.stat.host1Sizes = 0;
      context.stat.host1Folders = 0;
      context.stat.host2Messages = 0;
      context.stat.host2Sizes = 0;
      context.stat.host2Folders = 0;
      context.stat.localName = account.name;
      context.stat.remoteName = account.props.username;

      $("#openCountMessagesModal").modal("show");

      // notification
      nethserver.notifications.success = context.$i18n.t(
        "imapsync.synchronization_informations_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "imapsync.synchronization_informations_error"
      );

        nethserver.exec(
          ["nethserver-mail/imapsync/read"],
          {
            Port: account.props.Port,
            Security: account.props.Security,
            hostname: account.props.hostname,
            username: account.props.username,
            password: account.props.password,
            name: account.name,
            action: "sync-info"
          },
          null,
          function(success) {
            try {
              success = JSON.parse(success);
              context.stat.host1Messages = success.host1Messages
              context.stat.host1Sizes = success.host1Sizes
              context.stat.host1Folders = success.host1Folders
              context.stat.host2Messages = success.host2Messages
              context.stat.host2Sizes = success.host2Sizes
              context.stat.host2Folders = success.host2Folders
              context.stat.isLoading = false;

            } catch (e) {
              console.error(e);
            }
          },
          function(error) {
            console.error(error);
            context.currentUser.isLoading = false;
          }
        );
    },
    toggleAdvancedMode() {
      this.view.advanced = !this.view.advanced;
      this.$forceUpdate();
    },
    openEditUser(user) {
      this.currentUser = JSON.parse(JSON.stringify(user));
      this.currentUser.isLoading = false;
      this.view.advanced = false;
      this.view.credential = false;
      this.view.authentication = false;
      this.togglePass = true;

      //check if a previous form has already used, hence use it
      if ((this.currentUser.props.password) === '' && (this.previousValues.hostname !== '')) {
          this.currentUser.props.Port = this.previousValues.Port;
          this.currentUser.props.Security = this.previousValues.Security;
          this.currentUser.props.hostname = this.previousValues.hostname;
      }

      $("#editUserModal").modal("show");
    },
    editUser() {
      var context = this;

      var userObj = {
        DeleteDestination: context.currentUser.props.DeleteDestination,
        Port: context.currentUser.props.Port,
        Security: context.currentUser.props.Security,
        hostname: context.currentUser.props.hostname,
        username: context.currentUser.props.username,
        password: context.currentUser.props.password,
        name: context.currentUser.name,
        action: "update"
      };

      // save the previous values, it will be used to prefill the form
      context.previousValues.Port = context.currentUser.props.Port;
      context.previousValues.Security = context.currentUser.props.Security;
      context.previousValues.hostname = context.currentUser.props.hostname;

      context.currentUser.isLoading = true;

      // notification
      nethserver.notifications.success = context.$i18n.t(
        "imapsync.user_updated_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "imapsync.user_updated_error"
      );

      // update values
      nethserver.exec(
        ["nethserver-mail/imapsync/update"],
        userObj,
        function(stream) {
          console.info("update", stream);
        },
        function(success) {
          context.currentUser.isLoading = false;
          $("#editUserModal").modal("hide");
          // get all
          context.getAll();
        },
        function(error, data) {
          console.error(error, data);
          context.currentUser.isLoading = false;
        }
      );
    },
    toggleServiceStop(user) {
      var context = this;
      // notification
      nethserver.notifications.success = context.$i18n.t(
        "imapsync.stop_service_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "imapsync.stop_service_error"
      );

      // update values
      nethserver.exec(
        ["nethserver-mail/imapsync/update"],
        {
          name: user.name,
          action: "stop"
        },
        function(stream) {
          console.info("update", stream);
        },
        function(success) {
          // get all
          context.getAll();
        },
        function(error, data) {
          console.error(error, data);
        }
      );
    },
    startSyncUser (user) {
      var context = this;
      // notification
      nethserver.notifications.success = context.$i18n.t(
        "imapsync.start_service_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "imapsync.start_service_error"
      );

      // update values
      nethserver.exec(
        ["nethserver-mail/imapsync/update"],
        {
          name: user.name,
          action: "start"
        },
        function(stream) {
          console.info("update", stream);
        },
        function(success) {
          // get all
          context.getAll();
        },
        function(error, data) {
          console.error(error, data);
        }
      );
    },
    syncAll () {
      var context = this;
      // notification
      nethserver.notifications.success = context.$i18n.t(
        "imapsync.synchronize_all_service_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "imapsync.synchronize_all_service_error"
      );

      // update values
      nethserver.exec(
        ["nethserver-mail/imapsync/update"],
        {
          action: "synchronizeAll"
        },
        function(stream) {
          console.info("update", stream);
        },
        function(success) {
          // get all
          context.getAll();
        },
        function(error, data) {
          console.error(error, data);
        }
      );
    },
    toggleUserStatus(user) {
      var context = this;
      // notification
      nethserver.notifications.success = context.$i18n.t(
        "imapsync.toggle_status_user_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "imapsync.toggle_status_user_error"
      );

      // update values
      nethserver.exec(
        ["nethserver-mail/imapsync/update"],
        {
          name: user.name,
          action: "toggle-user-status"
        },
        function(stream) {
          console.info("update", stream);
        },
        function(success) {
          // get all
          context.getAll();
        },
        function(error, data) {
          console.error(error, data);
        }
      );
    },
    deleteImasync(user) {
      var context = this;

      // notification
      nethserver.notifications.success = context.$i18n.t(
        "imasync.configuration_deleted_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "imapsync.configuration_deleted_error"
      );

      $("#deleteImapSyncModal").modal("hide");
      nethserver.exec(
        ["nethserver-mail/imapsync/update"],
        {
          name: user.name,
          action: "delete"
        },
        function(stream) {
          console.info("imapsync", stream);
        },
        function(success) {
          // get addresss
          context.getAll();
        },
        function(error, data) {
          console.error(error, data);
        }
      );
    },
    testImap() {
      var context = this;

      context.view.authentication =  false;
      context.view.credential = false;
      context.view.isWaitingAuth = true;

      nethserver.exec(
        ["nethserver-mail/imapsync/execute"],
        {
          Port: context.currentUser.props.Port,
          Security: context.currentUser.props.Security,
          hostname: context.currentUser.props.hostname,
          username: context.currentUser.props.username,
          password: context.currentUser.props.password,
          name: context.currentUser.name
        },
        null,
        function(success) {
          context.view.authentication =  true;
          context.view.credential =  true;
          context.view.isWaitingAuth = false;
        },
        function(error, data) {
          context.view.authentication =  true;
          context.view.credential =  false;
          context.view.isWaitingAuth = false;
        }
      );
    }
  }
};
</script>

<style>
.span-right-icon {
  font-size: 15px;
  margin-right: 8px;
}
.check-ok {
  margin-left: 10px;
}
.syncAll {
    position: absolute;
    margin-top: 8px;
    right: 20px;
    z-index: 1;
}
.form-spinner {
    display: inline-block;
    top: 6px;
    left: 6px;
}
.center {
  text-align: center;
}
</style>
