<template>
<div>
    <h2>{{$t('connectors.title')}}</h2>

    <div v-if="!view.isLoaded" class="spinner spinner-lg view-spinner"></div>

    <div v-if="!connectorList && view.isLoaded" class="blank-slate-pf white">
        <div class="blank-slate-pf-icon">
            <span class="fa fa-plug"></span>
        </div>
        <h1>{{$t('connectors.no_pf_found')}}</h1>
        <p>{{$t('connectors.no_pf_found_text')}}.</p>
        <div class="blank-slate-pf-main-action">
            <button @click="openCreateConnector()" class="btn btn-primary btn-lg">{{$t('connectors.create_connector')}}</button>
        </div>
    </div>

    <div v-if="connectorList && view.isLoaded">
        <h3>{{$t('actions')}}</h3>
        <button @click="openCreateConnector()" class="btn btn-primary btn-lg">{{$t('connectors.create_connector')}}</button>
    </div>

    <div class="pf-container" v-if="connectorList && view.isLoaded">
        <h3>{{$t('list')}}</h3>

        <div v-for="(data, email, index) in connectorList">
            <div id="pf-list-simple-expansion" class="list-group list-view-pf list-view-pf-view wizard-pf-contents-title white mg-top-10">
                <div class="list-group-item list-view-pf-expand-active no-shadow mg-bottom-10">
                    <div class="list-group-item-header cursor-initial">
                        <div class="list-view-pf-main-info small-list">
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
                                    <span class="col-sm-2">
                                        <strong>{{$t('connectors.address')}}</strong>
                                    </span>
                                    <span class="col-sm-2">
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
                            <li v-for="(p,i) in data" v-bind:key="i" :class="['list-group-item small-li', p.props.status == 'disabled' ? 'gray' : '']">
                                <div class="col-sm-10">
                                    <span class="col-sm-2">{{p.name}}</span>
                                    <span class="col-sm-2">{{p.props.Server}}</span>
                                    <span class="col-sm-2">{{p.props.Username}}</span>
                                    <span class="col-sm-2">{{$t('connectors.'+p.props.Retriever)}}</span>
                                    <span class="col-sm-2">{{p.props.Time}}</span>
                                </div>
                                <div class="col-sm-2">
                                    <button @click="p.props.status == 'disabled' ? enableConnector(p, email) : openEditConnector(p, email, false)" :class="['btn btn-default', p.props.status == 'disabled' ? 'btn-primary' : '']">
                                        <span :class="['fa', p.props.status == 'disabled' ? 'fa-check' : 'fa-edit', 'span-right-margin']"></span>
                                        {{p.props.status == 'disabled' ? $t('enable') : $t('edit')}}
                                    </button>
                                    <div class="dropup pull-right dropdown-kebab-pf">
                                        <button class="btn btn-link dropdown-toggle" type="button" id="dropdownKebabRight9" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            <span class="fa fa-ellipsis-v"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li>
                                                <a @click="enableConnector(p, email)">
                                                    <span :class="['fa', p.props.status == 'enabled' ? 'fa-lock' : 'fa-check', 'span-right-margin']"></span>
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

    <!-- MODALS -->
    <div class="modal" id="createConnectorModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{newConnector.isEdit ? $t('connectors.edit_connector') : $t('connectors.create_connector')}}</h4>
                </div>
                <form class="form-horizontal" v-on:submit.prevent="saveConnector()">
                    <div class="modal-body">
                        <div :class="['form-group', newConnector.errors.name.hasError ? 'has-error' : '']">
                            <label class="col-sm-3 control-label">{{$t('connectors.address')}}</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" required v-model="newConnector.name">
                            </div>
                        </div>
                        <div :class="['form-group', newConnector.errors.Account.hasError ? 'has-error' : '']">
                            <label class="col-sm-3 control-label">{{$t('connectors.destination')}}</label>
                            <div class="col-sm-9">
                                <select class="form-control" v-model="newConnector.Account">
                                  <option v-for="item in destinations" v-bind:value="item.name">{{item.displayname}}</option>
                              </select>
                            </div>
                        </div>
                        <div :class="['form-group', newConnector.errors.Retriever.hasError ? 'has-error' : '']">
                            <label class="col-sm-3 control-label">{{$t('connectors.retriever')}}</label>
                            <div class="col-sm-9">
                                <select class="form-control" v-model="newConnector.Retriever">
                                    <option value="SimplePOP3Retriever">{{$t('connectors.SimplePOP3Retriever')}}</option>
                                    <option value="SimplePOP3SSLRetriever">{{$t('connectors.SimplePOP3SSLRetriever')}}</option>
                                    <option value="SimpleIMAPRetriever">{{$t('connectors.SimpleIMAPRetriever')}}</option>
                                    <option value="SimpleIMAPSSLRetriever">{{$t('connectors.SimpleIMAPSSLRetriever')}}</option>
                                </select>
                            </div>
                        </div>
                        <div :class="['form-group', newConnector.errors.Server.hasError ? 'has-error' : '']">
                            <label class="col-sm-3 control-label">{{$t('connectors.server')}}</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" required v-model="newConnector.Server">
                            </div>
                        </div>
                        <div :class="['form-group', newConnector.errors.Username.hasError ? 'has-error' : '']">
                            <label class="col-sm-3 control-label">{{$t('connectors.username')}}</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" required v-model="newConnector.Username">
                            </div>
                        </div>
                        <div :class="['form-group', newConnector.errors.Password.hasError ? 'has-error' : '']">
                            <label class="col-sm-3 control-label">{{$t('connectors.password')}}</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" required v-model="newConnector.Password">
                            </div>
                        </div>
                        <legend class="fields-section-header-pf" aria-expanded="true">
                            <span :class="['fa fa-angle-right field-section-toggle-pf', newConnector.advanced ? 'fa-angle-down' : '']"></span>
                            <a class="field-section-toggle-pf" @click="toggleAdvancedMode()">{{$t('advanced_mode')}}</a>
                        </legend>

                        <div v-show="newConnector.advanced" :class="['form-group', newConnector.errors.Time.hasError ? 'has-error' : '']">
                            <label class="col-sm-3 control-label">{{$t('connectors.time')}}</label>
                            <div class="col-sm-9">
                                <select class="form-control" v-model="newConnector.Time">
                                    <option value="5">{{$t('connectors.minutes_5')}}</option>
                                    <option value="10">{{$t('connectors.minutes_10')}}</option>
                                    <option value="15">{{$t('connectors.minutes_15')}}</option>
                                    <option value="30">{{$t('connectors.minutes_30')}}</option>
                                    <option value="60">{{$t('connectors.minutes_60')}}</option>
                                </select>
                            </div>
                        </div>

                        <div v-show="newConnector.advanced" :class="['form-group', newConnector.errors.Delete.hasError ? 'has-error' : '']">
                            <label class="col-sm-3 control-label">{{$t('connectors.delete')}}</label>
                            <div class="col-sm-9">
                                <select class="form-control" v-model="newConnector.Delete">
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

                        <div v-show="newConnector.advanced" :class="['form-group', newConnector.errors.Delete.hasError ? 'has-error' : '']">
                            <label class="col-sm-3 control-label">{{$t('connectors.filtercheck')}}</label>
                            <div class="col-sm-9">
                                <input type="checkbox" v-model="newConnector.FilterCheck" true-value="enabled" false-value="disabled" class="form-control">
                                <span v-if="newConnector.errors.FilterCheck.hasError" class="help-block">{{newConnector.errors.FilterCheck.message}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer no-mg-top">
                        <div v-if="newConnector.isLoading" class="spinner spinner-sm form-spinner-loader"></div>
                        <button class="btn btn-default" type="button" data-dismiss="modal">{{$t('cancel')}}</button>
                        <button class="btn btn-primary" type="submit">{{newConnector.isEdit ? $t('edit') : newConnector.isDuplicate ? $t('connector.duplicate') : $t('save')}}</button>
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
    mounted() {
        this.getConnectors();
    },
    beforeRouteLeave(to, from, next) {
        $(".modal").modal("hide");
        next();
    },
    data() {
        return {
            view: {
                isLoaded: false
            },
            connectorList: null,
            newConnector: this.initConnector(),
            destinations: this.getDestinations()
        };
    },
    methods: {
      saveConnector() {

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
        getConnectors() {
            var context = this;

            context.view.isLoaded = false;
            nethserver.exec(
                ["nethserver-mail/connectors/read"], {
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
        openCreateConnector() {
            this.newConnector = this.initConnector();
            $("#createConnectorModal").modal("show");
        },
        initConnector() {
            return {
                Server: "",
                Delete: 0,
                Time: 30,
                status: "enabled",
                Account: "",
                Password: "",
                Retriever: "SimplePOP3Retriever",
                Username: "",
                FilterCheck: "enabled",
                name: "",
                errors: this.initErrors(),
                hasError: false,
                isEdit: false
            }
        },
        toggleAdvancedMode() {
            this.newConnector.advanced = !this.newConnector.advanced;
            this.$forceUpdate();
        },
    }
}
</script>
