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
    <h2>{{$t('logs.title')}}</h2>
    <h3>
      <pre id="log-file" class="monospace m-right-sm">{{!view.follow ? 'tail -'+view.lines+' /var/log/firewall.log' : 'tail -f /var/log/firewall.log'}}</pre>
      <button
        @click="handleLogs()"
        class="btn btn-primary"
      >{{view.follow ? $t('logs.stop_follow') : $t('logs.follow')}}</button>
    </h3>
    <div v-if="!view.logsLoaded" id="loader" class="spinner spinner-lg view-spinner"></div>
    <pre id="logs-output" v-if="view.logsLoaded" class="logs">{{view.logsContent}}</pre>
  </div>
</template>

<script>
export default {
  name: "Logs",
  mounted() {
    this.getLogs();
  },
  data() {
    return {
      view: {
        logsLoaded: false,
        logsContent: "",
        follow: false,
        lines: 50
      }
    };
  },
  methods: {
    handleLogs() {
      this.view.follow = !this.view.follow;
      this.view.logsContent = "";
      this.$forceUpdate();
      this.getLogs();
    },
    getLogs() {
      var context = this;
      nethserver.readLogs(
        {
          action: this.view.follow ? "follow" : "dump",
          lines: this.view.follow ? null : this.view.lines,
          mode: "file",
          paths: ["/var/log/firewall.log"]
        },
        this.view.follow
          ? function(stream) {
              context.view.logsLoaded = true;

              context.view.logsContent += stream;

              document.getElementById(
                "logs-output"
              ).scrollTop = document.getElementById("logs-output").scrollHeight;
            }
          : null,
        function(success) {
          context.view.logsLoaded = true;

          if (success.length == 0) {
            context.view.logsContent = context.i18n.t(
              "logs.process_terminated"
            );
          } else {
            context.view.logsContent = success;
          }

          setTimeout(function() {
            document.getElementById(
              "logs-output"
            ).scrollTop = document.getElementById("logs-output").scrollHeight;
          }, 100);

          context.$parent.getFirewallStatus();
        },
        function(error) {
          context.view.logsLoaded = true;
          context.logsContent = error;
        },
        false
      );
    }
  }
};
</script>

<style>
.monospace {
  display: inline;
  padding: 5px;
  font-size: 12px;
}

.logs {
  max-height: 500px;
}
</style>
