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
    <div v-bind:id="id" class="modal fade" tabindex="-1" role="dialog" v-bind:aria-labelledby="id + 'Label'" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" aria-label="Close">
                        <span class="pficon pficon-close"></span>
                    </button>
                    <h4 class="modal-title" v-bind:id="id + 'Label'">
                        <span v-if="useCase == 'delete'"      >{{ $t('domain.delete_title', this.domain) }}</span>
                        <span v-else-if="useCase == 'create'" >{{ $t('domain.create_title', this.domain) }}</span>
                        <span v-else                          >{{ $t('domain.edit_title', this.domain) }}</span>
                    </h4>
                </div>

                <div v-if="useCase == 'delete'" class="modal-body">
                    <form class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="textInput">Field One</label>
                            <div class="col-sm-9">
                                <input type="text" id="textInput" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>

                <div v-else-if="useCase == 'create'" class="modal-body">
                    <form class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="textInput">Field One</label>
                            <div class="col-sm-9">
                                <input type="text" id="textInput" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="textInput2">Field Two</label>
                            <div class="col-sm-9">
                                <input type="text" id="textInput2" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="textInput3">Field Three</label>
                            <div class="col-sm-9">
                                <input type="text" id="textInput3" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>

                <div v-else class="modal-body">
                    <form class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="textInput">Field One</label>
                            <div class="col-sm-9">
                                <input type="text" id="textInput" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="textInput2">Field Two</label>
                            <div class="col-sm-9">
                                <input type="text" id="textInput2" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="textInput3">Field Three</label>
                            <div class="col-sm-9">
                                <input type="text" id="textInput3" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ $t('modal.cancel_button') }}</button>
                    <button v-if="useCase == 'delete'"       type="button" class="btn btn-primary">{{ $t('modal.delete_button') }}</button>
                    <button v-else-if="useCase == 'create'"  type="button" class="btn btn-primary">{{ $t('modal.create_button') }}</button>
                    <button v-else                           type="button" class="btn btn-primary">{{ $t('modal.apply_button') }}</button>
                </div>
            </div>
        </div>
    </div>

</template>

<script>

export default {
    name: "DomainEdit",
    props: {
        'id': String,
        'useCase': String,
        'domain': {
            type: Object,
            default: function() {
                return {
                  "OpenDkimStatus": "disabled",
                  "DisclaimerStatus": "disabled",
                  "TransportType": "Relay",
                  "name": "",
                  "Description": ""
                }
            }
        },
    },
}
</script>
