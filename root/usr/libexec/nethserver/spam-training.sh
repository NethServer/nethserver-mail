#!/bin/bash 

#
# NethServer -- spam-training.sh 
#
# Read a mail message from standard input and pass it to sa-learn.
# This script is executed by dovecot as vmail user.
#

#
# Copyright (C) 2012 Nethesis S.r.l.
# http://www.nethesis.it - support@nethesis.it
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
# along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
#


# Close STDOUT descriptor
exec 1>&-

PROG=`basename $0`
USER=$1
ACTION=$2

function log {
    local level=$1
    shift
    [ -x /usr/bin/logger ] && /usr/bin/logger -i -t "${PROG}/${USER}" -p "mail.${level}" $*;    
}

if ! [ -x /usr/bin/rspamc ] || ! getent passwd _rspamd &>/dev/null; then
    # Nothing to do if rspamd is not installed
    exit 0
fi

# If defined spamtrainers user group, require that the current user is
# a member of it before going on.
sa_learn_group=`/usr/bin/getent group spamtrainers`
if [ $? -eq 0 ] && ! echo $sa_learn_group | \
    /bin/cut -d : -f 4 | \
    /bin/grep -q "\<${USER}\>"; then
    log debug "Not a member of 'spamtrainers' group. Nothing to do."
    exit 0;
fi

if ! [ $ACTION == 'learn_ham' ] && ! [ $ACTION == 'learn_spam' ] ; then
    log err "Action '${ACTION}' is not recognized" 
    exit 3
fi

/usr/sbin/sendmail -F 'spam-training.sh script' -r root@`hostname` ${USER}+${ACTION}@spamtrain.nh && log info "Message enqueued as ${ACTION}"

