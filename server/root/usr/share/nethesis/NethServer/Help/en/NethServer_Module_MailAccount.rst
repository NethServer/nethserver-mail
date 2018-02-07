===============
Email addresses
===============

Associate email address to users and shared mailboxes.

User mailboxes
==============

Edit
^^^^

Access to email services
    Enable or disable SMTP, IMAP and POP3 access for the user.

Local network only
    Enabling this option will block the reception of messages
    from external senders.

Forward messages
    Forward received emails to an alternative address.

Keep a copy on the server
    Forwarded email will still be saved in the user's inbox.

Custom mailbox quota
    Allows you to specify a dimension value other than the default.

Customize spam message retention
    The spam emails are deleted at regular intervals. Ticking the
    box you can set the number of days the user's messages
    classified as spam, will be maintained
    in the system before being deleted.

Shared mailboxes
================

Create / Modify
^^^^^^^^^^^^^^^

Name
    The name should be a descriptive string and will be shown by IMAP clients and
    mail web applications

Owner groups
    Select one or more owning groups of users that will be granted read and write
    privileges on the shared mailbox

Create Alias
    Create automatically a public alias for this shared mailbox

Special access
    IMAP ACLs set from other email clients will be listed here

Delete
^^^^^^

Delete the shared mailbox and all of its contents.

Mail aliases
============

Create / Modify
^^^^^^^^^^^^^^^

Create the association among a new email address and users,
shared mailboxes and external email addresses.

Email address
    Specify in the text field only the part before **@** character.
    Then choose from the drop-down menu if the address is for a
    specific domain or for *all* domains in the system.

Destinations
    Select users and shared mailboxes to associate to the email alias. A group
    is automatically expanded to the current member list.

External email destinations
    A comma (or semicolon) separated list of email addresses. This is an input
    only field: after the input has been submitted, parsed values will be added
    to :guilabel:`Destinations`.

Local network only
    Enabling this option will block the reception of messages
    from external senders.

Delete
^^^^^^

Delete the e-mail alias. This does not affect messages already delivered to
the user or into the shared mailbox associated with the alias.
Future messages destined the address will be rejected.
