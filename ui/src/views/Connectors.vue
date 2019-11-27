<template>
  <div>
    <h2>{{$t('connectors.title')}}</h2>
    <doc-info
      :placement="'top'"
      :title="$t('docs.connectors')"
      :chapter="'pop3_connector'"
      :section="''"
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
      <div v-if="isEmpty(connectorList) && view.isLoaded" class="blank-slate-pf white">
        <div class="blank-slate-pf-icon">
          <span class="fa fa-plug"></span>
        </div>
        <h1>{{$t('connectors.no_connectors_found')}}</h1>
        <div class="blank-slate-pf-main-action">
          <button
            :disabled="destinations.length == 0"
            @click="openCreateConnector()"
            class="btn btn-primary btn-lg"
          >{{$t('connectors.create_connector')}}</button>
        </div>
      </div>

      <div v-if="!isEmpty(connectorList) && view.isLoaded">
        <h3>{{$t('actions')}}</h3>
        <button
          :disabled="destinations.length == 0"
          @click="openCreateConnector()"
          class="btn btn-primary btn-lg"
        >{{$t('connectors.create_connector')}}</button>
      </div>

      <div class="pf-container" v-if="!isEmpty(connectorList) && view.isLoaded">
        <h3>{{$t('list')}}</h3>

        <div v-for="(data, email, index) in connectorList" v-bind:key="index">
          <div
            id="pf-list-simple-expansion"
            class="list-group list-view-pf list-view-pf-view wizard-pf-contents-title white mg-top-10"
          >
            <div class="list-group-item list-view-pf-expand-active mg-bottom-10">
              <div class="list-group-item-header cursor-initial">
                <div class="list-view-pf-main-info small-list">
                  <div class="list-view-pf-left">
                    <span class="pficon pficon-container-node list-view-pf-icon-sm small-icon"></span>
                  </div>
                  <div class="list-view-pf-body">
                    <div class="list-view-pf-description description-more-space">
                      <div class="list-group-item-heading flex-50">
                        <span class="normal">{{$t('connectors.destination')}}:</span>
                        {{email}}
                      </div>
                    </div>
                    <div class="list-view-pf-additional-info additional-info-less-space">
                      <div class="list-view-pf-additional-info-item">
                        <span class="fa fa-plug"></span>
                        <strong>{{data.length}}</strong>
                        {{$t('connectors.connectors')}}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="list-group-item-container container-fluid no-pd-bottom">
                <ul class="list-group no-border-top no-mg-bottom">
                  <li class="list-group-item transparent">
                    <div class="col-sm-10">
                      <span class="col-sm-4">
                        <strong>{{$t('connectors.server')}}</strong>
                      </span>
                      <span class="col-sm-2">
                        <strong>{{$t('connectors.username')}}</strong>
                      </span>
                      <span class="col-sm-2">
                        <strong>{{$t('connectors.retriever')}}</strong>
                      </span>
                      <span class="col-sm-2">
                        <strong>{{$t('connectors.time')}}</strong>
                      </span>
                    </div>
                    <span class="col-sm-2">
                      <strong>{{$t('actions')}}</strong>
                    </span>
                  </li>
                  <li
                    v-for="(p,i) in data"
                    v-bind:key="i"
                    :class="['list-group-item small-li', p.props.status == 'disabled' ? 'gray' : '']"
                  >
                    <div class="col-sm-10">
                      <span class="col-sm-4">{{p.props.Server}}</span>
                      <span class="col-sm-2">{{p.props.Username}}</span>
                      <span class="col-sm-2">{{$t('connectors.'+p.props.Retriever)}}</span>
                      <span class="col-sm-2">{{p.props.Time}}</span>
                    </div>
                    <div class="col-sm-2">
                      <button
                        @click="p.props.status == 'disabled' ? toggleStatus(p, email) : openEditConnector(p, email, false)"
                        :class="['btn btn-default', p.props.status == 'disabled' ? 'btn-primary' : '']"
                      >
                        <span
                          :class="['fa', p.props.status == 'disabled' ? 'fa-check' : 'fa-pencil', 'span-right-margin']"
                        ></span>
                        {{p.props.status == 'disabled' ? $t('enable') : $t('edit')}}
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
                        <ul class="dropdown-menu dropdown-menu-right">
                          <li>
                            <a @click="toggleStatus(p, email)">
                              <span
                                :class="['fa', p.props.status == 'enabled' ? 'fa-lock' : 'fa-check', 'span-right-margin']"
                              ></span>
                              {{p.props.status == 'enabled' ? $t('disable') : $t('enable')}}
                            </a>
                          </li>
                          <li>
                            <a @click="download(p, email)">
                              <span class="fa fa-download span-right-margin"></span>
                              {{$t('connectors.download_now')}}
                            </a>
                          </li>
                          <li role="presentation" class="divider"></li>
                          <li>
                            <a @click="openDeleteConnector(p, email)">
                              <span class="fa fa-times span-right-margin"></span>
                              {{$t('delete')}}
                            </a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODALS -->
    <div class="modal" id="createConnectorModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4
              class="modal-title"
            >{{newConnector.isEdit ? $t('connectors.edit_connector') : $t('connectors.create_connector')}}</h4>
          </div>
          <form class="form-horizontal" v-on:submit.prevent="saveConnector(newConnector)">
            <div class="modal-body">
              <div :class="['form-group', newConnector.errors.Account.hasError ? 'has-error' : '']">
                <label class="col-sm-3 control-label">{{$t('connectors.destination')}}</label>
                <div class="col-sm-9">
                  <select class="form-control" v-model="newConnector.props.Account">
                    <option
                      v-for="(item, index) in destinations"
                      v-bind:value="item.name"
                      v-bind:key="index"
                    >{{item.displayname}}</option>
                  </select>
                </div>
              </div>

              <legend class="fields-section-header-pf" aria-expanded="true">
                <span class="field-section-toggle-pf">{{$t('connectors.connection_details')}}</span>
              </legend>

              <div
                :class="['form-group', newConnector.errors.Retriever.hasError ? 'has-error' : '']"
              >
                <label class="col-sm-3 control-label">{{$t('connectors.retriever')}}</label>
                <div class="col-sm-9">
                  <select class="form-control" v-model="newConnector.props.Retriever">
                    <option value="SimplePOP3Retriever">{{$t('connectors.SimplePOP3Retriever')}}</option>
                    <option
                      value="SimplePOP3SSLRetriever"
                    >{{$t('connectors.SimplePOP3SSLRetriever')}}</option>
                    <option value="SimpleIMAPRetriever">{{$t('connectors.SimpleIMAPRetriever')}}</option>
                    <option
                      value="SimpleIMAPSSLRetriever"
                    >{{$t('connectors.SimpleIMAPSSLRetriever')}}</option>
                  </select>
                </div>
              </div>
              <div :class="['form-group', newConnector.errors.Server.hasError ? 'has-error' : '']">
                <label class="col-sm-3 control-label">{{$t('connectors.server')}}</label>
                <div class="col-sm-9">
                  <input
                    type="text"
                    class="form-control"
                    required
                    v-model="newConnector.props.Server"
                  />
                  <span
                    v-if="newConnector.errors.Server.hasError"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+newConnector.errors.Server.message)}}</span>
                </div>
              </div>
              <div
                :class="['form-group', newConnector.errors.Username.hasError ? 'has-error' : '']"
              >
                <label class="col-sm-3 control-label">{{$t('connectors.username')}}</label>
                <div class="col-sm-9">
                  <input
                    type="text"
                    class="form-control"
                    required
                    v-model="newConnector.props.Username"
                  />
                  <span
                    v-if="newConnector.errors.Username.hasError"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+newConnector.errors.Username.message)}}</span>
                </div>
              </div>
              <div
                :class="['form-group', newConnector.errors.Password.hasError ? 'has-error' : '']"
              >
                <label class="col-sm-3 control-label">{{$t('connectors.password')}}</label>
                <div class="col-sm-6">
                  <input
                    :type="newConnector.togglePass ? 'text' : 'password'"
                    class="form-control"
                    required
                    v-model="newConnector.props.Password"
                  />
                  <span
                    v-if="newConnector.errors.Password.hasError"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+newConnector.errors.Password.message)}}</span>
                </div>
                <div class="col-sm-3">
                  <button class="btn btn-primary" type="button" @click="togglePassMode()">
                    <span :class="['fa', !newConnector.togglePass ? 'fa-eye' : 'fa-eye-slash']"></span>
                  </button>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">{{$t('connectors.check_credentials')}}</label>
                <div class="col-sm-5">
                  <button
                    @click="checkCredentials()"
                    class="btn btn-primary"
                    type="button"
                  >{{$t('connectors.check')}}</button>
                  <span
                    v-if="!newConnector.isChecking && newConnector.isChecked"
                    class="fa fa-check green span-left-margin"
                  ></span>
                  <span
                    v-if="!newConnector.isChecking && newConnector.checkFail"
                    class="fa fa-times red span-left-margin"
                  ></span>
                  <div
                    v-if="newConnector.isChecking"
                    class="spinner spinner-sm span-left-margin check-spinner"
                  ></div>
                </div>
              </div>
              <legend class="fields-section-header-pf" aria-expanded="true">
                <span
                  :class="['fa fa-angle-right field-section-toggle-pf', newConnector.advanced ? 'fa-angle-down' : '']"
                ></span>
                <a
                  class="field-section-toggle-pf"
                  @click="toggleAdvancedMode()"
                >{{$t('advanced_mode')}}</a>
              </legend>

              <div
                v-show="newConnector.advanced"
                :class="['form-group', newConnector.errors.Time.hasError ? 'has-error' : '']"
              >
                <label class="col-sm-3 control-label">{{$t('connectors.time')}}</label>
                <div class="col-sm-9">
                  <select class="form-control" v-model="newConnector.props.Time">
                    <option value="5">{{$t('connectors.minutes_5')}}</option>
                    <option value="10">{{$t('connectors.minutes_10')}}</option>
                    <option value="15">{{$t('connectors.minutes_15')}}</option>
                    <option value="30">{{$t('connectors.minutes_30')}}</option>
                    <option value="60">{{$t('connectors.minutes_60')}}</option>
                  </select>
                </div>
              </div>

              <div
                v-show="newConnector.advanced"
                :class="['form-group', newConnector.errors.Delete.hasError ? 'has-error' : '']"
              >
                <label class="col-sm-3 control-label">{{$t('connectors.delete')}}</label>
                <div class="col-sm-9">
                  <select class="form-control" v-model="newConnector.props.Delete">
                    <option value="-1">{{$t('connectors.never')}}</option>
                    <option value="0">{{$t('connectors.now')}}</option>
                    <option value="1">{{$t('connectors.days_1')}}</option>
                    <option value="2">{{$t('connectors.days_2')}}</option>
                    <option value="3">{{$t('connectors.days_3')}}</option>
                    <option value="7">{{$t('connectors.days_7')}}</option>
                    <option value="30">{{$t('connectors.days_30')}}</option>
                    <option value="90">{{$t('connectors.days_90')}}</option>
                    <option value="360">{{$t('connectors.days_360')}}</option>
                  </select>
                </div>
              </div>

              <div
                v-show="newConnector.advanced"
                :class="['form-group', newConnector.errors.Delete.hasError ? 'has-error' : '']"
              >
                <label class="col-sm-3 control-label">{{$t('connectors.filtercheck')}}</label>
                <div class="col-sm-9">
                  <input
                    type="checkbox"
                    v-model="newConnector.props.FilterCheck"
                    true-value="enabled"
                    false-value="disabled"
                    class="form-control"
                  />
                  <span
                    v-if="newConnector.errors.FilterCheck.hasError"
                    class="help-block"
                  >{{newConnector.errors.FilterCheck.message}}</span>
                </div>
              </div>
            </div>
            <div class="modal-footer no-mg-top">
              <div v-if="newConnector.isLoading" class="spinner spinner-sm form-spinner-loader"></div>
              <button class="btn btn-default" type="button" data-dismiss="modal">{{$t('cancel')}}</button>
              <button
                :disabled="!newConnector.isEdit && !newConnector.isChecked"
                class="btn btn-primary"
                type="submit"
              >{{newConnector.isEdit ? $t('edit') : $t('save')}}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal" id="deleteConnectorModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{$t('connectors.delete_connector')}} {{currentConnector.name}}</h4>
          </div>
          <form class="form-horizontal" v-on:submit.prevent="deleteConnector(currentConnector)">
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

    <div
      class="modal"
      id="downloadConnectorModal"
      tabindex="-1"
      role="dialog"
      data-backdrop="static"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{$t('connectors.download_output')}} {{currentConnector.name}}</h4>
          </div>
          <form class="form-horizontal">
            <div class="modal-body">
              <div class="form-group">
                <div class="col-sm-12">
                  <div v-show="!currentConnector.downloadLogs" class="spinner spinner-sm"></div>
                  <pre
                    id="logs-output"
                    v-show="currentConnector.downloadLogs"
                    class="prettyprint command-output"
                  >{{currentConnector.downloadLogs}}</pre>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button
                @click="cleanLastLog()"
                class="btn btn-default"
                type="button"
                data-dismiss="modal"
              >{{$t('close')}}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Connectors",
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
  mounted() {
    this.getConnectors();
    this.getDestinations();
  },
  beforeRouteLeave(to, from, next) {
    $(".modal").modal("hide");
    next();
  },
  data() {
    return {
      view: {
        isLoaded: false,
        isInstalling: false,
        menu: {
          installed: false,
          packages: []
        }
      },
      connectorList: null,
      newConnector: this.initConnector(),
      currentConnector: {},
      destinations: []
    };
  },
  methods: {
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
    isEmpty(obj) {
      return jQuery.isEmptyObject(obj);
    },
    initConnector() {
      return {
        props: {
          Server: "",
          Delete: 0,
          Time: 30,
          status: "enabled",
          Account: "",
          Password: "",
          Retriever: "SimplePOP3Retriever",
          Username: "",
          FilterCheck: "enabled"
        },
        name: "",
        errors: this.initErrors(),
        hasError: false,
        isEdit: false,
        isChecked: false,
        isChecking: false,
        checkFail: false,
        downloadLogs: null,
        togglePass: false
      };
    },
    initErrors() {
      return {
        name: {
          hasError: false,
          message: ""
        },
        Server: {
          hasError: false,
          message: ""
        },
        Delete: {
          hasError: false,
          message: ""
        },
        Time: {
          hasError: false,
          message: ""
        },
        status: {
          hasError: false,
          message: ""
        },
        Account: {
          hasError: false,
          message: ""
        },
        Username: {
          hasError: false,
          message: ""
        },
        Password: {
          hasError: false,
          message: ""
        },
        Retriever: {
          hasError: false,
          message: ""
        },
        FilterCheck: {
          hasError: false,
          message: ""
        }
      };
    },
    toggleAdvancedMode() {
      this.newConnector.advanced = !this.newConnector.advanced;
      this.$forceUpdate();
    },
    getDestinations() {
      var context = this;

      nethserver.exec(
        ["nethserver-mail/mailbox/read"],
        {
          action: "list",
          expand: false
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          context.destinations = success.users;
        },
        function(error) {
          console.error(error);
        }
      );
    },
    getConnectors() {
      var context = this;

      context.view.isLoaded = false;
      nethserver.exec(
        ["nethserver-mail/connectors/read"],
        {
          action: "list"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
            context.view.isLoaded = true;
            context.connectorList = success.connectors;
          } catch (e) {
            console.error(e);
            context.view.isLoaded = true;
          }
        },
        function(error) {
          console.error(error);
          context.view.isLoaded = true;
        }
      );
    },
    checkCredentials() {
      var context = this;

      context.newConnector.isChecking = true;
      context.$forceUpdate();
      nethserver.exec(
        ["nethserver-mail/connectors/read"],
        {
          action: "check-credentials",
          Retriever: context.newConnector.props.Retriever,
          Server: context.newConnector.props.Server,
          Password: context.newConnector.props.Password,
          Username: context.newConnector.props.Username
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
            context.newConnector.isChecking = false;
            context.newConnector.isChecked = true;
            context.newConnector.checkFail = false;
          } catch (e) {
            console.error(e);
            context.newConnector.isChecking = false;
            context.newConnector.isChecked = false;
            context.newConnector.checkFail = true;
          }
          context.$forceUpdate();
        },
        function(error) {
          console.error(error);
          context.newConnector.isChecking = false;
          context.newConnector.isChecked = false;
          context.newConnector.checkFail = true;
          context.$forceUpdate();
        }
      );
    },
    openCreateConnector() {
      this.newConnector = this.initConnector();
      $("#createConnectorModal").modal("show");
    },
    openEditConnector(conn, email) {
      this.newConnector = Object.assign({}, conn);
      this.newConnector.isEdit = true;
      this.newConnector.isLoading = false;
      this.newConnector.togglePass = false;
      this.newConnector.errors = this.initErrors();
      $("#createConnectorModal").modal("show");
    },
    openDeleteConnector(conn) {
      this.currentConnector = Object.assign({}, conn);
      $("#deleteConnectorModal").modal("show");
    },
    saveConnector(connector) {
      var context = this;

      var connectorObj = {
        Server: connector.props.Server,
        Delete: connector.props.Delete,
        Time: connector.props.Time,
        status: connector.props.status ? "enabled" : "disabled",
        Account: connector.props.Account,
        Password: connector.props.Password,
        Retriever: connector.props.Retriever,
        Username: connector.props.Username,
        FilterCheck: connector.props.FilterCheck,
        name: connector.isEdit ? connector.name : null,
        action: connector.isEdit ? "update" : "create"
      };

      context.newConnector.isLoading = true;
      context.$forceUpdate();
      nethserver.exec(
        ["nethserver-mail/connectors/validate"],
        connectorObj,
        null,
        function(success) {
          context.newConnector.isLoading = false;
          $("#createConnectorModal").modal("hide");

          // notification
          nethserver.notifications.success = context.$i18n.t(
            "connectors.connector_" +
              (context.newConnector.isEdit ? "updated" : "created") +
              "_ok"
          );
          nethserver.notifications.error = context.$i18n.t(
            "connectors.connector_" +
              (context.newConnector.isEdit ? "updated" : "created") +
              "_error"
          );

          // update values
          if (connector.isEdit) {
            nethserver.exec(
              ["nethserver-mail/connectors/update"],
              connectorObj,
              function(stream) {
                console.info("connectors", stream);
              },
              function(success) {
                // get connectors
                context.getConnectors();
              },
              function(error, data) {
                console.error(error, data);
              }
            );
          } else {
            nethserver.exec(
              ["nethserver-mail/connectors/create"],
              connectorObj,
              function(stream) {
                console.info("connectors", stream);
              },
              function(success) {
                // get connectors
                context.getConnectors();
              },
              function(error, data) {
                console.error(error, data);
              }
            );
          }
        },
        function(error, data) {
          var errorData = {};
          context.newConnector.isLoading = false;
          context.newConnector.errors = context.initErrors();

          try {
            errorData = JSON.parse(data);
            for (var e in errorData.attributes) {
              var attr = errorData.attributes[e];
              context.newConnector.errors[attr.parameter].hasError = true;
              context.newConnector.errors[attr.parameter].message = attr.error;
            }
          } catch (e) {
            console.error(e);
          }
        }
      );
    },
    deleteConnector(connector) {
      var context = this;

      // notification
      nethserver.notifications.success = context.$i18n.t(
        "connectors.connector_deleted_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "connectors.connector_deleted_error"
      );

      $("#deleteConnectorModal").modal("hide");
      nethserver.exec(
        ["nethserver-mail/connectors/delete"],
        {
          name: connector.name,
          action: "delete"
        },
        function(stream) {
          console.info("connectors", stream);
        },
        function(success) {
          // get connectors
          context.getConnectors();
        },
        function(error, data) {
          console.error(error, data);
        }
      );
    },
    toggleStatus(connector) {
      var context = this;
      // notification
      nethserver.notifications.success = context.$i18n.t(
        "connectors.connector_updated_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "connectors.connector_updated_error"
      );

      // update values
      nethserver.exec(
        ["nethserver-mail/connectors/update"],
        {
          action: connector.props.status == "enabled" ? "disable" : "enable",
          name: connector.name
        },
        function(stream) {
          console.info("update-status", stream);
        },
        function(success) {
          // get all
          context.getConnectors();
        },
        function(error, data) {
          console.error(error, data);
        }
      );
    },
    download(connector) {
      var context = this;
      context.currentConnector.name = connector.name;
      context.currentConnector.downloadLogs = "";

      $("#downloadConnectorModal").modal("show");
      context.$forceUpdate();
      nethserver.execRaw(
        ["nethserver-mail/connectors/execute"],
        {
          name: connector.name,
          action: "run"
        },
        function(stream) {
          console.info("execute", stream);
          if (stream) {
            context.currentConnector.downloadLogs += stream;
          }

          document.getElementById(
            "logs-output"
          ).scrollTop = document.getElementById("logs-output").scrollHeight;

          context.$forceUpdate();
        },
        function(success) {
          // get all
          context.getConnectors();
        },
        function(error, data) {
          console.error(error, data);
        }
      );
    },
    cleanLastLog() {
      this.currentConnector.downloadLogs = null;
    },
    togglePassMode() {
      this.newConnector.togglePass = !this.newConnector.togglePass;
      this.$forceUpdate();
    }
  }
};
</script>

<style scoped>
.list-group-item-heading,
.list-group-item-text {
  width: calc(50% - 20px) !important;
  font-size: 16px !important;
}

.normal {
  font-weight: 500;
}

.small-icon {
  font-size: 12px !important;
  height: 25px !important;
  width: 25px !important;
}

.small-icon:before {
  line-height: 20px !important;
}

.list-view-pf .list-group-item:first-child {
  border-top: 1px solid transparent;
}

.list-view-pf .list-group-item:hover {
  background-color: #edf8ff;
  border-left-color: transparent;
  border-right-color: transparent;
}

.transparent:hover {
  background-color: transparent !important;
}

.no-pd-bottom {
  padding-bottom: 0 !important;
}

.no-border-top {
  border-top: 0 !important;
}

.no-mg-bottom {
  margin-bottom: 0 !important;
}

.mg-bottom-10 {
  margin-bottom: 10px !important;
}

.mg-top-10 {
  margin-top: 10px !important;
}

.list-group.list-view-pf {
  border-top: 0;
}

.list-view-pf .list-group-item {
  border-top: 1px solid #ededec;
}

.small-li {
  padding-top: 3px !important;
  padding-bottom: 3px !important;
}

.list-view-pf .list-group-item.list-view-pf-expand-active:first-child {
  border-top-color: #bbb;
}

.list-view-pf .list-group-item.list-view-pf-expand-active {
  border: 1px solid #bbb;
}

.cursor-initial {
  cursor: text;
}

.check-spinner {
  display: inline-block;
  vertical-align: text-top;
}

.prettyprint {
  max-height: 450px;
}

.command-output {
  background: black;
  color: #cecece;
  font-family: monospace;
  min-height: 175px;
  resize: vertical;
}
</style>
