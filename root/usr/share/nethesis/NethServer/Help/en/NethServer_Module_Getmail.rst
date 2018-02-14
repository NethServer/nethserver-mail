==============
POP3 connector
==============

External email addresses are mailboxes that
are checked at regular intervals using the **POP3** or **IMAP4** protocol.
Messages contained in the mailbox are downloaded and delivered to
local users.

Accounts
========

Configure the list of external addresses and the association with the user of the system.

Create / Modify
---------------

Create or edit an external address.

Mail address
    The external email address to check.

Protocol
    The protocol used to access the remote server. It can be *POP3*, *POP3 with SSL*, *IMAP4* or *IMAP with SSL*.

Server address
    Host name or IP address of the remote server.

Username
    Username used for authentication to the remote system.

Password
    The password used to authenticate.

Deliver messages to
    Select the user that will receive the downloaded messages. 

Check this account every
    Frequency of checking for new messages on the external addresses.
    It is recommended an interval at least of 15 minutes.

Delete downloaded messages
    Choose if downloaded messages should be removed.

Check messags for SPAM
    If enabled, downloaded messages will be checked for SPAM.

Check messags for virus
    If enabled, downloaded messages will be checked for virus:
    infected mails will be *discarded*.


Delete
-------

Deleting an account will *not* delete the messages already delivered.

