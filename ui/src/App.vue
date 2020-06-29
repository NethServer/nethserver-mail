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
  <div id="app">
    <nav
      id="navbar-left"
      class="nav-pf-vertical nav-pf-vertical-with-sub-menus nav-pf-persistent-secondary panel-group"
    >
      <ul class="list-group panel">
        <li
          id="dashboard-item"
          v-bind:class="[getCurrentPath('dashboard') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/dashboard">
            <span class="fa fa-cube"></span>
            <span class="list-group-item-value">{{$t('dashboard.menu_title')}}</span>
          </a>
        </li>
        <li class="li-empty"></li>
        <li
          id="domains-item"
          v-bind:class="[getCurrentPath('domains') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/domains">
            <span class="fa fa-tags"></span>
            <span class="list-group-item-value">{{$t('domains.menu_title')}}</span>
          </a>
        </li>
        <li
          id="filter-item"
          v-bind:class="[getCurrentPath('filter') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/filter">
            <span class="fa pficon-filter"></span>
            <span class="list-group-item-value">{{$t('filter.menu_title')}}</span>
          </a>
        </li>
        <li class="li-empty"></li>
        <li
          id="mailboxes-item"
          v-bind:class="[getCurrentPath('mailboxes') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/mailboxes">
            <span class="fa fa-inbox"></span>
            <span class="list-group-item-value">{{$t('mailboxes.menu_title')}}</span>
          </a>
        </li>
        <li
          id="addresses-item"
          v-bind:class="[getCurrentPath('addresses') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/addresses">
            <span class="fa fa-at"></span>
            <span class="list-group-item-value">{{$t('addresses.menu_title')}}</span>
          </a>
        </li>
        <li
          id="connectors-item"
          v-bind:class="[getCurrentPath('connectors') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/connectors">
            <span class="fa fa-plug"></span>
            <span class="list-group-item-value">{{$t('connectors.menu_title')}}</span>
          </a>
        </li>
        <li
          id="imapsync-item"
          v-bind:class="[getCurrentPath('imapsync') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/imapsync">
            <span class="fa fa-refresh"></span>
            <span class="list-group-item-value">{{$t('imapsync.menu_title')}}</span>
          </a>
        </li>
        <li class="li-empty"></li>
        <li
          id="queue-item"
          v-bind:class="[getCurrentPath('queue') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/queue">
            <span class="fa fa-exchange"></span>
            <span class="list-group-item-value">{{$t('queue.menu_title')}}</span>
          </a>
        </li>
        <li
          id="send-item"
          v-bind:class="[getCurrentPath('send') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/send">
            <span class="fa fa-send"></span>
            <span class="list-group-item-value">{{$t('send.menu_title')}}</span>
          </a>
        </li>
        <li
          v-bind:class="[getCurrentPath('settings') ? 'active' : '', 'list-group-item']"
          class="list-group-item"
        >
          <a href="#/settings">
            <span class="fa fa-cog"></span>
            <span class="list-group-item-value">{{$t('settings.menu_title')}}</span>
          </a>
        </li>
        <li
          id="logs-item"
          v-bind:class="[getCurrentPath('logs') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/logs">
            <span class="fa fa-list"></span>
            <span class="list-group-item-value">{{$t('logs.title')}}</span>
          </a>
        </li>
        <li class="li-empty"></li>
        <li
          id="about-item"
          v-bind:class="[getCurrentPath('about') ? 'active' : '', 'list-group-item']"
        >
          <a href="#/about">
            <span class="fa fa-info"></span>
            <span class="list-group-item-value">{{$t('about.title')}}</span>
          </a>
        </li>
      </ul>
    </nav>
    <div class="container-fluid container-cards-pf">
      <router-view/>
    </div>
  </div>
</template>

<script>
export default {
  name: "App",
  watch: {
    $route: function(val) {
      localStorage.setItem("mail-path", val.path);
    }
  },
  mounted() {
    var path = localStorage.getItem("mail-path") || "/";
    this.$router.push(path);
  },
  methods: {
    getCurrentPath(route, offset) {
      if (offset) {
        return this.$route.path.split("/")[offset] === route;
      } else {
        return this.$route.path.split("/")[1] === route;
      }
    }
  }
};
</script>

<style>
.right {
  float: right;
}
.divider {
  border-bottom: 1px solid #d1d1d1;
}

.stats-container {
  padding: 20px !important;
  border-width: initial !important;
  border-style: none !important;
  border-color: initial !important;
  -o-border-image: initial !important;
  border-image: initial !important;
}

.stats-text {
  margin-top: 10px !important;
  display: block;
}

.stats-description {
  float: left;
  line-height: 1;
}

.stats-count {
  font-size: 26px;
  font-weight: 300;
  margin-right: 10px;
  float: left;
  line-height: 1;
}

.row-stat {
  margin-left: 0px;
  margin-right: 0px;
}

.compact {
  margin-bottom: 0px !important;
}

.row-inline-block {
  display: inline-block;
  width: 100%;
}

.search-pf {
  width: 50%;
}

.list-view-pf .list-group-item:first-child {
  border-top: 1px solid transparent;
}

.list-group.list-view-pf {
  border-top: 0px;
}

.list-view-pf .list-group-item {
  border-top: 1px solid #ededec;
}

.span-right-margin {
  margin-right: 4px;
}

.span-left-margin {
  margin-left: 5px;
}

.margin-left-md {
  margin-left: 10px !important;
}

.floatleft {
  float: left;
}

.small-list {
  padding-top: 5px;
  padding-bottom: 5px;
}

.small-li {
  padding-top: 3px !important;
  padding-bottom: 3px !important;
}

.multi-line {
  display: unset;
  text-align: unset;
}

.adjust-line {
  line-height: 26px;
}

.legend {
  position: absolute;
  right: 0;
  font-size: 0.8em;
  top: -10px;
  text-align: left;
}
.dygraph-label.dygraph-ylabel {
  transform: rotate(-90deg) !important;
  text-align: center;
  padding-left: 15px;
}

.no-mg-top {
  margin-top: 0px !important;
}

.mg-top-10 {
  margin-top: 10px !important;
}

.green {
  color: #3f9c35;
}

.red {
  color: #cc0000;
}

.gray {
  color: #72767b !important;
}

.v-suggestions .items {
  max-height: 290px;
  overflow-y: hidden;
  border: 1px solid #bbb;
  border-width: 1px;
}

.v-suggestions .suggestions {
  top: 23px;
  background-color: #fff;
  border-radius: 1px;
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.175);
  font-size: 12px;
  text-align: left;
}

.bootstrap-select:not([class*="col-"]):not([class*="form-control"]):not(.input-group-btn) {
  width: 160px;
}
</style>
