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
    <h1>{{ $t('dashboard.title') }}</h1>
    <div v-if="vReadStatus == 'running'" class="spinner spinner-lg view-spinner"></div>
    <div v-else-if="vReadStatus == 'error'">
        <div class="alert alert-danger">
          <span class="pficon pficon-error-circle-o"></span>
          <strong>OOOPS!</strong> An unexpected error has occurred:<pre>{{ vReadError }}</pre>
        </div>
    </div>
    <div v-else>

    <div class="row">
        <div class="col-xs-12 col-sm-2 col-md-4 col-lg-2">
            <p>{{ $t('dashboard.email_domains_label') }}</p>
        </div>
        <div class="col-xs-12 col-sm-10 col-md-8 col-lg-10">
            <p>{{ domains.join(', ') }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-2 col-md-4 col-lg-2">
            <p>{{ $t('dashboard.installed_components_label') }}</p>
        </div>
        <div class="col-xs-12 col-sm-10 col-md-8 col-lg-10">
            <p>{{ installedComponents.join(', ') }}</p>
        </div>
    </div>

    <div class="row row-eq-height">

        <div v-if="packages['filter'] > 0" class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <span class="icon-header-panel"><span class="fa fa-shield"></span></span>
                        {{ $t('dashboard.filter_card_title') }}
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="services-status">
                        <span class="service-status">
                            <span
                                :class="'fa ' + faServiceStatusIcon(services['rspamd'])"
                                :title="faServiceStatusLabel(services['rspamd'])"
                                data-toggle="tooltip"
                                data-placement="top" ></span>
                            <span class="service-name">{{ $t('dashboard.rspamd_service_label') }}</span>
                        </span>
                        <span class="service-status">
                            <span
                                :class="'fa ' + faServiceStatusIcon(services['clamd@rspamd'])"
                                :title="faServiceStatusLabel(services['clamd@rspamd'])"
                                data-toggle="tooltip"
                                data-placement="top" ></span>
                            <span class="service-name">{{ $t('dashboard.clamd_service_label') }}</span>
                        </span>
                    </div>
                <div id="rspamd-pie-chart"></div>
                </div>
            </div>
        </div>

        <div v-if="packages['server'] > 0" class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <span class="icon-header-panel"><span class="fa fa-user-circle-o"></span></span>
                        {{ $t('dashboard.server_card_title') }}
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="services-status">
                        <span class="service-status">
                            <span
                                :class="'fa ' + faServiceStatusIcon(services['dovecot'])"
                                :title="faServiceStatusLabel(services['dovecot'])"
                                data-toggle="tooltip"
                                data-placement="top" ></span>
                            <span class="service-name">{{ $t('dashboard.dovecot_service_label') }}</span>
                        </span>
                    </div>
                    <div class="row-inline-block">
                        <div class="stats-container col-xs-6 col-sm-6 col-md-6 col-lg-6">
                            <span class="card-pf-utilization-card-details-count stats-count">{{ statistics.mailboxes }}</span>
                            <span class="card-pf-utilization-card-details-description stats-description">
                                <span class="card-pf-utilization-card-details-line-2 stats-text">{{ $t('dashboard.stats_mailboxes_label') }}</span>
                            </span>
                        </div>
                        <div class="stats-container col-xs-6 col-sm-6 col-md-6 col-lg-6">
                            <span class="card-pf-utilization-card-details-count stats-count">{{ quota.status == 'enabled' ? this.$options.filters.formatBytes(quota.size) : $t('dashboard.stats_quota_na') }}</span>
                            <span class="card-pf-utilization-card-details-description stats-description">
                                <span class="card-pf-utilization-card-details-line-2 stats-text">{{ $t('dashboard.stats_quota_label') }}</span>
                            </span>
                        </div>
                        <div class="stats-container col-xs-6 col-sm-6 col-md-6 col-lg-6">
                            <span class="card-pf-utilization-card-details-count stats-count">{{ statistics.pseudonyms }}</span>
                            <span class="card-pf-utilization-card-details-description stats-description">
                                <span class="card-pf-utilization-card-details-line-2 stats-text">{{ $t('dashboard.stats_aliases_label') }}</span>
                            </span>
                        </div>
                        <div class="stats-container col-xs-6 col-sm-6 col-md-6 col-lg-6">
                            <span class="card-pf-utilization-card-details-count stats-count">{{ statistics.externals }}</span>
                            <span class="card-pf-utilization-card-details-description stats-description">
                                <span class="card-pf-utilization-card-details-line-2 stats-text">{{ $t('dashboard.stats_forwarded_label') }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <span class="icon-header-panel"><span class="fa fa-send"></span></span>
                        {{ $t('dashboard.transport_card_title') }}
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="services-status">
                        <span class="service-status">
                            <span
                                :class="'fa ' + faServiceStatusIcon(services['postfix'])"
                                :title="faServiceStatusLabel(services['postfix'])"
                                data-toggle="tooltip"
                                data-placement="top" ></span>
                            <span class="service-name">{{ $t('dashboard.postfix_service_label') }}</span>
                        </span>
                        <span class="service-status">
                            <span
                                :class="'fa ' + faServiceStatusIcon(services['opendkim'])"
                                :title="faServiceStatusLabel(services['opendkim'])"
                                data-toggle="tooltip"
                                data-placement="top" ></span>
                            <span class="service-name">{{ $t('dashboard.opendkim_service_label') }}</span>
                        </span>
                    </div>
                    <div class="stats-container col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <span class="card-pf-utilization-card-details-count stats-count">{{ queue }}</span>
                        <span class="card-pf-utilization-card-details-description stats-description">
                            <span class="card-pf-utilization-card-details-line-2 stats-text">{{ $t('dashboard.transport_queue_label') }}</span>
                        </span>
                    </div>
                </div>
                <div class="panel-footer">
                    {{ configuration['SmartHostStatus'] == 'enabled' ? $t('dashboard.smarthost_configured_yes') : $t('dashboard.smarthost_configured_no') }}
                </div>
            </div>
        </div>

    </div>


    <h3>{{ $t('dashboard.tcp_connections_title') }}</h3>
    <div v-if="activeConnections.length == 0" class="row row-stat">
        <div class="row-inline-block">
            <div class="stats-container col-xs-12 col-sm-12 col-md-12 col-lg-12">
                {{ $t('dashboard.tcp_connections_none') }}
            </div>
        </div>
    </div>
    <div v-else class="row row-stat">
        <div class="row-inline-block" v-for="conn in activeConnections" v-bind:key="conn.proto" v-bind:id="'stats-proto-' + item">
            <div class="stats-container col-xs-12 col-sm-4 col-md-3 col-lg-2">
                <span class="card-pf-utilization-card-details-count stats-count">{{ conn.count }}</span>
                <span class="card-pf-utilization-card-details-description stats-description">
                    <span class="card-pf-utilization-card-details-line-2 stats-text">{{ conn.proto }}</span>
                </span>
            </div>
        </div>
    </div>

    </div> <!-- success: end v-else  -->
  </div>
</template>

<script>
import execp from '@/execp'
import filters from '@/filters'
import generatePieChart from '@/piechart'

export default {
    filters,
    name: "Dashboard",
    mounted() {
        execp("nethserver-mail/dashboard/read")
        .then(result => {
            for(var k in result) {
                this[k] = result[k]
            }
            this.vReadStatus = 'success'
        })
        .catch(error => {
            this.vReadStatus = 'error'
            this.vReadError = error
        })
    },
    updated() {
        var $ = window.jQuery
        $('[data-toggle="tooltip"]').tooltip();
        if( ! this.rspamdPieChart) {
            this.rspamdPieChart = generatePieChart('#rspamd-pie-chart', {
                names: {
                    reject: this.$t('dashboard.reject_rspamd_graph_label'),
                    greylist: this.$t('dashboard.greylist_rspamd_graph_label'),
                    probable: this.$t('dashboard.probable_rspamd_graph_label'),
                    clean: this.$t('dashboard.clean_rspamd_graph_label')
                },
                colors: {
                    reject: $.pfPaletteColors.red,
                    greylist: $.pfPaletteColors.blue,
                    probable: $.pfPaletteColors.orange,
                    clean: $.pfPaletteColors.green
                },
                columns: []
            })
        }
        this.rspamdPieChart.load({
            columns: [
                ['reject', this.rspamd.reject],
                ['greylist', this.rspamd.greylist],
                ['probable', this.rspamd.probable],
                ['clean', this.rspamd.clean]
            ]
        })
    },
    data() {
        return {
            vReadStatus: 'running',
            rspamd: {
                reject: 0,
                greylist: 0,
                probable: 0,
                clean: 0
            },
        }
    },
    computed: {
        installedComponents: function() {
            return Object.keys(this.packages).filter(k => this.packages[k] > 0).sort();
        },
        activeConnections: function() {
            var ret = []
            for(let conn of Object.keys(this.connections).sort()) {
                ret.push({ proto: conn, count: parseInt(this.connections[conn])})
            }
            return ret
        }
    },
    methods: {
        faServiceStatusIcon: function(status) {
            return status ? 'fa-check green' : 'fa-times red';
        },
        faServiceStatusLabel: function(status) {
            return status ? this.$t('dashboard.service_status_running_tooltip') : this.$t('dashboard.service_status_stopped_tooltip');
        }
    },
}
</script>

<style>
.services-status {
    min-height: 2em;
}

.service-status {
    display: inline-block;
    margin-right: 1em;
    white-space: nowrap;
}

.service-status > .fa {
    margin-right: 0.5ex;
    vertical-align: middle;
}

.green {
    color: #3f9c35;
}
.red {
    color: #c00;
}

.row-eq-height {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  flex-flow: row wrap;
}

.row-eq-height > div {
  margin: 20px 0;
}

.row-eq-height .panel {
    min-height: 100%;
    display: flex;
    flex-direction: column;
}

.panel-body {
    flex-grow: 1;
}

.panel-body [class^="pficon-"] {
    vertical-align: middle;
    margin-right: 0.5ex;
}

.icon-header-panel .fa {
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
</style>
