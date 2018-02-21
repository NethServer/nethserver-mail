==========
POP3 proxy
==========

The proxy intercepts POP3 connections to external servers, 
then it scans incoming mail to block viruses and tag spam. 
The process is absolutely transparent for mail clients,
the user believes to connect directly to your ISP's POP3 server,
but the server will intercept all traffic and handle connections to external servers.

Enabled/Disabled
    If enabled, all connections to the POP3 port (110) 
    will be intercepted by the firewall and sent to the proxy. 

Antivirus
    Enable virus check on downloaded mails.

Antispam
    Enable spam check on downloaded mails.

POP3s scan (port 995)
    Enable all checks on POP3 with SSL (POP3s):
    all connections to port 995 will be intercepted by the firewall and sent to the proxy.
    The server will take care to establish secure connections to external servers, while data transfers
    to LAN clients will be in clear text.
    *NB*: the clients must be configured to connect to port 995 but will have to turn off encryption. 

Mail template language
    In case of virus detection, the client is notified with a special message. 
    This field allows you to choose the language of the notification message.
