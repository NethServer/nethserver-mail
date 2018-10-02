#!/bin/bash

#
# Copyright (C) 2018 Nethesis S.r.l.
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

JUNKFOLDER=$(/sbin/e-smith/config getprop dovecot SpamFolder)

if [[ -z "${JUNKFOLDER}" ]]; then
    echo "[WARNING] junk folder is not defined."
    exit 0
fi

trap "echo '[WARNING] Interrupt!'; exit 0" INT

for USER in /var/lib/nethserver/vmail/*; do
  USER=$(basename $USER)

  echo "Loading $USER.."
 
  doveadm search -u $USER mailbox "${JUNKFOLDER}" ALL | while read guid uid; do
     doveadm -f pager fetch -u $USER text mailbox-guid $guid uid $uid \
         | sed '1d;$d' \
         | rspamc -t 60 -h localhost:11334 learn_spam
  done

done
