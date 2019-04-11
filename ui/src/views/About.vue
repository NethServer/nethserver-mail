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
    <h2>{{ $t('about.title') }}</h2>

    <div v-if="!view.isLoaded" class="spinner spinner-lg view-spinner"></div>
    <div v-if="view.isLoaded">
      <img class="logo" src="logo.png">
      <h2>{{ info.name }}</h2>
      <h3>{{ info.description }}</h3>

      <div class="list-group-item info-item">
        <span class="fa fa-code m-right-sm"></span>
        <strong>{{ info.release.version }}</strong>
      </div>

      <div class="list-group-item info-item">
        <span class="fa fa-globe m-right-sm"></span>
        <a target="_blank" :href="info.homepage">{{ $t('about.website') }}</a>
      </div>
      <div class="list-group-item info-item">
        <span class="fa fa-bug m-right-sm"></span>
        <a target="_blank" :href="info.bugs.url">{{ $t('about.bug_tracker') }}</a>
      </div>

      <div class="list-group-item info-item">
        <span class="fa fa-user m-right-sm"></span>
        <span>{{ info.author.name }} | {{ info.author.email }}</span>
      </div>
    </div>
  </div>
</template>

<script>
var nethserver = window.nethserver;
var console = window.console;

export default {
  name: "About",
  mounted() {
    this.getInfo();
  },
  data() {
    return {
      view: {
        isLoaded: false
      },
      info: {}
    };
  },
  methods: {
    getInfo() {
      var context = this;
      nethserver.exec(
        ["system-apps/read"],
        {
          action: "info",
          name: "nethserver-mail"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
            context.view.isLoaded = true;
            context.info = success;
          } catch (e) {
            console.error(e);
            context.view.isLoaded = true;
          }
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
.m-right-sm {
  margin-right: 5px;
}

.info-item {
  font-size: 14px;
}

.logo {
  float: right;
  width: 65px;
  height: 65px;
}
</style>
