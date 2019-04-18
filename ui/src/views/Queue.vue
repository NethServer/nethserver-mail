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
    <h1>{{ $t('queue.title') }}</h1>

    <h3 v-if="view.isChartLoaded && mails">{{$t('charts')}}</h3>
    <a
      v-if="view.isChartLoaded && mails"
      @click="toggleCharts()"
    >{{view.chartsShowed ? $t('hide_charts') : $t('show_charts')}}</a>
    <div v-if="!view.isChartLoaded && mails" class="spinner spinner-lg view-spinner"></div>
    <div :class="view.chartsShowed ? '' : 'hidden'">
      <div
        v-if="view.invalidChartsData && mails"
        class="alert alert-warning alert-dismissable col-sm-12"
      >
        <span class="pficon pficon-warning-triangle-o"></span>
        <strong>{{$t('warning')}}!</strong>
        {{$t('charts_not_updated')}}.
      </div>
      <div v-show="mails && view.isChartLoaded && !view.invalidChartsData" class="row">
        <div class="col-sm-5">
          <h4 class="col-sm-12">
            {{$t('queue.mail_in_queue')}}
            <div id="chart-status-mails" class="legend"></div>
          </h4>
          <div id="chart-mails" class="col-sm-12"></div>
        </div>
        <div class="col-sm-5">
          <h4 class="col-sm-12">
            {{$t('queue.mail_size')}}
            <div id="chart-status-size" class="legend"></div>
          </h4>
          <div id="chart-size" class="col-sm-12"></div>
        </div>
      </div>
    </div>

    <div v-if="!view.isLoaded" class="spinner spinner-lg view-spinner"></div>

    <h3 v-if="view.isLoaded && !isEmpty(mails)">{{$t('actions')}}</h3>
    <form v-if=" view.isLoaded && !isEmpty(mails)" role="form" class="search-pf has-button search">
      <div class="form-group">
        <button
          class="btn btn-primary btn-lg margin-left-md"
          type="button"
          v-on:click="doAction(null, 'send-all', 'update')"
        >{{$t('queue.resend_all')}}</button>
        <button
          class="btn btn-danger btn-lg margin-left-md"
          type="button"
          data-toggle="modal"
          data-target="#deleteAllMailModal"
        >{{$t('queue.delete_all')}}</button>
      </div>
    </form>

    <h3 v-if="view.isLoaded && !isEmpty(mails)">{{$t('list')}}</h3>
    <div v-if="view.isLoaded && isEmpty(mails)" class="blank-slate-pf" id>
      <div class="blank-slate-pf-icon">
        <span class="fa fa-envelope"></span>
      </div>
      <h1>{{$t('queue.no_mails_found')}}</h1>
      <p>{{$t('queue.your_queue_empty')}}.</p>
    </div>

    <form v-if="view.isLoaded && !isEmpty(mails)" role="form" class="search-pf has-button search">
      <div class="form-group has-clear">
        <div class="search-pf-input-group">
          <label class="sr-only">Search</label>
          <input
            v-focus
            type="search"
            v-model="searchString"
            class="form-control input-lg"
            :placeholder="$t('search')+'...'"
            id="pf-search-list"
          >
        </div>
      </div>
    </form>

    <div v-if="view.isLoaded" class="list-group list-view-pf list-view-pf-view no-mg-top mg-top-10">
      <div v-for="(m, mk) in filteredMails" v-bind:key="mk" class="list-group-item">
        <div class="list-view-pf-actions">
          <button
            class="btn btn-default"
            data-toggle="modal"
            data-target="#modalEditDomain"
            v-on:click="doAction(m.id, 'send', 'update')"
          >
            <span class="fa fa-paper-plane span-right-margin"></span>
            {{ $t('queue.resend')}}
          </button>
          <div class="dropup pull-right dropdown-kebab-pf">
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
              <li @click="openDeleteMail(m)">
                <a>
                  <span class="fa fa-times span-right-margin"></span>
                  {{$t('delete')}}
                </a>
              </li>
            </ul>
          </div>
        </div>

        <div class="list-view-pf-main-info small-list">
          <div class="list-view-pf-left">
            <span :class="['fa', 'list-view-pf-icon-sm', 'fa-inbox']"></span>
            <br>
            {{m.status | uppercase}}
          </div>
          <div class="list-view-pf-body">
            <div class="list-view-pf-description">
              <div class="list-group-item-heading">
                {{m.id}}
                <br>
                {{m.rawdate}}
              </div>
              <div class="list-group-item-text">{{m.reason}}</div>
            </div>
            <div class="list-view-pf-additional-info rules-info">
              <div
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                :title="'Prova'"
                class="list-view-pf-additional-info-item"
              >
                <strong>{{m.sender}}</strong>
              </div>
              <div
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                :title="'Prova'"
                class="list-view-pf-additional-info-item"
              >
                <span class="fa fa-arrow-right"></span>
                <strong>{{m.recipient}}</strong>
              </div>
              <div class="list-view-pf-additional-info-item">
                <span class="fa fa-database"></span>
                {{ m.size | byteFormat }}
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- end item -->
    </div>

    <div class="modal" id="deleteMailModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{$t('queue.delete_mail')}} {{currentMail.id}}</h4>
          </div>
          <form
            class="form-horizontal"
            v-on:submit.prevent="doAction(currentMail.id, 'delete', 'delete')"
          >
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
    <div class="modal" id="deleteAllMailModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{{$t('queue.delete_all_mail')}}</h4>
          </div>
          <form
            class="form-horizontal"
            v-on:submit.prevent="doAction(null, 'delete-all', 'delete')"
          >
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
import Dygraph from "dygraphs";

export default {
  name: "Queue",
  beforeRouteLeave(to, from, next) {
    $(".modal").modal("hide");
    clearInterval(this.pollingIntervalIdChart);
    next();
  },
  mounted() {
    this.getAll();
    this.initCharts();
  },
  data() {
    return {
      view: {
        isLoaded: false,
        isChartLoaded: false,
        invalidChartsData: false,
        chartsShowed: false
      },
      mails: null,
      currentMail: {},
      charts: {
        mails: null
      },
      searchString: "",
      pollingIntervalIdChart: 0
    };
  },
  computed: {
    filteredMails() {
      var mailsArr = [];
      for (var m in this.mails) {
        var mail = this.mails[m];
        mail.id = m;
        mailsArr.push(mail);
      }

      var returnObj = [];
      for (var r in mailsArr) {
        var rule = JSON.stringify(mailsArr[r]);
        if (rule.toLowerCase().includes(this.searchString.toLowerCase())) {
          returnObj.push(mailsArr[r]);
        }
      }

      return returnObj;
    }
  },
  methods: {
    isEmpty(obj) {
      return jQuery.isEmptyObject(obj);
    },
    toggleCharts() {
      this.view.chartsShowed = !this.view.chartsShowed;
      if (this.view.chartsShowed) {
        this.initCharts();
      }
    },
    initCharts() {
      var context = this;
      nethserver.exec(
        ["nethserver-mail/queue/read"],
        {
          action: "stats",
          time: 900
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }

          if (success.mails.data.length > 0 && success.size.data.length > 0) {
            context.view.invalidChartsData = false;

            for (var t in success.mails.data) {
              success.mails.data[t][0] = new Date(
                success.mails.data[t][0] * 1000
              );
            }
            for (var t in success.size.data) {
              success.size.data[t][0] = new Date(
                success.size.data[t][0] * 1000
              );
            }

            context.charts["chart-mails"] = new Dygraph(
              document.getElementById("chart-mails"),
              success.mails.data.reverse(),
              {
                fillGraph: true,
                stackedGraph: true,
                labels: success.mails.labels,
                height: 150,
                strokeWidth: 1,
                strokeBorderWidth: 1,
                ylabel: context.$i18n.t("queue.total"),
                axisLineColor: "white",
                labelsDiv: document.getElementById("chart-status-mails"),
                labelsSeparateLines: true,
                legend: "always",
                axes: {
                  y: {
                    axisLabelFormatter: function(y) {
                      return Math.ceil(y);
                    }
                  }
                }
              }
            );
            context.charts["chart-mails"].initialData = success.mails.data;

            context.charts["chart-size"] = new Dygraph(
              document.getElementById("chart-size"),
              success.size.data.reverse(),
              {
                fillGraph: true,
                stackedGraph: true,
                labels: success.size.labels,
                height: 150,
                strokeWidth: 1,
                strokeBorderWidth: 1,
                ylabel: context.$i18n.t("queue.size_kb"),
                axisLineColor: "white",
                labelsDiv: document.getElementById("chart-status-size"),
                labelsSeparateLines: true,
                legend: "always",
                axes: {
                  y: {
                    axisLabelFormatter: function(y) {
                      return Math.ceil(y);
                    }
                  }
                }
              }
            );
            context.charts["chart-size"].initialData = success.size.data;

            context.view.isChartLoaded = true;

            // start polling
            if (context.pollingIntervalIdChart == 0) {
              context.pollingIntervalIdChart = setInterval(function() {
                context.updateCharts(900);
              }, 10000);
            }
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
    updateCharts(time) {
      var context = this;
      nethserver.exec(
        ["nethserver-mail/queue/read"],
        {
          action: "stats",
          time: time
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          if (success.mails.data.length > 0 && success.size.data.length > 0) {
            context.view.invalidChartsData = false;

            for (var t in success.mails.data) {
              success.mails.data[t][0] = new Date(
                success.mails.data[t][0] * 1000
              );
            }
            for (var t in success.size.data) {
              success.size.data[t][0] = new Date(
                success.size.data[t][0] * 1000
              );
            }

            context.charts["chart-mails"].updateOptions({
              file: success.mails.data.reverse()
            });
            context.charts["chart-size"].updateOptions({
              file: success.size.data.reverse()
            });
          } else {
            context.view.invalidChartsData = true;
            context.$forceUpdate();
          }
        },
        function(error) {
          console.error(error);
        }
      );
    },
    getAll() {
      var context = this;

      context.view.isLoaded = false;
      nethserver.exec(
        ["nethserver-mail/queue/read"],
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

          context.mails = success;
          context.view.isLoaded = true;
        },
        function(error) {
          console.error(error);
        }
      );
    },
    openDeleteMail(mail) {
      this.currentMail = Object.assign({}, mail);
      $("#deleteMailModal").modal("show");
    },
    doAction(id, action, api) {
      var context = this;

      // notification
      nethserver.notifications.success = context.$i18n.t(
        "queue.queue_updated_ok"
      );
      nethserver.notifications.error = context.$i18n.t(
        "queue.queue_updated_error"
      );

      $("#deleteMailModal").modal("hide");
      $("#deleteAllMailModal").modal("hide");
      nethserver.exec(
        ["nethserver-mail/queue/" + api],
        {
          action: action,
          name: id
        },
        function(stream) {
          console.info("address", stream);
        },
        function(success) {
          // get queue
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
