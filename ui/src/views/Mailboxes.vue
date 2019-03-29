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
              <a
                tabindex="0"
                role="button"
                data-toggle="popover"
                data-html="true"
                data-placement="top"
                :title="$t('mailboxes.aliases')"
                :id="'popover-'+props.row.name | sanitize"
                @click="aliasDetails(props.row)"
              >
                <strong>{{ props.row.displayname}}</strong>
              </a>
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
            <td :class="['fancy', props.row.enabled ? '' : 'gray']">
              <span class="fa fa-users span-right-icon"></span>
              <a
                tabindex="0"
                role="button"
                :data-toggle="props.row.enabled ? 'popover': ''"
                data-html="true"
                data-placement="top"
                :title="$t('mailboxes.aliases')"
                :id="'popover-'+props.row.name | sanitize"
                @click="props.row.enabled ? aliasDetails(props.row) : undefined"
                :class="[props.row.enabled ? '' : 'gray']"
              >
                <strong>{{ props.row.name}}</strong>
              </a>
            </td>
            <td>
              <button
                v-if="props.row.enabled"
                @click="openEditGroup(props.row)"
                class="btn btn-default"
              >
                <span class="fa fa-pencil span-right-margin"></span>
                {{$t('edit')}}
              </button>
              <button
                v-if="!props.row.enabled"
                @click="openEnableGroup(props.row)"
                class="btn btn-primary"
              >
                <span class="fa fa-check span-right-margin"></span>
                {{$t('enable')}}
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
              <span class="fa fa-folder-open span-right-icon"></span>
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
              <div class="dropdown pull-right dropdown-kebab-pf">
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
                  <li @click="openDeletePublic(r)">
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
    this.initListeners(0);
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
    toggleDetails() {
      this.view.opened = !this.view.opened;
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
          ["nethserver-mail/mailboxes/read"],
          {
            action: "aliases",
            name: mailbox.name
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

          setTimeout(function() {
            $('[data-toggle="tooltip"]').tooltip();
          }, 500);

          setTimeout(function() {
            context.enablePopover();
          }, 250);
        },
        function(error) {
          console.error(error);
        }
      );
    },
    enablePopover() {
      $("[data-toggle=popover]")
        .popovers()
        .popovers()
        .on("hidden.bs.popover", function(e) {
          $(e.target).data("bs.popover").inState.click = false;
        });
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
