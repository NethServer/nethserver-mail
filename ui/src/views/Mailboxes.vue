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
      :chapter="'mailboxes'"
      :section="''"
      :inline="false"
    ></doc-info>

    <h3>{{ $t('mailboxes.configuration') }}</h3>
    <div class="panel panel-default">
      <div class="panel-heading">
        <button @click="openConfigure()" class="btn btn-default right">{{$t('configure')}}</button>
        <span class="panel-title">{{$t('mailboxes.configuration')}}</span>
        <span
          class="provider-details margin-left-md"
          data-toggle="collapse"
          data-parent="#provider-markup"
          href="#providerDetails"
          @click="toggleDetails()"
        >
          <span :class="['fa', view.opened ? 'fa-angle-down' : 'fa-angle-right']"></span>
          {{$t('mailboxes.details')}}
        </span>
      </div>
      <div id="providerDetails" class="panel-collapse collapse list-group list-view-pf">
        <dl class="dl-horizontal details-container">
          <span v-for="(v,k) in configuration" v-bind:key="k">
            <dt>{{k | capitalize}}</dt>
            <dd>{{v | capitalize}}</dd>
          </span>
        </dl>
      </div>
    </div>

    <ul class="nav nav-tabs nav-tabs-pf">
      <li>
        <a
          class="nav-link"
          data-toggle="tab"
          href="#users-tab"
          id="users-tab-parent"
        >{{$t('mailboxes.users')}}</a>
      </li>
      <li>
        <a
          class="nav-link"
          data-toggle="tab"
          href="#groups-tab"
          id="groups-tab-parent"
        >{{$t('mailboxes.groups')}}</a>
      </li>
      <li>
        <a
          class="nav-link"
          data-toggle="tab"
          href="#public-tab"
          id="public-tab-parent"
        >{{$t('mailboxes.public')}}</a>
      </li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane fade active" id="users-tab" role="tabpanel" aria-labelledby="hosts-tab">
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
            <td class="fancy">
              <span class="fa fa-user span-right-icon"></span>
              <a @click="openEditUser(props.row)">
                <strong>{{ props.row.displayname}}</strong>
              </a>
              {{ props.row.props.Description}}
            </td>
            <td class="fancy quota-min-width">
              <div class="progress-description adjust-progress-description">
                <strong>{{$t('mailboxes.usage')}}:</strong>
              </div>
              <div class="progress progress-xs progress-label-top-right">
                <div
                  class="progress-bar"
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
            <td class="fancy">
              <span class="fa fa-calendar"></span>
              {{ props.row.props.MailSpamRetentionTime}}
            </td>
            <td class="fancy">
              <span class="fa fa-share"></span>
              {{ props.row.props.MailForwardAddress || '-'}}
            </td>
            <td>
              <button @click="openEditUser(props.row)" class="btn btn-default">
                <span class="fa fa-pencil span-right-margin"></span>
                {{$t('edit')}}
              </button>
            </td>
          </template>
        </vue-good-table>
      </div>

      <div class="tab-pane fade active" id="groups-tab" role="tabpanel" aria-labelledby="hosts-tab">
        <h3>{{$t('list')}}</h3>
        <div v-if="!view.isLoaded" class="spinner spinner-lg"></div>
        <vue-good-table
          v-show="view.isLoaded"
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
            <td class="fancy">
              <span class="fa fa-users span-right-icon"></span>
              <a @click="openEditGroup(props.row)">
                <strong>{{ props.row.name}}</strong>
              </a>
            </td>
            <td class="fancy">
              <span class="fa fa-lock"></span>
              <span
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                :title="a.rights.replace(/ /g,'<br>')"
                class="span-left-margin"
                v-for="(a, ak) in props.row.acls"
                v-bind:key="ak"
              >
                {{a.name || '-'}}
                <span v-show="ak+1 != props.row.acls.length">,</span>
              </span>
            </td>
            <td>
              <button @click="openEditGroup(props.row)" class="btn btn-default">
                <span class="fa fa-pencil span-right-margin"></span>
                {{$t('edit')}}
              </button>
            </td>
          </template>
        </vue-good-table>
      </div>

      <div class="tab-pane fade active" id="public-tab" role="tabpanel" aria-labelledby="hosts-tab">
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
              <span class="fa fa-globe span-right-icon"></span>
              <a @click="openEditPublic(props.row)">
                <strong>{{ props.row.name}}</strong>
              </a>
            </td>
            <td class="fancy">
              <span class="fa fa-lock"></span>
              <span
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                :title="a.rights.replace(/ /g,'<br>')"
                class="span-left-margin"
                v-for="(a, ak) in props.row.acls"
                v-bind:key="ak"
              >
                {{a.name || '-'}}
                <span v-show="ak+1 != props.row.acls.length">,</span>
              </span>
            </td>
            <td>
              <button @click="openEditPublic(props.row)" class="btn btn-default">
                <span class="fa fa-pencil span-right-margin"></span>
                {{$t('edit')}}
              </button>
            </td>
          </template>
        </vue-good-table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Mailboxes",
  beforeRouteLeave(to, from, next) {
    $(".modal").modal("hide");
    next();
  },
  mounted() {
    $("#users-tab-parent").click();
    this.getAll();
    this.getConfiguration();
  },
  data() {
    return {
      view: {
        isLoaded: false,
        opened: false
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
          label: this.$i18n.t("mailboxes.spam_retention"),
          field: "props.MailSpamRetentionTime",
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
      configuration: {}
    };
  },
  methods: {
    toggleDetails() {
      this.view.opened = !this.view.opened;
    },
    getAll() {
      var context = this;

      context.view.isLoaded = false;
      nethserver.exec(
        ["nethserver-mail/mailbox/read"],
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
          context.view.isLoaded = true;
          context.usersRows = success["users"];
          context.groupsRows = success["groups"];
          context.publicRows = success["public"];

          setTimeout(function() {
            $('[data-toggle="tooltip"]').tooltip();
          }, 500);
        },
        function(error) {
          console.error(error);
        }
      );
    },
    getConfiguration() {
      var context = this;

      context.view.isLoaded = false;
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
          context.view.isLoaded = true;
          context.configuration = success["configuration"];
        },
        function(error) {
          console.error(error);
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
</style>
