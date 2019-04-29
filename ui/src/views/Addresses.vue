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
      :chapter="'mail'"
      :section="'email-addresses'"
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
      <div class="tab-content">
        <h3>{{$t('actions')}}</h3>
        <button
          @click="openCreateAddress()"
          class="btn btn-primary btn-lg"
        >{{$t('addresses.add_alias')}}</button>

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
              <a
                :class="[props.row.builtin ? 'builtin-color' : '']"
                @click="props.row.builtin ? undefined : openEditAddress(props.row)"
              >
                <strong>{{ props.row.name}}</strong>
              </a>
              <span
                class="span-left-margin gray"
                v-if="props.row.wildcard"
              >({{$t('addresses.wildcard')}})</span>
              <span
                class="span-left-margin gray"
                v-if="props.row.builtin"
              >({{$t('addresses.builtin')}})</span>
              <span
                v-if="props.row.props.Description && props.row.props.Description.length > 0"
                class="gray span-left-margin description"
              >{{ props.row.props.Description}}</span>
            </td>
            <td class="fancy">
              <span
                class="span-left-margin"
                v-for="(a, ak) in props.row.props.Account"
                v-bind:key="ak"
              >
                <span :class="['fa', getIcon(a), 'span-right-icon-mg']"></span>
                {{a.displayname || a.name || '-'}}
                <span
                  v-show="ak+1 != props.row.props.Account.length"
                >,</span>
              </span>
            </td>
            <td class="fancy">
              <span :class="[props.row.props.Access == 'private' ? 'fa fa-lock' : 'fa fa-globe']"></span>
              {{props.row.props.Access == 'private' ? $t('addresses.internal') : $t('addresses.public')}}
            </td>
            <td>
              <button
                v-if="!props.row.builtin"
                @click="openEditAddress(props.row)"
                class="btn btn-default"
              >
                <span class="fa fa-pencil span-right-margin"></span>
                {{$t('edit')}}
              </button>
              <button
                v-if="props.row.builtin"
                @click="togglePrivate(props.row)"
                :class="['btn', props.row.props.Access == 'public' ? 'btn-default' : 'btn-default']"
              >
                <span
                  :class="['fa', props.row.props.Access == 'private' ? 'fa-globe' : 'fa-lock', 'span-right-margin']"
                ></span>
                {{props.row.props.Access == 'private' ? $t('addresses.make_public') : $t('addresses.make_private')}}
              </button>
              <div v-if="!props.row.builtin" class="dropup pull-right dropdown-kebab-pf">
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
                  <li @click="openDeleteAddress(props.row)">
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

    <div class="modal" id="newAddressModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4
              class="modal-title"
            >{{newAddress.isEdit ? $t('addresses.edit_alias') + ' '+ newAddress.name : $t('addresses.add_alias')}}</h4>
          </div>
          <form class="form-horizontal" v-on:submit.prevent="saveAddress(newAddress)">
            <div class="modal-body">
              <div
                :class="['form-group', newAddress.errors.name.hasError || newAddress.errors.domains.hasError ? 'has-error' : '']"
              >
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('addresses.name')}}</label>
                <div :class="[newAddress.isEdit ? 'col-sm-9' : 'col-sm-4']">
                  <input
                    :disabled="newAddress.isEdit"
                    required
                    type="text"
                    v-model="newAddress.name"
                    class="form-control"
                  >
                  <span
                    v-if="newAddress.errors.name.hasError"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+newAddress.errors.name.message)}}</span>
                </div>
                <div v-if="!newAddress.isEdit" class="col-sm-1">
                  <span class="at-address">@</span>
                </div>
                <div v-if="!newAddress.isEdit" class="col-sm-4">
                  <select
                    class="selectpicker"
                    multiple
                    v-model="newAddress.domains"
                    :title="$t('addresses.all')"
                  >
                    <option v-bind:value="d" v-for="(d, dk) in domains" v-bind:key="dk">{{d}}</option>
                  </select>
                  <span
                    v-if="newAddress.errors.domains.hasError"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+newAddress.errors.domains.message)}}</span>
                </div>
              </div>

              <div
                v-show="newAddress.domains && newAddress.domains.length == 0"
                class="alert alert-info alert-dismissable"
              >
                <span class="pficon pficon-info"></span>
                <strong>{{$t('info')}}</strong>
                {{$t('addresses.creation_wildcard_users')}}.
              </div>

              <div :class="['form-group', newAddress.errors.Account.hasError ? 'has-error' : '']">
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('addresses.destinations')}}</label>
                <div class="col-sm-9">
                  <suggestions
                    v-model="newAddress.destToAdd"
                    :options="autoOptions"
                    :onInputChange="filterSrcAuto"
                    :onItemSelected="selectSrcAuto"
                    :required="!newAddress.isEdit"
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
                    v-if="newAddress.errors.Account.hasError"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+newAddress.errors.Account.message)}}</span>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label" for="textInput-modal-markup"></label>
                <div class="col-sm-9">
                  <ul class="list-inline compact">
                    <li
                      v-for="(i, ki) in newAddress.Destinations"
                      v-bind:key="ki"
                      class="mg-bottom-5"
                    >
                      <span class="label label-info">
                        {{i.displayname || i.name}}
                        <a
                          @click="removeDestToAlias(ki)"
                          class="remove-item-inline"
                        >
                          <span class="fa fa-times"></span>
                        </a>
                      </span>
                    </li>
                  </ul>
                </div>
              </div>

              <legend class="fields-section-header-pf" aria-expanded="true">
                <span
                  :class="['fa fa-angle-right field-section-toggle-pf', newAddress.advanced ? 'fa-angle-down' : '']"
                ></span>
                <a
                  class="field-section-toggle-pf"
                  @click="toggleAdvancedMode()"
                >{{$t('advanced_mode')}}</a>
              </legend>

              <div
                :class="['form-group', newAddress.errors.Account.hasError && newAddress.External.length > 0 ? 'has-error' : '']"
                v-if="newAddress.advanced"
              >
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('addresses.external_mail_addresses')}}</label>
                <div class="col-sm-9">
                  <textarea
                    type="text"
                    v-model="newAddress.External"
                    class="form-control min-textarea-height"
                  ></textarea>
                  <span
                    v-if="newAddress.errors.Account.hasError && newAddress.External.length > 0"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+newAddress.errors.Account.message)}}</span>
                </div>
              </div>
              <div
                :class="['form-group', newAddress.errors.Description.hasError ? 'has-error' : '']"
                v-if="newAddress.advanced"
              >
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('addresses.description')}}</label>
                <div class="col-sm-9">
                  <input type="text" v-model="newAddress.props.Description" class="form-control">
                  <span
                    v-if="newAddress.errors.Description.hasError"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+newAddress.errors.Description.message)}}</span>
                </div>
              </div>
              <div
                :class="['form-group', newAddress.errors.Access.hasError ? 'has-error' : '']"
                v-if="newAddress.advanced"
              >
                <label
                  class="col-sm-3 control-label"
                  for="textInput-modal-markup"
                >{{$t('addresses.local_network_only')}}</label>
                <div class="col-sm-9">
                  <input type="checkbox" v-model="newAddress.props.Access" class="form-control">
                  <span
                    v-if="newAddress.errors.Access.hasError"
                    class="help-block"
                  >{{$t('validation.validation_failed')}}: {{$t('validation.'+newAddress.errors.Access.message)}}</span>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <div v-if="newAddress.isLoading" class="spinner spinner-sm form-spinner-loader"></div>
              <button class="btn btn-default" type="button" data-dismiss="modal">{{$t('cancel')}}</button>
              <button
                class="btn btn-primary"
                type="submit"
              >{{newAddress.isEdit ? $t('edit') : $t('save')}}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal" id="deleteAddressModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{$t('addresses.delete_alias')}} {{currentAddress.name}}</h4>
          </div>
          <form class="form-horizontal" v-on:submit.prevent="deleteAddress(currentAddress)">
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
  </div>
</template>

<script>
export default {
  name: "Adress",
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
    this.getDestinations();
    this.getDomains();
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
      tableLangsTexts: this.tableLangs(),
      autoOptions: {
        inputClass: "form-control"
      },
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
      rows: [],
      newAddress: this.initAddress(),
      currentAddress: {},
      destinations: [],
      domains: []
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
    initAddress() {
      return {
        isLoading: false,
        isEdit: false,
        name: "",
        domains: [],
        External: "",
        Destinations: [],
        props: {
          Access: false,
          Account: [],
          Description: ""
        },
        destToAdd: "",
        errors: this.initAddressErrors(),
        advanced: false
      };
    },
    initAddressErrors() {
      return {
        name: {
          hasError: false,
          message: ""
        },
        domains: {
          hasError: false,
          message: ""
        },
        Access: {
          hasError: false,
          message: ""
        },
        Account: {
          hasError: false,
          message: ""
        },
        Description: {
          hasError: false,
          message: ""
        },
        External: {
          hasError: false,
          message: ""
        }
      };
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
      this.addDestToAlias(item);
      this.newAddress.destToAdd = item.displayname || item.name;
    },
    destAlreadyAdded(bind) {
      var found = false;
      for (var i in this.newAddress.Destinations) {
        var account = this.newAddress.Destinations[i];
        if (account.name == bind.name) {
          found = true;
        }
      }
      return found;
    },
    addDestToAlias(dest) {
      if (dest.name.length > 0 && dest.name != "-") {
        if (!this.destAlreadyAdded(dest)) {
          this.newAddress.Destinations.push({
            name: dest.name,
            displayname: dest.displayname || dest.name,
            type: dest.type
          });
        }
      }
    },
    removeDestToAlias(index) {
      this.newAddress.Destinations.splice(index, 1);
    },
    toggleAdvancedMode() {
      this.newAddress.advanced = !this.newAddress.advanced;
      this.$forceUpdate();
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

        case "root":
          return "fa-eye";
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
          context.destinations = success.users.concat(
            success.groups.concat(success.public)
          );
        },
        function(error) {
          console.error(error);
        }
      );
    },
    getDomains() {
      var context = this;

      nethserver.exec(
        ["nethserver-mail/domains/read"],
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
          context.domains = success.domains.map(function(d) {
            return d.name;
          });
        },
        function(error) {
          console.error(error);
        }
      );
    },
    openCreateAddress() {
      this.newAddress = this.initAddress();
      setTimeout(function() {
        $(".selectpicker").selectpicker();
      }, 50);

      $("#newAddressModal").modal("show");
    },
    openEditAddress(address) {
      address.Destinations = Object.values(
        Object.assign(
          {},
          address.props.Account.filter(function(a) {
            return a.type != "external";
          })
        )
      );
      address.External = Object.values(
        Object.assign(
          {},
          address.props.Account.filter(function(a) {
            return a.type == "external";
          })
        )
      )
        .map(function(a) {
          return a.name;
        })
        .join("\n");
      this.newAddress = Object.assign({}, address);
      this.newAddress.errors = this.initAddressErrors();
      this.newAddress.isLoading = false;
      this.newAddress.isEdit = true;
      this.newAddress.props.Access = this.newAddress.props.Access == "private";
      this.newAddress.destToAdd = this.newAddress.Destinations.map(function(a) {
        return a.displayname || a.name;
      }).join(",");

      $("#newAddressModal").modal("show");
    },
    openDeleteAddress(address) {
      this.currentAddress = Object.assign({}, address);
      $("#deleteAddressModal").modal("show");
    },
    saveAddress(address) {
      var context = this;

      var external =
        address.External.length > 0
          ? address.External.split("\n").map(function(a) {
              return {
                name: a,
                type: "external"
              };
            })
          : [];

      var addressObj = {
        action: address.isEdit ? "update-pseudonym" : "create-pseudonym",
        domains: address.isEdit ? null : address.domains,
        Description: address.props.Description,
        Access: address.props.Access ? "private" : "public",
        Account: address.Destinations.concat(external),
        name: address.name
      };

      context.newAddress.isLoading = true;
      nethserver.exec(
        ["nethserver-mail/pseudonym/validate"],
        addressObj,
        null,
        function(success) {
          context.newAddress.isLoading = false;
          $("#newAddressModal").modal("hide");

          // notification
          nethserver.notifications.success = context.$i18n.t(
            "addresses.address_" +
              (context.newAddress.isEdit ? "updated" : "created") +
              "_ok"
          );
          nethserver.notifications.error = context.$i18n.t(
            "addresses.address_" +
              (context.newAddress.isEdit ? "updated" : "created") +
              "_error"
          );

          // update values
          if (address.isEdit) {
            nethserver.exec(
              ["nethserver-mail/pseudonym/update"],
              addressObj,
              function(stream) {
                console.info("address", stream);
              },
              function(success) {
                // get addresses
                context.getAll();
              },
              function(error, data) {
                console.error(error, data);
              }
            );
          } else {
            nethserver.exec(
              ["nethserver-mail/pseudonym/create"],
              addressObj,
              function(stream) {
                console.info("addresses", stream);
              },
              function(success) {
                // get addresses
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
          context.newAddress.isLoading = false;
          context.newAddress.errors = context.initAddressErrors();

          try {
            errorData = JSON.parse(data);
            for (var e in errorData.attributes) {
              var attr = errorData.attributes[e];
              context.newAddress.errors[attr.parameter].hasError = true;
              context.newAddress.errors[attr.parameter].message = attr.error;
            }

            context.$forceUpdate();
          } catch (e) {
            console.error(e);
          }
        }
      );
    },
    togglePrivate(address) {
      var context = this;

      // notification
      nethserver.notifications.success = context.$i18n.t(
        "addresses.address_" +
          (context.newAddress.isEdit ? "updated" : "created") +
          "_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "addresses.address_" +
          (context.newAddress.isEdit ? "updated" : "created") +
          "_error"
      );

      nethserver.exec(
        ["nethserver-mail/pseudonym/update"],
        {
          action: "update-builtin",
          Access: address.props.Access == "private" ? "public" : "private",
          type: address.type,
          name: address.name
        },
        function(stream) {
          console.info("address", stream);
        },
        function(success) {
          // get addresses
          context.getAll();
        },
        function(error, data) {
          console.error(error, data);
        }
      );
    },
    deleteAddress(address) {
      var context = this;

      // notification
      nethserver.notifications.success = context.$i18n.t(
        "addresses.address_deleted_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "addresses.address_deleted_error"
      );

      $("#deleteAddressModal").modal("hide");
      nethserver.exec(
        ["nethserver-mail/pseudonym/delete"],
        {
          name: address.name,
          action: "delete"
        },
        function(stream) {
          console.info("addresss", stream);
        },
        function(success) {
          // get addresss
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

.span-right-icon-mg {
  font-size: 15px;
  margin-right: 4px;
}

.at-address {
  font-size: 16px;
}

.builtin-color {
  color: #ec7a08;
}
.builtin-color:hover {
  color: #ec7a08;
  cursor: initial;
}
</style>
