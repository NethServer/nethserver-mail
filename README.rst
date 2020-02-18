===============
nethserver-mail
===============

Mail system implementation based on Postfix, Dovecot, Rspamd, OpenDKIM. The mail
system configuration is splitted into many RPMs, described in the following sections.

.. contents::
    :local:

nethserver-mail-common
----------------------

* Common infrastructure for ``nethserver-mail-server and nethserver-mail-filter``, Postfix-based.
* Relay
* Queue parameters: age + message size
* MX record configuration


nethserver-mail-smarthost
-------------------------

* Send mail through the given MTA (smarthost), with SMTP/AUTH
* StartTLS encryption
* Set sender address for mail sent from root user (see `Notifications` section under nethserver-base package README)

nethserver-mail-disclaimer
--------------------------

* Attach disclaimer/legal notice to outbound messages for certain domains
* Runs ``altermime`` with Postfix ``content_filter`` option

nethserver-mail-filter
----------------------

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

nethserver-mail-server
----------------------

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
* Sender address restriction based on login name
* Dynamic group members address list expansion

nethserver-mail-ipaccess
------------------------

IMAP access for a specific group of users. See `IP-based IMAP access restriction`_.


nethserver-mail-getmail
-----------------------

The package configures getmail using cron.

For each enabled account the system:

* generates a ``.cfg`` file inside the ``/var/lib/getmail`` directory from the template ``/etc/e-smith/templates/getmailrc``
* adds a line inside the ``/etc/cron.d/getmail``, all getmail instances use a non-blocking flock
* delivers the messages using dovecot-lda

All operations are logged in ``/var/log/maillog``. 

If a virus is found inside a received mail, the message is dropped.

The evidence  of log in ``/var/log/maillog``: ::

  Feb 14 18:19:10 vm5 clamd[1791]: instream(local): Eicar-Test-Signature FOUND


nethserver-mail-p3scan
----------------------

This package configures p3scan, full-transparent POP3 proxy-server for email
clients.

* POP3 and POP3s proxy
* Anti-virus and anti-spam checks

mail-quarantine
---------------

This package makes a quarantine for spam. They are sent to a mailbox (you need to manually created it), waiting a review of the sysadmin. If enabled a mail notification is sent to the postmaster (root alias) for each quarantined email.

Database format
---------------

configuration
^^^^^^^^^^^^^

Postfix example: ::

 postfix=service
    ...
    AccessPolicies=
    AlwaysBccStatus=disabled
    AlwaysBccAddress=
    MessageQueueLifetime=4
    MessageSizeMax=20000000
    ConnectionsLimit=
    ConnectionsLimitPerIp=
    SystemUserRecipientStatus=disabled
    ...
    SenderValidation=disabled
    DynamicGroupAlias=disabled
    HeloHost=
    SmartHostAuth=disabled
    SmartHostAuthStatus=disabled
    SmartHostName=192.168.5.252
    SmartHostPassword=password
    SmartHostPort=25
    SmartHostStatus=disabled
    SmartHostTlsStatus=enabled
    SmartHostUsername=ns1

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

* ``SystemUserRecipientStatus {enabled,disabled}`` ``enabled``,
  accept from any network the recipient addresses formed by user
  account names and domain part ``localhost``,
  ``localhost.<domainname>`` and FQDN hostname.

* ``SenderValidation {enabled,disabled}``, default ``disabled``,
  checks the SMTP sender is consistent with /etc/login_maps 
  and /etc/login_maps.pcre contents.

* ``DynamicGroupAlias {enabled,disabled}``, default ``disabled``,
   if ``enabled``, create distribution lists based on system groups.
   See also the "Dynamic group aliases" section below.

* ``HeloHost``. FQDN hostname used by Postfix when connecting to other MTAs

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
        SharedSeen=disabled
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
* ``SharedSeen {disabled,enabled}`` Control the \Seen IMAP flag (enabled means all users will see an email as read as soon as the first user reads it)
* ``SpamFolder FolderName`` Deliver spam tagged messages to the given folder (applied to all users)
* ``SpamRetentionTime Nd`` Expunge messages in SpamFolder if older than the given time span. "d" is for days.
* ``TlsSecurity {optional,required}`` 
  controls dovecot ``disable_plaintext_auth`` parameter: if set to ``required`` clear-text authentication methods are disabled, while ``optional`` enables them.
* ``RestrictedAccessGroup`` The value is a long group name, like ``domain
  admins@mydomain.tld``. Members of the given group can login to dovecot
  services only from trusted networks. Install the
  ``nethserver-mail-server-ipaccess`` package to enable this feature.

p3scan example: ::

  p3scan=service
    SSLScan=enabled
    SpamScan=enabled
    TCPPort=8110
    Template=/etc/p3scan/p3scan-en.mail
    VirusScan=enabled
    access=
    status=enabled


rspamd example: ::
    
    rspamd=service
        BlockAttachmentClassList=Exec
        BlockAttachmentCustomList=doc,odt
        BlockAttachmentCustomStatus=disabled
        BlockAttachmentStatus=enabled
        OletoolsStatus=enabled
        Password=uO9QjlnRCDsT0ZCD
        RecipientWhiteList=
        SenderBlackList=
        SenderWhiteList=
        SpamCheckStatus=enabled
        SpamDsnLevel=20
        SpamGreyLevel=4
        SpamKillLevel=15
        SpamSubjectPrefixStatus=disabled
        SpamSubjectPrefixString=***SPAM***
        SpamTag2Level=6
        SpamTagLevel=2
        VirusAction=reject
        VirusCheckStatus=enabled
        VirusScanOnlyAttachment=false
        VirusScanSize=20000000
        status=enabled

Properties:

* ``BlockAttachmentClassList {Exec,Arch}`` Reject the attachements matching the extension list
* ``BlockAttachmentCustomList List`` Reject the attachements matching the custom extension list
* ``BlockAttachmentCustomStatus {enabled,disabled}`` Enable the custom list of rejected extensions
* ``OletoolsStatus {enabled,disabled}`` Enable Oletools to reject suspicious microsoft office document macro
* ``Password`` Password to authenticate the user rspamd for the Rspamd UI
* ``RecipientWhiteList`` Do not perform checks for the recipient list, always accept 
* ``SenderBlackList`` Do not perform checks for the sender list, always reject
* ``SenderWhiteList`` Do not perform checks for the sender list, always accept
* ``SpamCheckStatus {enabled,disabled}`` Enable the SPAM filter
* ``SpamSubjectPrefixStatus {enabled,disabled}`` Enable to rewrite the subject when a possible spam is detected
* ``SpamSubjectPrefixString string`` Rewrite the subject with the string when a possible spam is detected
* ``VirusAction`` Possible action when a virus is detected (reject is default, 'rewrite_subject' to tag as spam)
* ``VirusCheckStatus {enabled,disabled}`` Enable the virus check with Clamav
* ``VirusScanOnlyAttachment {true,false}`` If `true` only messages with non-image attachments will be checked
* ``VirusScanSize`` The messages > n bytes in size are not scanned (valuable for Clamav and Oletools)

domains
^^^^^^^

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
  
accounts
^^^^^^^^

Groups: ::

  employees@domain.com=group
     ...
     MailStatus=enabled
     MailAccess=private

  administrators@domain.com=group
     ...
     MailStatus=enabled
     MailAccess=public

  info@domain.com=group
     ...
     MailStatus=enabled
     MailAccess=public

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

getmail
^^^^^^^

All records of type ``getmail`` are saved inside the ``getmail`` database.

Properties:

* The key is the mail account to be downloaded
* ``status``: can be ``enabled`` or ``disabled``, default is ``enabled``
* ``Account``: local user where messages will be delivered. Should be in the form *user@domain*
* ``Server``: server of the mail account
* ``Username``: user name of the mail account
* ``Password``: password of the mail account
* ``Delete``: numbers of days after downloaded messages will be deleted, ``-1`` means never, ``0`` means immediately
* ``Time``: integer number rappresenting the minutes between each check, valid valued are between 1 and 60
* ``FilterCheck``: if ``enabled``, check downloaded messages for viruses and spam using ``rspamc`` classifier
* ``Retriever``: can be any getmail retriever, see `Getmail official doc <http://pyropus.ca/software/getmail/documentation.html>`_
    Retrievers available in the web interface:

    * ``SimplePOP3Retriever``
    * ``SimplePOP3SSLRetriever``
    * ``SimpleIMAPRetriever``
    * ``SimpleIMAPSSLRetriever`` 

Example: ::

 db getmail set test@neth.eu getmail Account pippo@neth.eu status enabled Password Nethesis,1234 Server localhost Username test@neth.eu Retriever SimplePOP3Retriever Delete enabled Time 30 VirusCheck enabled SpamCheck enabled

quarantine
^^^^^^^^^^

The properties are under the ``rspamd`` key (configuration database): ::

    rspamd=service
    ...
    QuarantineAccount=vmail+quarantine
    QuarantineSelector=is_reject
    QuarantineStatus=enabled
    SpamNotificationStatus=disabled


 * ``QuarantineAccount``: The local email box where to send all spams (spam check is automatically disabled on this account). You must create it manually. You could send it to an external mailbox  but then you must disable the spam check on this server
 * ``QuarantineSelector``: It is possible to move to quarantine all spams (add_header, rewrite_subject, reject), allowed values are ``is_reject`` (default) or ``is_spam``
 * ``QuarantineStatus``: Enable the quarantine, spam are no more rejected: enabled/disabled (default)
 * ``SpamNotificationStatus``: Enable the email notification when email are quarantined: enabled/disabled (default)

For example, the following commands enable the quarantine: ::

   config setprop rspamd QuarantineAccount spam@domain.org QuarantineStatus enabled SpamNotificationStatus enabled
   signal-event nethserver-mail-quarantine-save

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


IP-based IMAP access restriction
--------------------------------

This feature allows to restrict IMAP access for a specific group.
Members of the given group have IMAP access restricted to trusted networks.

1. Install ``nethserver-mail-ipaccess`` package ::

     yum install nethserver-mail-ipaccess

2. Set the limited group, remember to use the full group name: ``<group>@<domain>`` ::

     config setprop dovecot RestrictedAccessGroup <group>@<domain>

   Example for group ``collaborators@nethserver.org``: ::

     config setprop dovecot RestrictedAccessGroup collaborators@nethserver.org

3. Apply the configuration ::

     signal-event nethserver-mail-server-save

Syntax of ``/etc/dovecot/ipaccess.conf``
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The ``dovecot-postlogin`` script enforces an IP-based access policy to dovecot
services when the file :file:``/etc/dovecot/ipaccess.conf`` exists and is readable.

The file is composed by comments and records. Comments are line starting with ``#``,
whilst records have the following syntax: ::

  <long group name> = <cidr list>

A *long group name* is the group name with domain suffix, like ``domain
admins@mydomain.tld``.

The *cidr list* is a comma-separated list of IP and network addresses in CIDR
format, like ``127.0.0.1, 192.168.1.0/24, 10.1.1.2``. The binary conversion is
implemented by the ``NetAddr::IP`` Perl module. See ``perldoc NetAddr::IP`` for
details.


Enable dovecot IMAP rawlog
--------------------------

This section describes how to record the list of IMAP commands sent by the
client and the server during an IMAP session. For more information see `Dovecot
rawlog <https://wiki.dovecot.org/Debugging/Rawlog>`_.

Create the file
``/etc/e-smith/templates-custom/etc/dovecot/dovecot.conf/90rawlog`` with the
following contents: ::

    #
    # 90rawlog (custom)
    #
    import_environment = $import_environment DEBUG=1

    service imap-postlogin \{
      executable = script-login -d rawlog -t /usr/libexec/nethserver/dovecot-postlogin
    \}

Apply the new configuration ::

  signal-event nethserver-mail-server-save

To enable IMAP rawlog for a specific user, identify the user (vmail) home
directory with the following command: ::

    # doveadm user -u first.user@dpnet.nethesis.it
    userdb: first.user@dpnet.nethesis.it
    system_groups_user: first.user@dpnet.nethesis.it
    uid       : 987
    gid       : 990
    home      : /var/lib/nethserver/vmail/first.user@dpnet.nethesis.it

.. warning::

    Always use the user long name form, which includes the ``@domain`` suffix.
    In our example ``first.user`` would not work

Create the ``dovecot.rawlog`` directory and change permissions: ::

    mkdir "/var/lib/nethserver/vmail/first.user@dpnet.nethesis.it/dovecot.rawlog"
    chown vmail:vmail "/var/lib/nethserver/vmail/first.user@dpnet.nethesis.it/dovecot.rawlog"

Detailed IMAP rawlogs are now created under the user's (vmail) home directory.
Each session is split into two files: ``.in`` file for client requests, ``.out``
file for server responses. For instance, ::

    /var/lib/nethserver/vmail/first.user@dpnet.nethesis.it/dovecot.rawlog/20180913-143301-6293.in
    /var/lib/nethserver/vmail/first.user@dpnet.nethesis.it/dovecot.rawlog/20180913-143301-6293.out

The initial timestamp helps to mix them together and obtain the complete IMAP
session trace: ::

    sort -n /var/lib/nethserver/vmail/first.user@dpnet.nethesis.it/dovecot.rawlog/20180913-143301-6293.*


Access the rspamd UI
--------------------

The rspamd UI is available from the same httpd instance of Server Manager: ::
    
    https://<IP>:980/rspamd

Access is granted to any account defined in
``/etc/httpd/admin-conf/rspamd.secret``. By default a ``rspamd`` login is
created automatically. Its password is available with the following command: ::
    
    config getprop rspamd Password

Additional accounts can be created with the following command: ::
    
    /usr/bin/htpasswd -b -m /etc/httpd/admin-conf/rspamd.secret username S3cr3t

If an account provider is configured, the default access policy to rspamd UI is
granting access also to ``admin`` user and members of the ``domain admins`` group.
Type ``config show admins`` for details.

Bayesian rules upgrade to rspamd
--------------------------------

Each ``Junk`` (or ``junkmail``) folder from users' accounts, if present, can be
used to train the Rspamd Bayesian filter database, by running the attached
script: ::

  bash /usr/share/doc/nethserver-mail-server-*/bayes_training.sh


Sender address validation
-------------------------

If the ``postfix/SenderValidation`` prop is set to ``enabled`` the SMTP server
restricts the ``Mail from`` command usage. The sender address must be associated
with the SMTP login name. The login/sender match is specified in the following
Postfix tables, both implemented with an e-smith template:

- ``/etc/postfix/login_maps``
- ``/etc/postfix/login_maps.pcre``

To enable the ``SenderValidation``: ::

    config setprop postfix SenderValidation enabled
    signal-event nethserver-mail-server-update

Postfix SMTP listening ports
----------------------------

The Postfix SMTP server listens on the following TCP ports

- ``25``, standard SMTP port; used by other MTAs
- ``587``, standard SMTP submission port; STARTTLS required by default to protect login passwords; used by MUAs
- ``465``, standard SMTPS submission port; TLS always required at socket level; used by MUAs which not support STARTTLS
- ``10587``, additional SMTP submission port for localhost only; no TLS required; used by local mail applications

Dynamic group aliases
---------------------

If the ``postfix/DynamicGroupAlias`` prop is ``enabled`` an additional
``virtual_alias_maps`` TCP table is available. It expands a long group name to
the group members list with a ``getent group`` call. The table is implemented in
:file:``/usr/libexec/nethserver/postfix-get-group``. Note that group members
lists are returned by SSSD: as such they obey to its caching rules.

When the ``DynamicGroupAlias`` general switch is enabled, individual groups can
be *disabled* and marked *private*. If ``MailStatus`` prop is ``disabled`` the
group long name is not considered a valid email address anymore. The
``MailAccess`` prop works like the ``Access`` prop for ``user`` records: if set
to ``private`` only authenticated SMTP clients are allowed to use it as
recipient. 

Accounts DB ``group`` props example: ::

  employees@domain.com=group
     ...
     MailStatus=enabled
     MailAccess=private

