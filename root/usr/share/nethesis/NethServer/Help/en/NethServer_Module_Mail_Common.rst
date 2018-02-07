.. --initial-header-level=3

Domains
=======

The table contains the list of internet domain names for which the
server will accept incoming email.

Create / Modify
---------------

Add a domain to the list of those configured for the email reception.


Domain
    The domain name, for example *nethesis.it*.

Description
    An optional field useful to the system administrator to take note
    of domain information.

Delivery locally
    Select this option to configure the server to deliver
    incoming mail addressed to the specified domain
    in local folders.

Forward to another server
    If you select this option, incoming mail will
    be forwarded to the specified server.

Disclaimer (legal notice)
    Automatically add a legal message (disclaimer)
    to all outgoing emails (not addressed to the domain).


Delete
-------

Remove the domain from those managed by the server. Any email
intended for the domain will be rejected.


Filter
======

Configure the filtering options of the email (antivirus, antispam,
forbidden attachments, etc).

Antivirus
    Enable virus scanning of emails in transit.

Antispam
    Enable antispam scanning of incoming emails.

Prefix Spam
    This prefix is added to the object underlying the recognized emails
    as spam.

Attachment Blocking
    The email server will block emails that contain attachments of types
    specified.

Executable
    The email server will block executable programs in email attachments.

Archives
    The email server will block emails with attachments containing archive files (zip,
    rar, etc.).

Custom List
    Define a list of extensions that will be blocked,
    such as doc, pdf, etc. (without starting dot, ie doc and not .doc).

Messages
========

Configure the management of email messages.

Accept message size to
    Use the cursor to select the maximum size of a
    single email message. The server will reject email larger than the value
    set, returning an explanatory error.

Retry sending for
    Use the cursor to select the maximum time for which the server
    try to send a message. When it reaches the maximum time
    and the email has not yet been delivered, the sender will receive a
    error and the message is removed from the send queue, the server will no
    longer attempting to deliver it.

Always send a copy (Bcc)
    Send a blind carbon copy (Bcc) to the given email address for any message
    entering the email system.

Queue Management
================

This tab allows you to manage the queue of emails in transit on the server.
The table lists all the mail waiting to be delivered,
and is normally empty. The following fields will be shown:

* Id: identifier of the message
* Sender: from email address (who sent the message)
* Size: The size in bytes of the email
* Date: The date of creation of the email
* Recipients: the list of recipients


Delete
-------

It is possible to delete an email in the queue, for example, an email sent
by mistake or too large.

Remove all
-------------

The button will delete all the emails in the queue.

Try sending
-------------

Normally, the server, in case of problems while sending the email,
retries at regular intervals. Clicking Attempt to send, emails
will be sent immediately.

Update
--------

Reload the list of emails in the queue.


SMTP access
===========

Allow relay from IP addresses
    Allow sending mail messages from the specified IP address, without
    SMTP authentication and other security policy restrictions.  This option
    is good for legacy network devices that do not support SMTP/AUTH protocol.

Allow relay from trusted networks
    Allow sending mail messages from any host in the trusted networks, without
    SMTP authentication and other security policy restrictions.

Enable authentication on port 25
    Email clients should send messages only using the standard submission port
    587.  For legacy environments, this option enables client authentication and
    message relaying also on port 25.
