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
        <strong>OOOPS!</strong> An unexpected error has occurred:
        <pre>{{ vReadError }}</pre>
      </div>
    </div>
    <div v-else>
      <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
          <div class="form-horizontal">
            <div class="form-group compact">
              <label class="col-sm-3 control-label">{{ $t('dashboard.email_domains_label') }}</label>
              <div class="col-sm-9 adjust-li">
                <p class="align-domains">{{ domains.join(' | ') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row row-eq-height row-stat divider row-status container-fluid">
        <div v-if="packages['filter'] > 0" class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">
                <span class="icon-header-panel">
                  <span class="fa fa-shield"></span>
                </span>
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
                    data-placement="top"
                  ></span>
                  <span class="service-name">{{ $t('dashboard.rspamd_service_label') }}</span>
                </span>
                <span class="service-status">
                  <span
                    :class="'fa ' + faServiceStatusIcon(services['clamd@rspamd'])"
                    :title="faServiceStatusLabel(services['clamd@rspamd'])"
                    data-toggle="tooltip"
                    data-placement="top"
                  ></span>
                  <span class="service-name">{{ $t('dashboard.clamd_service_label') }}</span>
                </span>
              </div>
              <div v-if="Object.keys(rspamd).length == 0" class="empty-piechart">
                <span class="fa fa-pie-chart"></span>
                <div>{{ $t('dashboard.empty_piechart_label') }}</div>
              </div>
              <div v-else id="rspamd-pie-chart"></div>
            </div>
            <div class="panel-footer">
              <div v-if="rspamd.learned < minLearns">
                <span class="pficon pficon-warning-triangle-o filter-icon"></span>
                <span>{{$t('dashboard.bayes_not_ready')}} ({{rspamd.learned > 0 ? Math.round(rspamd.learned / minLearns * 100) : 0 }}%)</span>
                <doc-info
                  :placement="'bottom'"
                  :title="$t('docs.bayes_title')"
                  :chapter="'bayes_description'"
                  :section="'filter'"
                  :inline="true"
                  :lang="'en'"
                  class="pull-right"
                ></doc-info>
              </div>
              <div v-if="rspamd.learned >= minLearns">
                <span class="pficon pficon-ok filter-icon"></span>
                <span>{{$t('dashboard.bayes_ready')}}</span>
              </div>
              <div v-else>
                <span class="pficon pficon-info filter-icon"></span>
                <span>{{$t('dashboard.bayes_not_running')}}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="packages['server'] > 0" class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">
                <span class="icon-header-panel">
                  <span class="fa fa-user-circle-o"></span>
                </span>
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
                    data-placement="top"
                  ></span>
                  <span class="service-name">{{ $t('dashboard.dovecot_service_label') }}</span>
                </span>
              </div>
              <div class="row-inline-block">
                <div class="stats-container col-xs-6 col-sm-6 col-md-6 col-lg-6">
                  <span
                    class="card-pf-utilization-card-details-count stats-count"
                  >{{ statistics.mailboxes }}</span>
                  <span class="card-pf-utilization-card-details-description stats-description">
                    <span
                      class="card-pf-utilization-card-details-line-2 stats-text"
                    >{{ $t('dashboard.stats_mailboxes_label') }}</span>
                  </span>
                </div>
                <div class="stats-container col-xs-6 col-sm-6 col-md-6 col-lg-6">
                  <span
                    class="card-pf-utilization-card-details-count stats-count"
                  >{{ quota.status == 'enabled' ? this.$options.filters.byteFormat(quota.size) : this.$options.filters.byteFormat(0) }}</span>
                  <span class="card-pf-utilization-card-details-description stats-description">
                    <span
                      class="card-pf-utilization-card-details-line-2 stats-text"
                    >{{ $t('dashboard.stats_quota_label') }}</span>
                  </span>
                </div>
                <div class="stats-container col-xs-6 col-sm-6 col-md-6 col-lg-6">
                  <span
                    class="card-pf-utilization-card-details-count stats-count"
                  >{{ statistics.pseudonyms }}</span>
                  <span class="card-pf-utilization-card-details-description stats-description">
                    <span
                      class="card-pf-utilization-card-details-line-2 stats-text"
                    >{{ $t('dashboard.stats_aliases_label') }}</span>
                  </span>
                </div>
                <div class="stats-container col-xs-6 col-sm-6 col-md-6 col-lg-6">
                  <span
                    class="card-pf-utilization-card-details-count stats-count"
                  >{{ statistics.externals }}</span>
                  <span class="card-pf-utilization-card-details-description stats-description">
                    <span
                      class="card-pf-utilization-card-details-line-2 stats-text"
                    >{{ $t('dashboard.stats_forwarded_label') }}</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">
                <span class="icon-header-panel">
                  <span class="fa fa-send"></span>
                </span>
                {{ $t('dashboard.transfer_card_title') }}
              </h3>
            </div>
            <div class="panel-body">
              <div class="services-status">
                <span class="service-status">
                  <span
                    :class="'fa ' + faServiceStatusIcon(services['postfix'])"
                    :title="faServiceStatusLabel(services['postfix'])"
                    data-toggle="tooltip"
                    data-placement="top"
                  ></span>
                  <span class="service-name">{{ $t('dashboard.postfix_service_label') }}</span>
                </span>
                <span class="service-status">
                  <span
                    :class="'fa ' + faServiceStatusIcon(services['opendkim'])"
                    :title="faServiceStatusLabel(services['opendkim'])"
                    data-toggle="tooltip"
                    data-placement="top"
                  ></span>
                  <span class="service-name">{{ $t('dashboard.opendkim_service_label') }}</span>
                </span>
              </div>
              <div class="stats-container col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <span class="card-pf-utilization-card-details-count stats-count">{{ queue }}</span>
                <span class="card-pf-utilization-card-details-description stats-description">
                  <span
                    class="card-pf-utilization-card-details-line-2 stats-text"
                  >{{ $t('dashboard.transfer_queue_label') }}</span>
                </span>
              </div>
            </div>
            <div class="panel-footer">
              {{ configuration['SmartHostStatus'] == 'enabled' ? $t('dashboard.smarthost_configured_yes') : $t('dashboard.smarthost_configured_no') }}
              <a
                target="_blank"
                class="pull-right"
                href="/nethserver#/settings"
              >
                {{$t('send.default_smarthost')}}
                <span class="fa fa-external-link"></span>
              </a>
            </div>
          </div>
        </div>
      </div>

      <h3>{{ $t('dashboard.tcp_connections_title') }}</h3>
      <div v-if="activeConnections.length == 0" class="row row-stat">
        <div class="row-inline-block">
          <div
            class="stats-container col-xs-12 col-sm-12 col-md-12 col-lg-12"
          >{{ $t('dashboard.tcp_connections_none') }}</div>
        </div>
      </div>
      <div v-else class="row row-stat">
        <div class="row-inline-block">
          <div
            class="stats-container col-xs-12 col-sm-4 col-md-3 col-lg-2"
            v-for="conn in activeConnections"
            v-bind:key="conn.proto"
          >
            <span class="card-pf-utilization-card-details-count stats-count">{{ conn.count }}</span>
            <span class="card-pf-utilization-card-details-description stats-description">
              <span class="card-pf-utilization-card-details-line-2 stats-text">{{ conn.proto }}</span>
            </span>
          </div>
        </div>
      </div>

      <div class="divider"></div>

      <h3>
        {{ $t('dashboard.log_stats_title') }}
        <span
          :display="vReadLogStatus == 'success'"
          class="last-update-txt"
        >{{ $t('dashboard.last_update') }} : {{ logstats.time | dateFormat }}</span>
      </h3>
      <div v-if="vReadLogStatus == 'running'" class="spinner spinner-lg view-spinner"></div>

      <div :display="vReadLogStatus == 'success'" class="row">
        <div class="stats-container col-xs-12 col-sm-4 col-md-3 col-lg-2">
          <span
            class="card-pf-utilization-card-details-count stats-count"
          >{{ logstats.messages ? logstats.messages.delivered : '-' }}</span>
          <span class="card-pf-utilization-card-details-description stats-description">
            <span
              class="card-pf-utilization-card-details-line-2 stats-text"
            >{{ $t('dashboard.messages_delivered') }}</span>
          </span>
        </div>

        <div class="stats-container col-xs-12 col-sm-4 col-md-3 col-lg-2">
          <span
            class="card-pf-utilization-card-details-count stats-count"
          >{{ logstats.messages ? logstats.messages.bytes_delivered : 0 | byteFormat }}</span>
          <span class="card-pf-utilization-card-details-description stats-description">
            <span
              class="card-pf-utilization-card-details-line-2 stats-text"
            >{{ $t('dashboard.bytes_delivered') }}</span>
          </span>
        </div>

        <div class="stats-container col-xs-12 col-sm-4 col-md-3 col-lg-2">
          <span
            class="card-pf-utilization-card-details-count stats-count"
          >{{ logstats.messages ? logstats.messages.received : "-" }}</span>
          <span class="card-pf-utilization-card-details-description stats-description">
            <span
              class="card-pf-utilization-card-details-line-2 stats-text"
            >{{ $t('dashboard.messages_received') }}</span>
          </span>
        </div>

        <div class="stats-container col-xs-12 col-sm-4 col-md-3 col-lg-2">
          <span
            class="card-pf-utilization-card-details-count stats-count"
          >{{ logstats.messages ? logstats.messages.bytes_received : 0 | byteFormat }}</span>
          <span class="card-pf-utilization-card-details-description stats-description">
            <span
              class="card-pf-utilization-card-details-line-2 stats-text"
            >{{ $t('dashboard.bytes_received') }}</span>
          </span>
        </div>

        <div class="stats-container col-xs-12 col-sm-4 col-md-3 col-lg-2">
          <span
            class="card-pf-utilization-card-details-count stats-count"
          >{{ logstats.messages ? logstats.messages.forwarded : '-' }}</span>
          <span class="card-pf-utilization-card-details-description stats-description">
            <span
              class="card-pf-utilization-card-details-line-2 stats-text"
            >{{ $t('dashboard.messages_forwarded') }}</span>
          </span>
        </div>

        <div class="stats-container col-xs-12 col-sm-4 col-md-3 col-lg-2">
          <span
            class="card-pf-utilization-card-details-count stats-count"
          >{{ logstats.messages ? logstats.messages.bounced : '-' }}</span>
          <span class="card-pf-utilization-card-details-description stats-description">
            <span
              class="card-pf-utilization-card-details-line-2 stats-text"
            >{{ $t('dashboard.messages_bounced') }}</span>
          </span>
        </div>
      </div>

      <div :display="vReadLogStatus == 'success'" class="row mg-top-20">
        <div class="col-sm-5">
          <h4 class="col-sm-12">
            {{$t('dashboard.mail_per_hour')}}
            <div id="chart-per-hour-legend" class="legend"></div>
          </h4>
          <div id="chart-per-hour" class="col-sm-12"></div>
        </div>
        <div class="col-sm-5">
          <h4 class="col-sm-12">
            {{$t('dashboard.mail_per_day')}}
            <div id="chart-per-day-legend" class="legend"></div>
          </h4>
          <div id="chart-per-day" class="col-sm-12"></div>
        </div>
      </div>

      <h4 class="mg-top-20">{{ $t('dashboard.top_talkers_title') }}</h4>
      <div :display="vReadLogStatus == 'success'" class="row mg-top-20">
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">{{ $t('dashboard.top-recipients-size') }}</h3>
            </div>
            <div class="panel-body" v-for="item in logstats['recipients-size']" :key="item.address">
              <span
                class="card-pf-utilization-card-details-count stats-count-small col-xs-5"
              >{{ item.value | byteFormat}}</span>
              <span
                class="card-pf-utilization-card-details-description stats-description-small col-xs-6"
              >
                <span
                  class="card-pf-utilization-card-details-line-2 stats-text-small"
                >{{ item.address }}</span>
              </span>
            </div>
          </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">{{ $t('dashboard.top-recipients-count') }}</h3>
            </div>
            <div
              class="panel-body"
              v-for="item in logstats['recipients-count']"
              :key="item.address"
            >
              <span
                class="card-pf-utilization-card-details-count stats-count-small col-xs-5"
              >{{ item.value }}</span>
              <span
                class="card-pf-utilization-card-details-description stats-description-small col-xs-6"
              >
                <span
                  class="card-pf-utilization-card-details-line-2 stats-text-small"
                >{{ item.address }}</span>
              </span>
            </div>
          </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">{{ $t('dashboard.top-senders-size') }}</h3>
            </div>
            <div class="panel-body" v-for="item in logstats['senders-size']" :key="item.address">
              <span
                class="card-pf-utilization-card-details-count stats-count-small col-xs-5"
              >{{ item.value | byteFormat}}</span>
              <span
                class="card-pf-utilization-card-details-description stats-description-small col-xs-6"
              >
                <span
                  class="card-pf-utilization-card-details-line-2 stats-text-small"
                >{{ item.address }}</span>
              </span>
            </div>
          </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">{{ $t('dashboard.top-senders-count') }}</h3>
            </div>
            <div class="panel-body" v-for="item in logstats['senders-count']" :key="item.address">
              <span
                class="card-pf-utilization-card-details-count stats-count-small col-xs-5"
              >{{ item.value}}</span>
              <span
                class="card-pf-utilization-card-details-description stats-description-small col-xs-6"
              >
                <span
                  class="card-pf-utilization-card-details-line-2 stats-text-small"
                >{{ item.address }}</span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- success: end v-else  -->
  </div>
</template>

<script>
import execp from "@/execp";
import generatePieChart from "@/piechart";
import Dygraph from "dygraphs";

export default {
  name: "Dashboard",
  beforeRouteLeave(to, from, next) {
    $(".modal").modal("hide");
    next();
  },
  mounted() {
    execp("nethserver-mail/dashboard/read", { action: "live" })
      .then(result => {
        for (var k in result) {
          this[k] = result[k];
        }
        this.vReadStatus = "success";
        this.initCharts();
      })
      .catch(error => {
        this.vReadStatus = "error";
        this.vReadError = error;
      });
  },
  updated() {
    var $ = window.jQuery;
    $('[data-toggle="tooltip"]').tooltip();
    if (!this.rspamdPieChart) {
      this.rspamdPieChart = generatePieChart("#rspamd-pie-chart", {
        names: {
          reject: this.$t("dashboard.reject_rspamd_graph_label"),
          greylist: this.$t("dashboard.greylist_rspamd_graph_label"),
          probable: this.$t("dashboard.probable_rspamd_graph_label"),
          clean: this.$t("dashboard.clean_rspamd_graph_label")
        },
        colors: {
          reject: $.pfPaletteColors.red,
          greylist: $.pfPaletteColors.blue,
          probable: $.pfPaletteColors.orange,
          clean: $.pfPaletteColors.green
        },
        columns: []
      });
    }
    this.rspamdPieChart.load({
      columns: [
        ["reject", this.rspamd.reject || 0],
        ["greylist", this.rspamd.greylist || 0],
        ["probable", this.rspamd.probable || 0],
        ["clean", this.rspamd.clean || 0]
      ]
    });
  },
  data() {
    return {
      vReadStatus: "running",
      vReadLogStatus: "running",
      "clamav-update": 0,
      configuration: {},
      connections: {},
      domains: [],
      packages: {},
      queue: 0,
      quota: {},
      rspamd: {},
      services: {},
      statistics: {},
      item: "",
      logstats: {},
      charts: {},
      view: {
        isChartLoaded: false,
        invalidChartsData: false,
        chartsShowed: false
      },
      minLearns: 400
    };
  },
  computed: {
    installedComponents: function() {
      return Object.keys(this.packages)
        .filter(k => this.packages[k] > 0)
        .sort();
    },
    activeConnections: function() {
      var ret = [];
      for (let conn of Object.keys(this.connections).sort()) {
        ret.push({ proto: conn, count: parseInt(this.connections[conn]) });
      }
      return ret;
    }
  },
  methods: {
    initCharts() {
      var context = this;
      nethserver.exec(
        ["nethserver-mail/dashboard/read"],
        {
          action: "logs"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }

          context.logstats = success;

          if (
            success["hour-stats"].data.length > 0 ||
            success["day-stats"].data.length > 0
          ) {
            context.view.invalidChartsData = false;

            context.charts["chart-per-hour"] = new Dygraph(
              document.getElementById("chart-per-hour"),
              success["hour-stats"].data,
              {
                fillGraph: true,
                stackedGraph: true,
                labels: success["hour-stats"].labels,
                height: 150,
                strokeWidth: 1,
                strokeBorderWidth: 1,
                ylabel: context.$i18n.t("dashboard.mails"),
                axisLineColor: "white",
                labelsDiv: document.getElementById("chart-per-hour-legend"),
                labelsSeparateLines: true,
                legend: "always",
                axes: {
                  y: {
                    axisLabelFormatter: function(y) {
                      return Math.ceil(y);
                    }
                  },
                  x: {
                    axisLabelFormatter: function(x) {
                      return x + ":00";
                    }
                  }
                }
              }
            );
            context.charts["chart-per-hour"].initialData =
              success["hour-stats"].data;

            context.charts["chart-per-day"] = new Dygraph(
              document.getElementById("chart-per-day"),
              success["day-stats"].data,
              {
                fillGraph: true,
                stackedGraph: true,
                labels: success["day-stats"].labels,
                height: 150,
                strokeWidth: 1,
                strokeBorderWidth: 1,
                ylabel: context.$i18n.t("dashboard.mails"),
                axisLineColor: "white",
                labelsDiv: document.getElementById("chart-per-day-legend"),
                labelsSeparateLines: true,
                legend: "always",
                axes: {
                  y: {
                    axisLabelFormatter: function(y) {
                      return Math.ceil(y);
                    }
                  },
                  x: {
                    axisLabelFormatter: function(x) {
                      var d = new Date(x * 1000);
                      return d.toDateString();
                    }
                  }
                }
              }
            );
            context.charts["chart-per-day"].initialData =
              success["day-stats"].data;

            context.vReadLogStatus = "success";
            context.view.isChartLoaded = true;
          } else {
            context.view.invalidChartsData = true;
            context.view.isChartLoaded = true;
            context.$forceUpdate();
          }
        },
        function(error) {
          console.error(error);
          context.view.isChartLoaded = true;
        }
      );
    },
    faServiceStatusIcon: function(status) {
      return status ? "fa-check green" : "fa-times red";
    },
    faServiceStatusLabel: function(status) {
      return status
        ? this.$t("dashboard.service_status_running_tooltip")
        : this.$t("dashboard.service_status_stopped_tooltip");
    }
  }
};
</script>

<style>
.empty-piechart {
  margin-top: 2em;
  text-align: center;
  color: #9c9c9c;
}

.empty-piechart .fa {
  font-size: 92px;
  color: #bbbbbb;
}

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

.mg-top-20 {
  margin-top: 20px;
}

.stats-count-small {
  margin-right: 5px;
  font-size: 18px;
  font-weight: 300;
  float: left;
  line-height: 0.5;
}

.stats-text-small {
  line-height: 0.5;
}

.legend {
  z-index: 5;
}

.stats-description-small {
  float: left;
  overflow: hidden;
  text-overflow: ellipsis;
}

.filter-icon {
  margin-right: 10px;
  font-size: 20px;
}

.last-update-txt {
  font-size: 70%;
  float: right;
  color: grey;
}

.align-domains {
  line-height: 25px;
}
</style>
