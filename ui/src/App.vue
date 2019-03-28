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
    <nav id="navbar-left" class="nav-pf-vertical nav-pf-vertical-with-sub-menus nav-pf-persistent-secondary panel-group">
        <ul class="list-group panel">
            <router-link tag="li" to="/dashboard" active-class="active" class="list-group-item" id="dashboard-item">
                <a>
                    <span class="fa fa-cube"></span>
                    <span class="list-group-item-value">{{$t('dashboard.menu_title')}}</span>
                </a>
            </router-link>
            <li class="li-empty"></li>
            <router-link tag="li" to="/queue" active-class="active" class="list-group-item">
                <a>
                    <span class="fa fa-share"></span>
                    <span class="list-group-item-value">{{$t('queue.menu_title')}}</span>
                </a>
            </router-link>
            <router-link tag="li" to="/filter" active-class="active" class="list-group-item">
                <a>
                    <span class="fa pficon-filter"></span>
                    <span class="list-group-item-value">{{$t('filter.menu_title')}}</span>
                </a>
            </router-link>
            <router-link tag="li" to="/transport" active-class="active" class="list-group-item">
                <a>
                    <span class="fa fa-send"></span>
                    <span class="list-group-item-value">{{$t('transport.menu_title')}}</span>
                </a>
            </router-link>
            <router-link tag="li" to="/mailboxes" active-class="active" class="list-group-item">
                <a>
                    <span class="fa fa-inbox"></span>
                    <span class="list-group-item-value">{{$t('mailboxes.menu_title')}}</span>
                </a>
            </router-link>
            <router-link tag="li" to="/addresses" active-class="active" class="list-group-item">
                <a>
                    <span class="fa fa-at"></span>
                    <span class="list-group-item-value">{{$t('addresses.menu_title')}}</span>
                </a>
            </router-link>
            <li class="li-empty"></li>
            <router-link tag="li" to="/logs" active-class="active" class="list-group-item">
                <a>
                    <span class="fa fa-list"></span>
                    <span class="list-group-item-value">{{$t('logs.title')}}</span>
                </a>
            </router-link>
            <router-link tag="li" to="/about" active-class="active" class="list-group-item">
                <a>
                    <span class="fa fa-info"></span>
                    <span class="list-group-item-value">{{$t('about.title')}}</span>
                </a>
            </router-link>
        </ul>
    </nav>
    <div class="container-fluid container-cards-pf">
        <router-view/>
    </div>
</div>
</template>

<style>
.floatleft {
    float: left;
}
</style>
