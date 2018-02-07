nethserver-mail
===============

Mail system implementation based on Postfix, Dovecot and Rspamd. The mail system
is shipped by three main RPM packages:

- nethserver-mail-common
- nethserver-mail-filter
- nethserver-mail-server

mail-common
-----------

* Common infrastructure for ``nethserver-mail-server and nethserver-mail-filter``, Postfix-based.
* Relay
* Queue parameters: age + message size
* MX record configuration
* Disclaimer (based on Amavis)

mail-filter
-----------

* Based on `Rspamd`_
* Anti-spam with DNSBL (see: `nethserver-unbound`_)
* Anti-virus
* Attachment block
* Real-time Blackhole List (RBL) (default disabled)
* Sender Policy Framework (SPF) (default disabled)
* Customized spam threshold 
* Sender WBL, Recipient whitelist 

.. _Rspamd: https://rspamd.com
.. _nethserver-unbound: http://github.com/NethServer/nethserver-unbound

mail-server
-----------

* IMAP/POP3 mailbox access protocols
* STARTTLS enabled by default
* Mail quota
* Sieve filters
* Postfix/dovecot-lda integration
* Multi-domain
* Domain-specific configuration
* Pseudonyms 
* SMTP authentication
* Active Directory integration
* SpamAssassin's Bayesian classifier training (``spamtrainers`` group)
* Spam retention time


Configuration database
----------------------

Postfix example: ::

 postfix=service
    ...
    AccessPolicies=
    AlwaysBccStatus=disabled
    AlwaysBccAddress=
    MessageQueueLifetime=4
    MessageSizeMax=20000000
    MessageSizeMin=1048576
    MxRecordStatus=enabled
    ContentInspectionType=default
    ConnectionsLimit=
    ConnectionsLimitPerIp=
    MxRecordStatus=enabled
    SystemUserRecipientStatus=disabled

* ``AccessPolicies``: A comma separated list of values. Obsoletes
  ``SubmissionPolicyType`` prop.  Currently defined values are
  ``smtpauth`` and ``trustednetworks``.

* *smtpauth* enable SMTP/AUTH on port 25, otherwise it is enabled
  only on 587 and 465 submission ports

* *trustednetworks* allow relay from any host in trusted networks
  (green network included).

* ``AlwaysBccStatus {enabled,disabled}``: if ``enabled`` any message
  entering Postifx mail system is copied to the given ``AlwaysBccAddress``.

* ``AlwaysBccAddress``: an email address that always receives a
  message copy (controlled by ``AlwaysBccStatus``).

* ``MxRecordStatus {enabled,disabled}`` Push smtp, imap, pop, pop3 into /etc/hosts.
  They masquerade DNS A records in the LAN.

* ``SystemUserRecipientStatus {enabled,disabled}`` ``enabled``,
  accept from any network the recipient addresses formed by user
  account names and domain part ``localhost``,
  ``localhost.<domainname>`` and FQDN hostname.

Dovecot example: ::

    dovecot=service
        ...
        AdminIsMaster=disabled
        DeletedToTrash=disabled
        FtsLuceneStatus=enabled
        ImapStatus=enabled
        LmtpInetListenerStatus=disabled
        LogActions=disabled
        MaxProcesses=400
        MaxUserConnectionsPerIp=12
        PopStatus=enabled
        QuotaDefaultSize=20
        QuotaStatus=disabled
        SharedMailboxesStatus=enabled
        SpamFolder=Junk
        SpamRetentionTime=15d
        TlsSecurity=required
        RestrictedAccessGroup=


Properties:

* ``AdminIsMaster {enabled,disabled}`` allow root user to impersonate other users
* ``DeletedToTrash {enabled,disabled}`` deletedtotrash plugin
* ``FtsLuceneStatus {enabled,disabled}`` lucene indexed search plugin
* ``ImapStatus {enabled,disabled}`` IMAP protocol switch
* ``LmtpInetListenerStatus {enabled,disabled}`` open a TCP socket for LMTP protocol
* ``LogActions {enabled,disabled}`` IMAP actions logging plugin
* ``MaxProcesses N`` maximum number of worker processes spawned by dovecot. A single user session usually requires multiple processes.
* ``MaxUserConnectionsPerIp N`` maximum TCP connections for one user behind the same IP
* ``PopStatus {enabled,disabled}`` POP3 protocol switch
* ``QuotaDefaultSize N`` Default user quota size (1 unit is 10MB)
* ``QuotaStatus {enabled,disabled}`` General user mail quota switch
* ``SharedMailboxesStatus {disabled,enabled}`` Control the "Shared" IMAP namespace for per-user folder sharing
* ``SpamFolder FolderName`` Deliver spam tagged messages to the given folder (applied to all users)
* ``SpamRetentionTime Nd`` Expunge messages in SpamFolder if older than the given time span. "d" is for days.
* ``TlsSecurity {optional,required}`` 
  controls dovecot ``disable_plaintext_auth`` parameter: if set to ``required`` clear-text authentication methods are disabled, while ``optional`` enables them.
* ``RestrictedAccessGroup`` The value is a long group name, like ``domain
  admins@mydomain.tld``. Members of the given group can login to dovecot
  services only from trusted networks. Install the
  ``nethserver-mail-server-ipaccess`` package to enable this feature.


Domains database
----------------

Record of type `domain`: :: 

  internal.tld=domain
    ...
    TransportType=none

  mycompany.com=domain
    ...
    TransportType=Relay
    RelayHost=10.1.1.4
    RelayPort=25
    DisclaimerStatus=disabled

  test.tld=domain
    ...
    TransportType=SmtpSink

  example.com=domain
    ...
    TransportType=LocalDelivery
    UnknownRecipientsActionType=deliver
    UnknownRecipientsActionDeliverMailbox=jdoe
    AlwaysBccStatus=enabled
    AlwaysBccAddress=admin``there.org

  other.net=domain
    ...
    TransportType=Relay
    RelayHost=mail.other.net
    RelayPort=25
  
Accounts database
-----------------

Groups: ::

  employees=group
     ...
     MailStatus=enabled
     MailDeliveryType=shared

  administrators=group
     ...
     MailStatus=enabled
     MailDeliveryType=copy

  faxservice=group
     ...
     MailStatus=disabled
     MailDeliveryType={any}

User: ::

  jdoe=user
     FirstName=John
     LastName=Doe
     ...
     MailStatus=enabled
     MailQuotaType=custom
     MailQuotaCustom=15
     MailForwardStatus=disabled
     MailForwardAddress=
     MailForwardKeepMessageCopy=no

  and his pseudonyms: ::

   john.doe``example.com=pseudonym
     Account=jdoe
     ControlledBy=system
     Access=public

   doe``=pseudonym
     Account=jdoe
     ControlledBy=operators
     Access=private

Testing Postfix
---------------

Install **nethserver-mail-dev** package: ::

  yum install nethserver-mail-dev 

Create or modify an existing domain record. Then set ``TransportType`` prop to ``SmtpSink``: ::

  db domains setprop test.tld TransportType SmtpSink
  signal-event domain-modify test.tld


Start the ``smtp-sink`` server: ::

  /usr/sbin/smtp-sink -L -c -u postfix unix:/var/spool/postfix/smtp-sink 128


Execute smtptest command (see command help for details): ::

  /sbin/e-smith/smtptest --from sender``example.com --to rcpt1``test.it --addr 10.1.1.4 --ehlo testhelo.test.it --subject 'Test message' 


Execute "smtp-source":http://linux.die.net/man/1/smtp-source command (from postfix package): ::

  smtp-source -c -l 32000 -m 50 -N -f sender``yourdomain.tld -t test``test.it -S TEST-SMTP-SOURCE -s 5 <HOST-IP-ADDRESS>


RBL server list
---------------

Enable RBL checks, by adding *zen.spamhaus.org* to the RBL server list: ::

    db configuration setprop postfix RblStatus enabled RblServers zen.spamhaus.org
    signal-event nethserver-mail-filter-save


Mail quota
----------

The default mail quota is configured in ``dovecot.conf``. Custom user mail quota
is set by the ``dovecot-postlogin`` script, by reading
``/etc/dovecot/user-quota`` (which is a template). If a custom mail quota is set
the UI interface does not show the updated value until the user performs an IMAP
login.

Disabled users
--------------

By default all system users are also Dovecot users. To disable a user we
configure a blacklist in ``dovecot.conf``: ``/etc/dovecot/deny.passwd``.

As Dovecot is configured as authentication backend for Postfix, a disabled user
loses also SMTP AUTH access.


Testing Dovecot with Mutt
-------------------------

Read admin's mail with Mutt IMAP client.
Quickstart: ::

  yum install mutt
  cat - <<EOF > ~/.muttrc 
  set spoolfile="imaps://root@localhost/"
  set folder=""
  EOF
  mutt

See: http://dev.mutt.org/doc/manual.html

When mutt starts always asks for the ``root`` password.
To avoid typing the password again and again write it in ``.muttrc``: ::

  set spoolfile="imaps://root:PASSWORD@localhost/"
  set folder=""

``PASSWORD`` must be URL-encoded. For instance the slash character ``/`` is encoded as ``%2f``.

Set special ACL on mailboxes
----------------------------

The ``nethserver-mail-shrmbx-modify`` action applies some predefined ACL 
settings to shared mailboxes (type the mailbox name twice: the action performs also rename): ::

   /etc/e-smith/events/actions/nethserver-mail-shrmbx-modify EVENT OLDNAME NEWNAME ID PERM [ID PERM ...]

For instance, let's grant full "admin" permissions to group "administrators": ::

   /etc/e-smith/events/actions/nethserver-mail-shrmbx-modify ev 'Public folder1' 'Public Folder One' group=administrators@$(hostname -d) ADMIN

You can also use ``doveadm`` to set special ACL on a shared mailbox: ::

  doveadm acl set -u <user> <shared_mailbox> <subject> <flags>

Example: allow insert and expunge to user goofy on public mailbox testshare (domain of the machine is local.nethserver.org): ::

  doveadm acl set -u goofy@local.nethserver.org Public/testshare "user=goofy@local.nethserver.org" insert expunge

The /etc/dovecot/ipaccess.conf file
-----------------------------------

The ``dovecot-postlogin`` script enforces an IP-based access policy to dovecot
services when the file ``/etc/dovecot/ipaccess.conf`` exists and is readable.

The file is composed by comments and records. Comments are line starting with ``#``,
whilst records have the following syntax: ::

    <long group name> = <cidr list>

A *long group name* is the group name with domain suffix, like ``domain
admins@mydomain.tld``.

The *cidr list* is a comma-separated list of IP and network addresses in CIDR
format, like ``127.0.0.1, 192.168.1.0/24, 10.1.1.2``. The binary conversion is
implemented by the ``NetAddr::IP`` Perl module. See ``perldoc NetAddr::IP`` for
details.

IP-based IMAP access restriction
--------------------------------

This feature allows to restrict IMAP access for a specific group.
Members of the given group have IMAP access restricted to trusted networks.

1. Install ``nethserver-mail-server-ipaccess`` package ::

     yum install nethserver-mail-server-ipaccess

2. Set the limited group, remember to use the full group name: ``<group>@<domain>`` ::

     config setprop dovecot RestrictedAccessGroup <group>@<domain>

   Example for group ``collaborators@nethserver.org``: ::

     config setprop dovecot RestrictedAccessGroup collaborators@nethserver.org

3. Apply the configuration ::

     signal-event nethserver-mail-server-save
