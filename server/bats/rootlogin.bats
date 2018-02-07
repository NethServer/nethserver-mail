#
# prerequisite: install bats from EPEL; on NethServer 7
#
#    yum install bats expect
# 

shopt -s nullglob

setup ()
{
    load setup_global
    pushd /var/lib/nethserver/vmail
    for MDIR in root*; do
        mv -v $MDIR $$.$MDIR
    done
    popd
}

teardown ()
{
    pushd /var/lib/nethserver/vmail
    for MDIR in $$.root*; do
        if [[ -d ${MDIR##$$.} ]]; then
            mkdir -p bats.backup
            mv ${MDIR##$$.} bats.backup/${MDIR}
        fi
        mv -v $MDIR ${MDIR##$$.}
    done
    popd
}

@test "Login as root short username must succeed and create short Maildir" {
    run expect login.exp root $ROOTPW
    [[ $status == 0 ]]
    [[ -d /var/lib/nethserver/vmail/root ]]
    [[ ! -d /var/lib/nethserver/vmail/root@${DOMAIN} ]]
}

@test "Login as root long username must fail without creating any Maildir" {
    run expect login.exp root@${DOMAIN} $ROOTPW
    [[ $status != 0 ]]
    [[ ! -d /var/lib/nethserver/vmail/root ]]
    [[ ! -d /var/lib/nethserver/vmail/root@${DOMAIN} ]]
}