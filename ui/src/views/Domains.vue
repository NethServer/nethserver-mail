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

<style scoped>
.spaced {
  margin-top: 1em;
}
</style>

<template>
  <div>
    <h1>{{ $t('domains.title') }}</h1>
    <doc-info
      :placement="'top'"
      :title="$t('docs.domains')"
      :chapter="'mail'"
      :section="'domains'"
      :inline="false"
      :lang="'en'"
    ></doc-info>

    <div v-if="vReadStatus == 'running'" class="spinner spinner-lg view-spinner"></div>
    <div v-else-if="vReadStatus == 'error'">
      <div class="alert alert-danger">
        <span class="pficon pficon-error-circle-o"></span>
        <strong>OOOPS!</strong> An unexpected error has occurred:
        <pre>{{ vReadError }}</pre>
      </div>
    </div>
    <div v-else class="spaced">
      <h3>{{$t('actions')}}</h3>
      <button
        class="btn btn-primary btn-lg"
        v-on:click="openModal('modalCreateDomain', createDefaultDomain())"
      >{{ $t('domains.create_domain_button') }}</button>

      <h3>{{$t('list')}}</h3>
      <domains-list-view
        v-bind:items="domains"
        v-on:modal-close="read"
        v-on:item-edit="openModal('modalEditDomain', $event)"
        v-on:item-delete="openModal('modalDeleteDomain', $event)"
        v-on:item-dkim="openModal('modalEditDkim', $event)"
      ></domains-list-view>
    </div>

    <modal-domain-edit
      id="modalCreateDomain"
      v-on:modal-close="read($event)"
      use-case="create"
      v-bind:domain="currentItem"
      v-bind:with-disclaimer="isDisclaimerAvailable"
    ></modal-domain-edit>
    <modal-domain-edit
      id="modalEditDomain"
      v-on:modal-close="read"
      use-case="edit"
      v-bind:domain="currentItem"
      v-bind:with-disclaimer="isDisclaimerAvailable"
    ></modal-domain-edit>
    <modal-domain-edit
      id="modalDeleteDomain"
      v-on:modal-close="read"
      use-case="delete"
      v-bind:domain="currentItem"
      v-bind:with-disclaimer="isDisclaimerAvailable"
    ></modal-domain-edit>
    <modal-dkim-edit
      id="modalEditDkim"
      v-on:modal-close="read"
      v-bind:domain="currentItem"
      v-bind:dkimRawData="dkimRawData"
      v-bind:dkimTxtRecord="dkimTxtRecord"
    ></modal-dkim-edit>
  </div>
</template>

<script>
import execp from "@/execp";
import DomainsListView from "@/components/DomainsListView.vue";
import ModalDomainEdit from "@/components/ModalDomainEdit.vue";
import ModalDkimEdit from "@/components/ModalDkimEdit.vue";

export default {
  name: "Domains",
  components: {
    DomainsListView,
    ModalDomainEdit,
    ModalDkimEdit
  },
  beforeRouteLeave(to, from, next) {
    $(".modal").modal("hide");
    next();
  },
  mounted() {
    this.read();
  },
  data() {
    return {
      vReadStatus: "running",
      domains: [],
      isDisclaimerAvailable: false,
      isServerAvailable: false,
      currentItem: {},
      dkimRawData: "",
      dkimTxtRecord: "",
      defaultRecipientMailbox: {}
    };
  },
  methods: {
    createDefaultDomain() {
      return {
        unknownRecipientMailbox: this.defaultRecipientMailbox,
        name: "",
        isPrimaryDomain: false,
        TransportType: this.isServerAvailable ? "LocalDelivery" : "Relay",
        AlwaysBccStatus: "disabled",
        DisclaimerStatus: "disabled",
        OpenDkimStatus: "disabled",
        AlwaysBccAddress: "",
        UnknownRecipientsActionType: "bounce",
        Description: ""
      };
    },
    openModal(id, item) {
      this.currentItem = item;
      window.jQuery("#" + id).modal();
    },
    read(eventData = {}) {
      this.vReadStatus = "running";
      execp("nethserver-mail/domains/read", {})
        .then(result => {
          for (let k in result) {
            if (result.hasOwnProperty(k)) {
              this[k] = result[k];
            }
          }
          this.vReadStatus = "success";

          setTimeout(function() {
            $("[data-toggle=popover]")
              .popovers()
              .on("hidden.bs.popover", function(e) {
                $(e.target).data("bs.popover").inState.click = false;
              });
          }, 250);
        })
        .catch(error => {
          this.vReadStatus = "error";
          this.vReadError = error;
        })
        .then(() => {
          if (eventData.nextAction == "open-dkim-modal") {
            for (let i in this.domains) {
              if (this.domains[i].name == eventData.id) {
                this.openModal("modalEditDkim", this.domains[i]);
              }
            }
          }
        });
    }
  }
};
</script>
