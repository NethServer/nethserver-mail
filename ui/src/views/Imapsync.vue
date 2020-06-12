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
      :section="'mailbox-configuration'"
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
      <h3>{{$t('list')}}</h3>
      <div v-if="!view.isLoaded" class="spinner spinner-lg"></div>
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
              {{ props.row.props.hostname}}
            </span>
          </td>
          <td :class="['fancy', props.row.props.MailStatus == 'enabled' ? '' : 'gray']">
            <span>
              {{ props.row.props.username}}
            </span>
          </td>
          <td>
            <div v-if="props.row.props.password !== '' && props.row.service == 'active'">
              <span class="pficon pficon-on-running span-right-icon"></span>
              {{$t('imapsync.Sync_is_running')}}</div>
            <div v-if="props.row.props.password !== '' && props.row.service == 'stopped'">
              <span class="pficon pficon-paused span-right-icon"></span>
              {{$t('imapsync.Sync_is_stopped')}}</div>
            <div v-if="props.row.props.password === ''" >
              <span class="pficon pficon-off span-right-icon"></span>
              {{$t('imapsync.Sync_is_not_configured')}}
            </div>
          </td>
          <td>
            <button
              v-if="props.row.service == 'stopped' && props.row.props.MailStatus !== 'disabled'"
              @click="openEditUser(props.row)"
              class="btn btn-default"
            >
              <span class="fa fa-pencil span-right-margin"></span>
              {{$t('edit')}}
            </button>
            <button
              v-if="props.row.service == 'active'"
              @click="toggleServiceStop(props.row)"
              class="btn btn-primary"
            >
              <span class="fa pficon-paused span-right-margin"></span>
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
              v-if="props.row.props.MailStatus == 'enabled'"
              class="dropup pull-right dropdown-kebab-pf">
                <button
                  class="btn btn-link dropdown-toggle"
                  :disabled="props.row.service == 'active'"
                  type="button"
                  id="dropdownKebabRight9"
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="true"
                >
                  <span class="fa fa-ellipsis-v"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownKebabRight9">
                  <li @click="openDeleteImapsync(props.row)">
                    <a>
                      <span class="fa fa-times span-right-margin"></span>
                      {{$t('delete')}}
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
                      type="email"
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
                      <span v-if="view.authentication && view.credential" class="fa fa-check green check-ok"></span>
                      <span v-if="view.authentication && !view.credential" class="fa fa-remove red check-ok"></span>
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
    next();
  },
  mounted() {
    this.getAll();
  },
  data() {
    return {
      togglePass: true,
      view: {
        isLoaded: false,
        isInstalling: false,
        advanced: false,
        opened: false,
        credential: false,
        authentication: false,
        menu: {
          installed: false,
          packages: []
        }
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
    };
  },
  methods: {
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

          for (var u in context.usersRows) {
            var user = context.usersRows[u];
            user.aliases = {
              isLoaded: false
            };
          }
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
    toggleAdvancedMode() {
      this.view.advanced = !this.view.advanced;
      this.$forceUpdate();
    },
    openEditUser(user) {
      this.currentUser = JSON.parse(JSON.stringify(user));
      this.currentUser.props.DeleteDestinations =
        this.currentUser.props.DeleteDestination;
      this.currentUser.isLoading = false;
      this.view.advanced = false;
      this.view.credential = false;
      this.view.authentication = false;
      this.togglePass = true;
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

      context.currentUser.isLoading = true;
      // context.$forceUpdate();
      nethserver.exec(
        ["nethserver-mail/imapsync/validate"],
        userObj,
        null,
        function(success) {
          context.currentUser.isLoading = false;
          $("#editUserModal").modal("hide");

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
              // get all
              context.getAll();
            },
            function(error, data) {
              console.error(error, data);
            }
          );
        },
        function(error, data) {
          var errorData = {};
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
        "imapsync.stop_service_ok_error"
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
        },
        function(error, data) {
          context.view.authentication =  true;
          context.view.credential =  false;
        }
      );
    },
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
</style>
