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
    <h1>{{ $t('addresses.title') }}</h1>
    <doc-info
      :placement="'top'"
      :title="$t('docs.addresses')"
      :chapter="'addresses'"
      :section="''"
      :inline="false"
    ></doc-info>

    <div class="tab-content">
      <h3>{{$t('list')}}</h3>
      <div v-if="!view.isLoaded" class="spinner spinner-lg"></div>
      <vue-good-table
        v-show="view.isLoaded"
        :customRowsPerPageDropdown="[25,50,100]"
        :perPage="25"
        :columns="columns"
        :rows="rows"
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
            <a @click="openEdit(props.row)">
              <strong>{{ props.row.name}}</strong>
            </a>
            <span
              class="span-left-margin gray"
              v-if="props.row.wildcard"
            >({{$t('addresses.wildcard')}})</span>
          </td>
          <td class="fancy">
            <span
              class="span-left-margin"
              v-for="(a, ak) in props.row.props.Account"
              v-bind:key="ak"
            >
              <span :class="['fa', getIcon(a), 'span-right-icon-mg']"></span>
              {{a.name || '-'}}
              <span v-show="ak+1 != props.row.props.Account.length">,</span>
            </span>
          </td>
          <td class="fancy">
            <span :class="[props.row.props.Access == 'private' ? 'fa fa-check' : 'fa fa-times']"></span>
            {{props.row.props.Access == 'private' ? $t('yes') : $t('no')}}
          </td>
          <td>
            <button @click="openEdit(props.row)" class="btn btn-default">
              <span class="fa fa-pencil span-right-margin"></span>
              {{$t('edit')}}
            </button>
          </td>
        </template>
      </vue-good-table>
    </div>
  </div>
</template>

<script>
export default {
  name: "Adress",
  beforeRouteLeave(to, from, next) {
    $(".modal").modal("hide");
    next();
  },
  mounted() {
    this.getAll();
  },
  data() {
    return {
      view: {
        isLoaded: false
      },
      tableLangsTexts: this.tableLangs(),
      columns: [
        {
          label: this.$i18n.t("addresses.name"),
          field: "name",
          filterable: true
        },
        {
          label: this.$i18n.t("addresses.destinations"),
          field: "props.Account",
          type: "number",
          filterable: true,
          sortable: false
        },
        {
          label: this.$i18n.t("addresses.local_network_only"),
          field: "Access",
          filterable: true
        },
        {
          label: this.$i18n.t("action"),
          field: "",
          filterable: true,
          sortable: false
        }
      ],
      rows: []
    };
  },
  methods: {
    getIcon(account) {
      switch (account.type) {
        case "user":
          return "fa-user";
          break;

        case "group":
          return "fa-users";
          break;

        case "public":
          return "fa-globe";
          break;

        case "external":
          return "fa-external-link";
          break;

        case "pseudonym":
          return "fa-user-secret";
          break;
      }
    },
    getAll() {
      var context = this;

      context.view.isLoaded = false;
      nethserver.exec(
        ["nethserver-mail/pseudonym/read"],
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
          context.rows = success["pseudonyms"];

          setTimeout(function() {
            $('[data-toggle="tooltip"]').tooltip();
          }, 500);
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

.gray {
  color: #72767b !important;
}

.span-right-icon-mg {
  font-size: 15px;
  margin-right: 4px;
}
</style>
