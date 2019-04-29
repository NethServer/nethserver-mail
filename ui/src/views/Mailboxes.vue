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
    <h1>{{ $t('mailboxes.title') }}</h1>
    <doc-info
      :placement="'top'"
      :title="$t('docs.mailboxes')"
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
      <h3>{{ $t('mailboxes.configuration') }}</h3>
      <div class="panel panel-default">
        <div class="panel-heading">
          <button @click="openConfigure()" class="btn btn-primary right">{{$t('configure')}}</button>
          <span class="panel-title">{{$t('mailboxes.configuration_settings')}}</span>
        </div>
      </div>

      <ul class="nav nav-tabs nav-tabs-pf">
        <li>
          <a
            @click="initListeners(0)"
            class="nav-link"
            data-toggle="tab"
            href="#users-tab"
            id="users-tab-parent"
          >{{$t('mailboxes.users')}}</a>
        </li>
        <li>
          <a
            @click="initListeners(1)"
            class="nav-link"
            data-toggle="tab"
            href="#groups-tab"
            id="groups-tab-parent"
          >{{$t('mailboxes.groups')}}</a>
        </li>
        <li>
          <a
            @click="initListeners(2)"
            class="nav-link"
            data-toggle="tab"
            href="#public-tab"
            id="public-tab-parent"
          >{{$t('mailboxes.public')}}</a>
        </li>
      </ul>

      <div class="tab-content">
        <div
          class="tab-pane fade active"
          id="users-tab"
          role="tabpanel"
          aria-labelledby="hosts-tab"
        >
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
                <a
                  tabindex="0"
                  role="button"
                  :data-toggle="props.row.props.MailStatus == 'enabled' ? 'popover': ''"
                  data-html="true"
                  data-placement="top"
                  :title="$t('mailboxes.aliases')"
                  :id="'popover-'+props.row.name | sanitize"
                  @click="aliasDetails(props.row)"
                  class="span-left-margin"
                >{{$t('mailboxes.aliases')}}</a>
                <span
                  v-if="props.row.props.Description && props.row.props.Description.length > 0"
                  class="gray span-left-margin"
                >({{ props.row.props.Description}})</span>
              </td>
              <td class="fancy quota-min-width">
                <div class="progress-description adjust-progress-description">
                  <strong>{{$t('mailboxes.usage')}}:</strong>
                  <span
                    class="gray span-left-margin"
                  >{{props.row.quota.messages}} ({{$t('mailboxes.messages')}})</span>
                </div>
                <div class="progress progress-xs progress-label-top-right">
                  <div
                    :class="['progress-bar', props.row.props.MailStatus == 'disabled' ? 'back-gray' : '']"
                    role="progressbar"
                    aria-valuenow="42.7"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    :style="'width:'+ props.row.quota.percentage +'%;'"
                  >
                    <span
                      class="adjust-progress-span"
                    >{{props.row.quota.percentage}}% ({{props.row.quota.size | byteFormat}} {{$t('of')}} {{props.row.quota.maximum | byteFormat}})</span>
                  </div>
                </div>
              </td>
              <td :class="['fancy', props.row.props.MailStatus == 'enabled' ? '' : 'gray']">
                <span
                  v-if="props.row.props.MailForwardAddress.length > 0 && props.row.props.MailForwardStatus == 'enabled'"
                >
                  <span class="fa fa-share span-right-margin"></span>
                  {{ props.row.props.MailForwardAddress.join(', ') }}
                </span>
                <span v-else>-</span>
              </td>
              <td :class="['fancy', props.row.props.MailStatus == 'enabled' ? '' : 'gray']">
                <span
                  v-if="props.row.connectors.length > 0"
                  class="fa fa-envelope span-right-margin"
                ></span>
                <span v-else>-</span>
                <a
                  role="button"
                  data-toggle="popover"
                  data-html="true"
                  data-placement="top"
                  :title="$t('mailboxes.connectors')"
                  :id="'popover-connectors-'+c.name | sanitize"
                  v-for="(c, ck) in props.row.connectors"
                  v-bind:key="ck"
                  @click="connDetails(c)"
                >{{c.name}}</a>
              </td>
              <td>
                <button
                  v-if="props.row.props.MailStatus == 'enabled'"
                  @click="openEditUser(props.row)"
                  class="btn btn-default"
                >
                  <span class="fa fa-pencil span-right-margin"></span>
                  {{$t('edit')}}
                </button>
                <button
                  v-if="props.row.props.MailStatus == 'disabled'"
                  @click="toggleUserStatus(props.row)"
                  class="btn btn-primary"
                >
                  <span class="fa fa-check span-right-margin"></span>
                  {{$t('enable')}}
                </button>
              </td>
            </template>
          </vue-good-table>
        </div>

        <div
          class="tab-pane fade active"
          id="groups-tab"
          role="tabpanel"
          aria-labelledby="hosts-tab"
        >
          <div v-if="view.isLoaded && groupsRows.length == 0" class="blank-slate-pf">
            <div class="blank-slate-pf-icon">
              <span class="fa fa-users"></span>
            </div>
            <h1>{{$t('mailboxes.groups_not_found')}}</h1>
            <p>{{$t('mailboxes.groups_not_found_desc')}}.</p>
            <div class="blank-slate-pf-main-action">
              <button
                @click="enableAutoGroups()"
                class="btn btn-primary btn-lg"
              >{{$t('mailboxes.create_groups')}}</button>
            </div>
          </div>

          <h3 v-if="groupsRows.length > 0">{{$t('list')}}</h3>
          <div v-if="!view.isLoaded" class="spinner spinner-lg"></div>
          <vue-good-table
            v-show="view.isLoaded && groupsRows.length > 0"
            :customRowsPerPageDropdown="[25,50,100]"
            :perPage="25"
            :columns="groupsColumns"
            :rows="groupsRows"
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
                <span class="fa fa-users span-right-icon"></span>
                <a
                  @click="props.row.props.MailStatus == 'enabled' ? openEditGroup(props.row) : undefined"
                  :class="[props.row.props.MailStatus == 'enabled' ? '' : 'gray']"
                >
                  <strong>{{ props.row.displayname || props.row.name}}</strong>
                </a>
                <a
                  tabindex="0"
                  role="button"
                  :data-toggle="props.row.props.MailStatus == 'enabled' ? 'popover': ''"
                  data-html="true"
                  data-placement="top"
                  :title="$t('mailboxes.aliases')"
                  :id="'popover-'+props.row.name | sanitize"
                  @click="aliasDetails(props.row)"
                  class="span-left-margin"
                >{{$t('mailboxes.aliases')}}</a>
              </td>
              <td>
                <button
                  v-if="props.row.props.MailStatus == 'enabled'"
                  @click="toggleGroupStatus(props.row)"
                  class="btn btn-default"
                >
                  <span class="fa fa-lock span-right-margin"></span>
                  {{$t('disable')}}
                </button>
                <button
                  v-if="props.row.props.MailStatus == 'disabled'"
                  @click="toggleGroupStatus(props.row)"
                  class="btn btn-primary"
                >
                  <span class="fa fa-check span-right-margin"></span>
                  {{$t('enable')}}
                </button>
              </td>
            </template>
          </vue-good-table>
        </div>

        <div
          class="tab-pane fade active"
          id="public-tab"
          role="tabpanel"
          aria-labelledby="hosts-tab"
        >
          <h3>{{$t('actions')}}</h3>
          <button
            @click="openCreatePublic()"
            class="btn btn-primary btn-lg"
          >{{$t('mailboxes.add_public')}}</button>

          <h3>{{$t('list')}}</h3>
          <div v-if="!view.isLoaded" class="spinner spinner-lg"></div>
          <vue-good-table
            v-show="view.isLoaded"
            :customRowsPerPageDropdown="[25,50,100]"
            :perPage="25"
            :columns="publicColumns"
            :rows="publicRows"
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
              <td class="fancy">
                <span class="fa fa-folder-open span-right-icon"></span>
                <a @click="openEditPublic(props.row)">
                  <strong>{{ props.row.name}}</strong>
                </a>
                <a
                  tabindex="0"
                  role="button"
                  data-toggle="popover"
                  data-html="true"
                  data-placement="top"
                  :title="$t('mailboxes.aliases')"
                  :id="'popover-'+props.row.name | sanitize"
                  @click="aliasDetails(props.row)"
                  class="span-left-margin"
                >{{$t('mailboxes.aliases')}}</a>
              </td>
              <td class="fancy">
                <span class="fa fa-lock"></span>
                <span
                  data-toggle="tooltip"
                  data-placement="top"
                  data-html="true"
                  :title="'<b>'+$t('mailboxes.'+a.right) + '</b>:<br>'+a.rawrights.replace(/ /g,'<br>')"
                  class="span-left-margin"
                  v-for="(a, ak) in props.row.acls"
                  v-bind:key="ak"
                >
                  {{a.displayname || a.name || '-'}}
                  <span v-show="ak+1 != props.row.acls.length">,</span>
                </span>
              </td>
              <td>
                <button @click="openEditPublic(props.row)" class="btn btn-default">
                  <span class="fa fa-pencil span-right-margin"></span>
                  {{$t('edit')}}
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
                  <ul
                    class="dropdown-menu dropdown-menu-right"
                    aria-labelledby="dropdownKebabRight9"
                  >
                    <li @click="openDeletePublic(props.row)">
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
      </div>
    </div>
    <div class="modal" id="configurationModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{$t('configure')}}</h4>
          </div>
          <form class="form-horizontal" v-on:submit.prevent="editConfiguration()">
            <div class="modal-body">
              <legend class="fields-section-header-pf" aria-expanded="true">
                <span class="field-section-toggle-pf">{{$t('mailboxes.access_protocols')}}</span>
              </legend>

              <div class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.PopStatus')}}</label>
                <div class="col-sm-9">
                  <input type="checkbox" v-model="configuration.PopStatus" class="form-control">
                </div>
              </div>
              <div class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.ImapStatus')}}</label>
                <div class="col-sm-9">
                  <input type="checkbox" v-model="configuration.ImapStatus" class="form-control">
                </div>
              </div>
              <div class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.TlsSecurity')}}</label>
                <div class="col-sm-9">
                  <input type="checkbox" v-model="configuration.TlsSecurity" class="form-control">
                </div>
              </div>

              <legend class="fields-section-header-pf" aria-expanded="true">
                <span class="field-section-toggle-pf">{{$t('mailboxes.disk_space')}}</span>
              </legend>

              <div class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.QuotaStatus')}}</label>
                <div class="col-sm-9">
                  <input
                    id="quota-disabled"
                    class="col-sm-2 col-xs-2"
                    type="radio"
                    v-model="configuration.QuotaStatus"
                    :value="false"
                  >
                  <label
                    class="col-sm-10 col-xs-10 control-label text-align-left"
                    for="quota-disabled"
                  >{{$t('mailboxes.unlimited')}}</label>
                  <input
                    id="quota-enabled"
                    class="col-sm-2 col-xs-2"
                    type="radio"
                    v-model="configuration.QuotaStatus"
                    :value="true"
                  >
                  <label
                    class="col-sm-10 col-xs-10 control-label text-align-left"
                    for="quota-enabled"
                  >{{$t('mailboxes.apply_quota')}}</label>
                </div>
              </div>
              <div v-if="configuration.QuotaStatus" class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.QuotaDefaultSize')}}</label>
                <div class="col-sm-9">
                  <span>{{ configuration.QuotaDefaultSize }} GB</span>
                  <vue-slider
                    v-model="configuration.QuotaDefaultSize"
                    :min="1"
                    :max="100"
                    :use-keyboard="true"
                    :tooltip="'always'"
                  ></vue-slider>
                </div>
              </div>

              <legend class="fields-section-header-pf" aria-expanded="true">
                <span
                  :class="['fa fa-angle-right field-section-toggle-pf', configuration.advanced ? 'fa-angle-down' : '']"
                ></span>
                <a
                  class="field-section-toggle-pf"
                  @click="toggleAdvancedMode()"
                >{{$t('advanced_mode')}}</a>
              </legend>

              <div v-show="configuration.advanced">
                <div class="form-group">
                  <label
                    class="col-sm-3 control-label"
                    for="textInput-modal-markup"
                  >{{$t('mailboxes.SpamFolder')}}</label>
                  <div class="col-sm-9">
                    <input type="checkbox" v-model="configuration.SpamFolder" class="form-control">
                  </div>
                </div>
                <div class="form-group">
                  <label
                    class="col-sm-3 control-label"
                    for="textInput-modal-markup"
                  >{{$t('mailboxes.SpamRetentionTime')}}</label>
                  <div class="col-sm-9">
                    <span>
                      {{ configuration.SpamRetentionTime }}
                      <span
                        v-if="configuration.SpamRetentionTime != $t('ever')"
                      >{{$t('days')}}</span>
                    </span>
                    <vue-slider
                      v-model="configuration.SpamRetentionTime"
                      :data="[1,2,4,7,15,30,60,90,180,$t('ever')]"
                      :use-keyboard="true"
                      :tooltip="'always'"
                    ></vue-slider>
                  </div>
                </div>

                <div class="form-group">
                  <label
                    class="col-sm-3 control-label"
                    for="textInput-modal-markup"
                  >{{$t('mailboxes.AdminIsMaster')}}</label>
                  <div class="col-sm-9">
                    <input
                      type="checkbox"
                      v-model="configuration.AdminIsMaster"
                      class="form-control"
                    >
                  </div>
                </div>

                <div class="form-group">
                  <label
                    class="col-sm-3 control-label"
                    for="textInput-modal-markup"
                  >{{$t('mailboxes.DynamicGroupAlias')}}</label>
                  <div class="col-sm-9">
                    <input
                      type="checkbox"
                      v-model="configuration.DynamicGroupAlias"
                      class="form-control"
                    >
                  </div>
                </div>
                <div class="form-group">
                  <label
                    class="col-sm-3 control-label"
                    for="textInput-modal-markup"
                  >{{$t('mailboxes.LogActions')}}</label>
                  <div class="col-sm-9">
                    <input type="checkbox" v-model="configuration.LogActions" class="form-control">
                  </div>
                </div>
                <div class="form-group">
                  <label
                    class="col-sm-3 control-label"
                    for="textInput-modal-markup"
                  >{{$t('mailboxes.DeletedToTrash')}}</label>
                  <div class="col-sm-9">
                    <input
                      type="checkbox"
                      v-model="configuration.DeletedToTrash"
                      class="form-control"
                    >
                  </div>
                </div>
                <div class="form-group">
                  <label
                    class="col-sm-3 control-label"
                    for="textInput-modal-markup"
                  >{{$t('mailboxes.MaxUserConnectionsPerIp')}}</label>
                  <div class="col-sm-9">
                    <input
                      type="number"
                      v-model="configuration.MaxUserConnectionsPerIp"
                      class="form-control"
                    >
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <div v-if="configuration.isLoading" class="spinner spinner-sm form-spinner-loader"></div>
              <button class="btn btn-default" type="button" data-dismiss="modal">{{$t('cancel')}}</button>
              <button class="btn btn-primary" type="submit">{{$t('save')}}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal" id="editUserModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{$t('mailboxes.edit_user')}} {{currentUser.name}}</h4>
          </div>
          <form class="form-horizontal" v-on:submit.prevent="editUser(currentUser)">
            <div class="modal-body">
              <div class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.access_email')}}</label>
                <div class="col-sm-9">
                  <input
                    type="checkbox"
                    v-model="currentUser.props.MailStatus"
                    class="form-control"
                  >
                </div>
              </div>

              <div class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.forward_messages')}}</label>
                <div class="col-sm-9">
                  <toggle-button
                    class="min-toggle"
                    :width="40"
                    :height="20"
                    :color="{checked: '#0088ce', unchecked: '#bbbbbb'}"
                    :value="currentUser.props.MailForwardStatus"
                    :sync="true"
                    @change="currentUser.props.MailForwardStatus =! currentUser.props.MailForwardStatus"
                  />
                </div>
              </div>

              <div v-if="currentUser.props.MailForwardStatus" class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.forward_addresses')}}</label>
                <div class="col-sm-9">
                  <textarea
                    class="form-control min-textarea-height"
                    v-model="currentUser.props.MailForwardAddress"
                  ></textarea>
                </div>
              </div>
              <div v-if="currentUser.props.MailForwardStatus" class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.keep_messages_copy')}}</label>
                <div class="col-sm-9">
                  <input
                    type="checkbox"
                    v-model="currentUser.props.MailForwardKeepMessageCopy"
                    class="form-control"
                  >
                </div>
              </div>

              <div class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.custom_mail_quota')}}</label>
                <div class="col-sm-9">
                  <toggle-button
                    class="min-toggle"
                    :width="40"
                    :height="20"
                    :color="{checked: '#0088ce', unchecked: '#bbbbbb'}"
                    :value="currentUser.props.MailQuotaType"
                    :sync="true"
                    @change="currentUser.props.MailQuotaType =! currentUser.props.MailQuotaType"
                  />
                </div>
              </div>
              <div v-show="currentUser.props.MailQuotaType" class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.mail_quota')}}</label>
                <div class="col-sm-9">
                  <span>{{ currentUser.props.MailQuotaCustom }} GB</span>
                  <vue-slider
                    v-model="currentUser.props.MailQuotaCustom"
                    :min="1"
                    :max="100"
                    :use-keyboard="true"
                    :tooltip="'always'"
                  ></vue-slider>
                </div>
              </div>

              <div class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.custom_spam_retention')}}</label>
                <div class="col-sm-9">
                  <toggle-button
                    class="min-toggle"
                    :width="40"
                    :height="20"
                    :color="{checked: '#0088ce', unchecked: '#bbbbbb'}"
                    :value="currentUser.props.MailSpamRetentionStatus"
                    :sync="true"
                    @change="currentUser.props.MailSpamRetentionStatus =! currentUser.props.MailSpamRetentionStatus"
                  />
                </div>
              </div>
              <div v-show="currentUser.props.MailSpamRetentionStatus" class="form-group">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.spam_retention')}}</label>
                <div class="col-sm-9">
                  <span>{{ currentUser.props.MailSpamRetentionTime }} {{$t('days')}}</span>
                  <vue-slider
                    v-model="currentUser.props.MailSpamRetentionTime"
                    :data="[1,2,4,7,15,30,60,90,180,$t('ever')]"
                    :use-keyboard="true"
                    :tooltip="'always'"
                  ></vue-slider>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <div v-if="currentUser.isLoading" class="spinner spinner-sm form-spinner-loader"></div>
              <button class="btn btn-default" type="button" data-dismiss="modal">{{$t('cancel')}}</button>
              <button class="btn btn-primary" type="submit">{{$t('edit')}}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal" id="createPublicModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4
              class="modal-title"
            >{{currentPublic.isEdit ? $t('mailboxes.edit_public') + ' ' +currentPublic.name : $t('mailboxes.create_public')}}</h4>
          </div>
          <form class="form-horizontal" v-on:submit.prevent="createPublic(currentPublic)">
            <div class="modal-body">
              <div class="alert alert-info alert-dismissable">
                <span class="pficon pficon-info"></span>
                <strong>{{$t('info')}}</strong>
                {{$t('mailboxes.automatic_pseudonym_creation')}}.
              </div>
              <div :class="['form-group', currentPublic.errors.name.hasError ? 'has-error' : '']">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.name')}}</label>
                <div class="col-sm-9">
                  <input type="text" v-model="currentPublic.name" class="form-control">
                  <span
                    v-if="currentPublic.errors.name.hasError"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+currentPublic.errors.name.message)}}</span>
                </div>
              </div>

              <div :class="['form-group', currentPublic.errors.acls.hasError ? 'has-error' : '']">
                <label class="col-sm-3 control-label" for="textInput-modal-markup">
                  {{$t('mailboxes.acls')}}
                  <doc-info
                    :placement="'top'"
                    :title="$t('mailboxes.acls')"
                    :chapter="'acls'"
                    :inline="true"
                  ></doc-info>
                </label>
                <div class="col-sm-9">
                  <suggestions
                    v-model="currentPublic.aclToAdd"
                    :options="autoOptions"
                    :onInputChange="filterSrcAuto"
                    :onItemSelected="selectSrcAuto"
                    :required="!currentPublic.isEdit"
                  >
                    <div slot="item" slot-scope="props" class="single-item">
                      <span>
                        <span :class="['span-right-margin fa', getIcon(props.item)]"></span>
                        {{props.item.displayname || props.item.name}}
                        <i
                          class="mg-left-5"
                        >{{props.item.type}}</i>
                      </span>
                    </div>
                  </suggestions>
                  <span
                    v-if="currentPublic.errors.acls.hasError"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+currentPublic.errors.acls.message)}}</span>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label" for="textInput-modal-markup"></label>
                <div class="col-sm-9">
                  <ul class="list-inline compact">
                    <li v-for="(i, ki) in currentPublic.acls" v-bind:key="ki" class="mg-bottom-5">
                      <span class="label label-info label-select label-acl">
                        {{i.displayname || i.name}}
                        <div class="inline-select">
                          <select
                            :disabled="i.right == 'custom'"
                            class="form-control"
                            v-model="i.right"
                          >
                            <option value="read">{{$t('mailboxes.read')}}</option>
                            <option value="read-write">{{$t('mailboxes.read-write')}}</option>
                            <option value="full">{{$t('mailboxes.full')}}</option>
                            <option
                              v-if="i.right == 'custom'"
                              value="custom"
                            >{{$t('mailboxes.custom')}}</option>
                          </select>
                        </div>
                        <a @click="removeACLToPublic(ki)" class="remove-item-inline remove-acl">
                          <span class="fa fa-times"></span>
                        </a>
                      </span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <div v-if="currentPublic.isLoading" class="spinner spinner-sm form-spinner-loader"></div>
              <button class="btn btn-default" type="button" data-dismiss="modal">{{$t('cancel')}}</button>
              <button
                class="btn btn-primary"
                type="submit"
              >{{currentPublic.isEdit ? $t('edit') : $t('save')}}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal" id="deletePublicModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{$t('mailboxes.delete_public')}} {{toDelete.name}}</h4>
          </div>
          <form class="form-horizontal" v-on:submit.prevent="deletePublic(toDelete)">
            <div class="modal-body">
              <div class="form-group">
                <label
                  class="col-sm-10 control-label"
                  for="textInput-modal-markup"
                >{{$t('mailboxes.delete_public_mailbox')}}?</label>
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
import VueSlider from "vue-slider-component";
import "vue-slider-component/theme/default.css";

export default {
  name: "Mailboxes",
  components: {
    VueSlider
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
    $("#users-tab-parent").click();
    this.getAll();
    this.getDestinations();
    this.getConfiguration();
    this.initListeners(0);
  },
  data() {
    return {
      view: {
        isLoaded: false,
        isInstalling: false,
        opened: false,
        menu: {
          installed: false,
          packages: []
        }
      },
      tableLangsTexts: this.tableLangs(),
      usersColumns: [
        {
          label: this.$i18n.t("mailboxes.name"),
          field: "name",
          filterable: true
        },
        {
          label: this.$i18n.t("mailboxes.mail_quota_usage"),
          field: "quota.size",
          type: "number",
          filterable: true
        },
        {
          label: this.$i18n.t("mailboxes.forward_adress"),
          field: "Description",
          filterable: true,
          sortable: false
        },
        {
          label: this.$i18n.t("mailboxes.connectors"),
          field: "Connectors",
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
      groupsColumns: [
        {
          label: this.$i18n.t("mailboxes.name"),
          field: "name",
          filterable: true
        },
        {
          label: this.$i18n.t("action"),
          field: "",
          filterable: true,
          sortable: false
        }
      ],
      groupsRows: [],
      publicColumns: [
        {
          label: this.$i18n.t("mailboxes.name"),
          field: "name",
          filterable: true
        },
        {
          label: this.$i18n.t("mailboxes.acls"),
          field: "acls",
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
      publicRows: [],
      configuration: {
        isLoading: false,
        advanced: false
      },
      currentUser: {
        isLoading: false,
        props: {
          MailForwardAddress: [],
          MailSpamRetentionStatus: false,
          MailQuotaCustom: 0,
          MailForwardKeepMessageCopy: false,
          MailQuotaType: false,
          MailForwardStatus: false,
          MailSpamRetentionTime: 60,
          MailStatus: true
        },
        name: ""
      },
      currentGroup: {
        isLoading: false
      },
      currentPublic: this.initPublic(),
      toDelete: {
        name: ""
      },
      autoOptions: {
        inputClass: "form-control"
      },
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
          window.location.reload();
        },
        function(error) {
          console.error(error);
        }
      );
    },
    initPublic() {
      return {
        isLoading: false,
        name: "",
        acls: [],
        aclToAdd: "",
        errors: this.initPublicErrors()
      };
    },
    initPublicErrors() {
      return {
        name: {
          hasError: false,
          message: ""
        },
        acls: {
          hasError: false,
          message: ""
        }
      };
    },
    getIcon(account) {
      switch (account.type) {
        case "user":
          return "fa-user";

        case "group":
          return "fa-users";

        case "public":
          return "fa-folder-open";

        case "external":
          return "fa-external-link";

        case "pseudonym":
          return "fa-user-secret";
      }
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
          context.destinations = success.users.concat(success.groups);
        },
        function(error) {
          console.error(error);
        }
      );
    },
    getConnectorsDetails(props) {
      var text = "<h5><b>Retriever</b>: " + props.Retriever + "</h5>";
      text += "<li>Server: <code>" + props.Server + "</code></li>";
      text += "<li>Username: <code>" + props.Username + "</code></li>";
      text += "<h5><b>Type</b>: " + props.type + "</h5>";
      text +=
        "<h5><b>" +
        this.$i18n.t("status") +
        "</b>: " +
        (props.status == "enabled"
          ? '<span class="fa fa-check green"></span>'
          : '<span class="fa fa-times red"></span>') +
        "</h5>";

      return text;
    },
    initListeners(index) {
      var context = this;

      setTimeout(function() {
        context.enablePopover();
        $(
          $(".pagination-controls.pull-right>a.page-btn:first-child")[index]
        ).on("click", function() {
          context.enablePopover();
        });
        $($(".pagination-controls.pull-right>a.page-btn:last-child")[index]).on(
          "click",
          function() {
            context.enablePopover();
          }
        );
      }, 500);
    },
    filterSrcAuto(query) {
      if (query.trim().length === 0) {
        return null;
      }

      return this.destinations.filter(function(destination) {
        return (
          (destination.name &&
            destination.name.toLowerCase().includes(query.toLowerCase())) ||
          (destination.displayname &&
            destination.displayname
              .toLowerCase()
              .includes(query.toLowerCase())) ||
          (destination.type &&
            destination.type.toLowerCase().includes(query.toLowerCase()))
        );
      });
    },
    selectSrcAuto(item) {
      this.addACLToPublic(item);
      this.currentPublic.aclToAdd = item.displayname || item.name;
    },
    aclAlreadyAdded(bind) {
      var found = false;
      for (var i in this.currentPublic.acls) {
        var account = this.currentPublic.acls[i];
        if (account.name == bind.name) {
          found = true;
        }
      }
      return found;
    },
    addACLToPublic(dest) {
      if (dest.name.length > 0 && dest.name != "-") {
        if (!this.aclAlreadyAdded(dest)) {
          this.currentPublic.acls.push({
            name: dest.name,
            displayname: dest.displayname || dest.name,
            type: dest.type,
            right: "read"
          });
        }
      }
    },
    removeACLToPublic(index) {
      this.currentPublic.acls.splice(index, 1);
    },
    connDetails(conn) {
      var popover = $(
        "#" + this.$options.filters.sanitize("popover-connectors-" + conn.name)
      )
        .popover()
        .data("bs.popover");

      popover.options.content = this.getConnectorsDetails(conn.props);
      popover.show();
    },
    aliasDetails(mailbox) {
      var popover = $(
        "#" + this.$options.filters.sanitize("popover-" + mailbox.name)
      ).data("bs.popover");

      if (!mailbox.aliases.isLoaded && popover) {
        popover.options.content = '<div class="spinner spinner-sm"></div>';
        popover.show();

        var context = this;
        nethserver.exec(
          ["nethserver-mail/mailbox/read"],
          {
            action: "aliases",
            name: mailbox.name,
            type: mailbox.type
          },
          null,
          function(success) {
            try {
              success = JSON.parse(success);
            } catch (e) {
              console.error(e);
            }

            var aliases = success.aliases;

            if (aliases.length > 0) {
              popover.options.content = "";
            } else {
              popover.options.content = context.$i18n.t(
                "mailboxes.no_aliases_found"
              );
            }
            for (var a in aliases) {
              var alias = aliases[a];
              popover.options.content += "<li>" + alias + "</li>";
            }

            mailbox.aliases.isLoaded = true;
            popover.show();
          },
          function(error) {
            console.error(error);
          }
        );
      }
    },
    getAll() {
      var context = this;

      context.view.isLoaded = false;
      nethserver.exec(
        ["nethserver-mail/mailbox/read"],
        {
          action: "list",
          expand: true
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          context.usersRows = success["users"];
          context.groupsRows = success["groups"];
          context.publicRows = success["public"];

          for (var u in context.usersRows) {
            var user = context.usersRows[u];
            user.aliases = {
              isLoaded: false
            };
          }
          for (var g in context.groupsRows) {
            var group = context.groupsRows[g];
            group.aliases = {
              isLoaded: false
            };
          }
          for (var g in context.publicRows) {
            var pub = context.publicRows[g];
            pub.aliases = {
              isLoaded: false
            };
          }

          $('[data-toggle="tooltip"]').tooltip("destroy");
          setTimeout(function() {
            $('[data-toggle="tooltip"]').tooltip();
          }, 500);

          setTimeout(function() {
            context.enablePopover();
          }, 250);

          context.view.isLoaded = true;
        },
        function(error) {
          console.error(error);
        }
      );
    },
    enablePopover() {
      $("[data-toggle=popover]")
        .popovers()
        .on("hidden.bs.popover", function(e) {
          $(e.target).data("bs.popover").inState.click = false;
        });
    },
    getConfiguration() {
      var context = this;

      nethserver.exec(
        ["nethserver-mail/mailbox/read"],
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
          var config = success["configuration"];
          for (var c in config) {
            if (config[c] == "disabled") {
              config[c] = false;
            }

            if (config[c] == "enabled") {
              config[c] = true;
            }
          }

          context.configuration = config;
          context.configuration.isLoading = false;
          context.configuration.advanced = false;
        },
        function(error) {
          console.error(error);
        }
      );
    },
    toggleAdvancedMode() {
      this.configuration.advanced = !this.configuration.advanced;
      this.$forceUpdate();
    },
    openConfigure() {
      $("#configurationModal").modal("show");
      this.configuration.TlsSecurity = !this.configuration.TlsSecurity;
    },
    enableAutoGroups() {
      var context = this;

      // notification
      nethserver.notifications.success = context.$i18n.t(
        "mailboxes.groups_created_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "mailboxes.groups_created_error"
      );
      nethserver.exec(
        ["nethserver-mail/mailbox/update"],
        {
          PopStatus: context.configuration.PopStatus,
          ImapStatus: context.configuration.ImapStatus,
          TlsSecurity: context.configuration.TlsSecurity,
          QuotaStatus: context.configuration.QuotaStatus,
          QuotaDefaultSize: context.configuration.QuotaDefaultSize,

          AdminIsMaster: context.configuration.AdminIsMaster,
          SpamFolder: context.configuration.SpamFolder,
          SpamRetentionTime: context.configuration.SpamRetentionTime,
          DynamicGroupAlias: "enabled",
          MaxUserConnectionsPerIp:
            context.configuration.MaxUserConnectionsPerIp,
          LogActions: context.configuration.LogActions,
          DeletedToTrash: context.configuration.DeletedToTrash,
          action: "configuration"
        },
        function(stream) {
          console.info("configuration", stream);
        },
        function(success) {
          // get configuration
          context.getAll();
          context.getConfiguration();
        },
        function(error, data) {
          console.error(error, data);
        }
      );
    },
    editConfiguration() {
      var context = this;

      var configObj = {
        PopStatus: context.configuration.PopStatus ? "enabled" : "disabled",
        ImapStatus: context.configuration.ImapStatus ? "enabled" : "disabled",
        TlsSecurity: context.configuration.TlsSecurity ? "disabled" : "enabled",
        QuotaStatus: context.configuration.QuotaStatus ? "enabled" : "disabled",
        QuotaDefaultSize: context.configuration.QuotaDefaultSize,

        AdminIsMaster: context.configuration.AdminIsMaster
          ? "enabled"
          : "disabled",
        SpamFolder: context.configuration.SpamFolder ? "enabled" : "disabled",
        SpamRetentionTime:
          context.configuration.SpamRetentionTime == this.$i18n.t("ever")
            ? -1
            : context.configuration.SpamRetentionTime,
        DynamicGroupAlias: context.configuration.DynamicGroupAlias
          ? "enabled"
          : "disabled",
        MaxUserConnectionsPerIp: context.configuration.MaxUserConnectionsPerIp,
        LogActions: context.configuration.LogActions ? "enabled" : "disabled",
        DeletedToTrash: context.configuration.DeletedToTrash
          ? "enabled"
          : "disabled",
        action: "configuration"
      };

      context.configuration.isLoading = true;
      context.$forceUpdate();
      nethserver.exec(
        ["nethserver-mail/mailbox/validate"],
        configObj,
        null,
        function(success) {
          context.configuration.isLoading = false;
          $("#configurationModal").modal("hide");

          // notification
          nethserver.notifications.success = context.$i18n.t(
            "mailboxes.configuration_updated_ok"
          );
          nethserver.notifications.error = context.$i18n.t(
            "mailboxes.configuration_updated_error"
          );

          // update values
          nethserver.exec(
            ["nethserver-mail/mailbox/update"],
            configObj,
            function(stream) {
              console.info("configuration", stream);
            },
            function(success) {
              // get configuration
              context.getConfiguration();
              context.getAll();
            },
            function(error, data) {
              console.error(error, data);
            }
          );
        },
        function(error, data) {
          var errorData = {};
          context.configuration.isLoading = false;

          try {
            errorData = JSON.parse(data);
            for (var e in errorData.attributes) {
              var attr = errorData.attributes[e];
              context.configuration.errors[attr.parameter].hasError = true;
              context.configuration.errors[attr.parameter].message = attr.error;
            }
          } catch (e) {
            console.error(e);
          }
        }
      );
    },
    openEditUser(user) {
      this.currentUser = JSON.parse(JSON.stringify(user));
      this.currentUser.props.MailForwardAddress =
        this.currentUser.props.MailForwardAddress.length > 0
          ? this.currentUser.props.MailForwardAddress.join("\n")
          : "";
      this.currentUser.props.MailForwardStatus =
        this.currentUser.props.MailForwardStatus == "enabled";
      this.currentUser.props.MailSpamRetentionStatus =
        this.currentUser.props.MailSpamRetentionStatus == "enabled";
      this.currentUser.props.MailQuotaType =
        this.currentUser.props.MailQuotaType != "default";
      this.currentUser.props.MailForwardKeepMessageCopy =
        this.currentUser.props.MailForwardKeepMessageCopy != "no";
      this.currentUser.props.MailStatus = this.currentUser.props.MailStatus =
        "public";
      this.currentUser.isLoading = false;
      $("#editUserModal").modal("show");
    },
    openEditGroup() {},
    openCreatePublic() {
      this.currentPublic = this.initPublic();
      $("#createPublicModal").modal("show");
    },
    openEditPublic(publicAddr) {
      this.currentPublic = JSON.parse(JSON.stringify(publicAddr));
      this.currentPublic.errors = this.initPublicErrors();
      this.currentPublic.isEdit = true;
      this.currentPublic.isLoading = false;
      this.currentPublic.aclToAdd = this.currentPublic.acls
        .map(function(a) {
          return a.displayname || a.name;
        })
        .join(",");
      $("#createPublicModal").modal("show");
    },
    editUser() {
      var context = this;

      var userObj = {
        MailForwardAddress:
          context.currentUser.props.MailForwardAddress.length > 0
            ? context.currentUser.props.MailForwardAddress.split("\n")
            : [],
        MailSpamRetentionStatus: context.currentUser.props
          .MailSpamRetentionStatus
          ? "enabled"
          : "disabled",
        MailQuotaCustom: context.currentUser.props.MailQuotaCustom,
        MailForwardKeepMessageCopy: context.currentUser.props
          .MailForwardKeepMessageCopy
          ? "yes"
          : "no",
        MailQuotaType: context.currentUser.props.MailQuotaType
          ? "custom"
          : "default",
        MailForwardStatus: context.currentUser.props.MailForwardStatus
          ? "enabled"
          : "disabled",
        MailSpamRetentionTime: context.currentUser.props.MailSpamRetentionTime,
        MailStatus: context.currentUser.props.MailStatus
          ? "enabled"
          : "disabled",
        name: context.currentUser.name,
        action: "update-user"
      };

      context.currentUser.isLoading = true;
      context.$forceUpdate();
      nethserver.exec(
        ["nethserver-mail/mailbox/validate"],
        userObj,
        null,
        function(success) {
          context.currentUser.isLoading = false;
          $("#editUserModal").modal("hide");

          // notification
          nethserver.notifications.success = context.$i18n.t(
            "mailboxes.user_updated_ok"
          );
          nethserver.notifications.error = context.$i18n.t(
            "mailboxes.user_updated_error"
          );

          // update values
          nethserver.exec(
            ["nethserver-mail/mailbox/update"],
            userObj,
            function(stream) {
              console.info("update-user", stream);
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

          try {
            errorData = JSON.parse(data);
            for (var e in errorData.attributes) {
              var attr = errorData.attributes[e];
              context.currentUser.errors[attr.parameter].hasError = true;
              context.currentUser.errors[attr.parameter].message = attr.error;
            }
          } catch (e) {
            console.error(e);
          }
        }
      );
    },
    toggleUserStatus(user) {
      var context = this;
      // notification
      nethserver.notifications.success = context.$i18n.t(
        "mailboxes.user_updated_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "mailboxes.user_updated_error"
      );

      // update values
      nethserver.exec(
        ["nethserver-mail/mailbox/update"],
        {
          MailStatus:
            user.props.MailStatus == "enabled" ? "disabled" : "enabled",
          MailForwardAddress: user.props.MailForwardAddress,
          MailSpamRetentionStatus: user.props.MailSpamRetentionStatus,
          MailQuotaCustom: user.props.MailQuotaCustom,
          MailForwardKeepMessageCopy: user.props.MailForwardKeepMessageCopy,
          MailQuotaType: user.props.MailQuotaType,
          MailForwardStatus: user.props.MailForwardStatus,
          MailSpamRetentionTime: user.props.MailSpamRetentionTime,
          name: user.name,
          action: "update-user"
        },
        function(stream) {
          console.info("update-user", stream);
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
    toggleGroupStatus(group) {
      var context = this;
      // notification
      nethserver.notifications.success = context.$i18n.t(
        "mailboxes.group_updated_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "mailboxes.group_updated_error"
      );

      // update values
      nethserver.exec(
        ["nethserver-mail/mailbox/update"],
        {
          MailStatus:
            group.props.MailStatus == "enabled" ? "disabled" : "enabled",
          name: group.name,
          action: "update-group"
        },
        function(stream) {
          console.info("update-group", stream);
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
    createPublic(publicAddr) {
      var context = this;

      var publicObj = {
        action: publicAddr.isEdit ? "update-public" : "create-public",
        acls: publicAddr.acls,
        name: publicAddr.name
      };

      context.currentPublic.isLoading = true;
      context.$forceUpdate();
      nethserver.exec(
        ["nethserver-mail/mailbox/validate"],
        publicObj,
        null,
        function(success) {
          context.currentPublic.isLoading = false;
          $("#createPublicModal").modal("hide");

          // notification
          nethserver.notifications.success = context.$i18n.t(
            "mailbox.public_" +
              (context.currentPublic.isEdit ? "updated" : "created") +
              "_ok"
          );
          nethserver.notifications.error = context.$i18n.t(
            "mailbox.public_" +
              (context.currentPublic.isEdit ? "updated" : "created") +
              "_error"
          );

          // update values
          if (publicAddr.isEdit) {
            nethserver.exec(
              ["nethserver-mail/mailbox/update"],
              publicObj,
              function(stream) {
                console.info("public", stream);
              },
              function(success) {
                // get all
                context.getAll();
              },
              function(error, data) {
                console.error(error, data);
              }
            );
          } else {
            nethserver.exec(
              ["nethserver-mail/mailbox/create"],
              publicObj,
              function(stream) {
                console.info("public", stream);
              },
              function(success) {
                // get all
                context.getAll();
              },
              function(error, data) {
                console.error(error, data);
              }
            );
          }
        },
        function(error, data) {
          var errorData = {};
          context.currentPublic.isLoading = false;
          context.currentPublic.errors = context.initPublicErrors();

          try {
            errorData = JSON.parse(data);
            for (var e in errorData.attributes) {
              var attr = errorData.attributes[e];
              context.currentPublic.errors[attr.parameter].hasError = true;
              context.currentPublic.errors[attr.parameter].message = attr.error;
            }
          } catch (e) {
            console.error(e);
          }
        }
      );
    },
    openDeletePublic(publicAddr) {
      this.toDelete = Object.assign({}, publicAddr);
      $("#deletePublicModal").modal("show");
    },
    deletePublic(publicAddr) {
      var context = this;

      // notification
      nethserver.notifications.success = context.$i18n.t(
        "mailboxes.public_deleted_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "mailboxes.public_deleted_error"
      );

      $("#deletePublicModal").modal("hide");
      nethserver.exec(
        ["nethserver-mail/mailbox/delete"],
        {
          name: publicAddr.name
        },
        function(stream) {
          console.info("public", stream);
        },
        function(success) {
          // get all
          context.getAll();
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
.adjust-progress-description {
  height: 28px !important;
  margin-bottom: 0px !important;
}
.adjust-progress-span {
  top: -20px !important;
  line-height: 15px !important;
}
.quota-min-width {
  min-width: 280px;
}

.span-right-icon {
  font-size: 15px;
  margin-right: 8px;
}

.back-gray {
  background-color: #72767b !important;
}

.dt-config {
  width: 230px !important;
}

.dd-config {
  margin-left: 240px !important;
}

.inline-select {
  display: inline-block !important;
  margin-right: 6px;
  margin: 5px;
}

.label-select {
  padding: 8px;
}

.label-acl {
  color: #363636;
  background: #f5f5f5;
}

.remove-acl {
  color: #363636;
}
</style>
