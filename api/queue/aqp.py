#!/usr/bin/env python

# Source: https://github.com/alexjurkiewicz/apq/

'''
Parse Postfix mailq and return a filtered list as JSON
'''

import sys, subprocess, re, time, datetime
try:
    import argparse
except ImportError:
    print >> sys.stderr, 'Error: Can\'t import argparse. Try installing python-argparse.'
    sys.exit(1)

MONTH_MAP = {'Jan': 1, 'Feb': 2, 'Mar': 3, 'Apr': 4, 'May': 5, 'Jun': 6, 'Jul': 7, 'Aug': 8, 'Sep': 9, 'Oct': 10, 'Nov': 11, 'Dec': 12}
UNIX_EPOCH = datetime.datetime(1970,1,1)

def call_mailq(args):
    '''
    Call mailq and return stdout as a string
    '''
    if not args.mailq_data:
        cmd = subprocess.Popen(['mailq'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        stdout, stderr = cmd.communicate()
        if cmd.returncode not in (0, 69):
            print >>sys.stderr, 'Error: mailq failed: "{}"'.format(stderr.strip())
    else:
        with open(args.mailq_data, 'r') as f:
            stdout = f.read()
    return stdout.strip()

def parse_mq(args):
    '''
    Parse mailq output and return data as a dict.
    '''
    mailq_stdout = call_mailq(args)
    curmsg = None
    msgs = {}
    for line in mailq_stdout.splitlines():
        if not line or line[:10] == '-Queue ID-' or line[:2] == '--':
            continue
        if line[0] in '0123456789ABCDEF':
            s = line.split()
            curmsg = s[0]
            if curmsg[-1] == '*':
                status = 'active'
                curmsg = curmsg[:-1]
            elif curmsg[-1] == '!':
                status = 'held'
                curmsg = curmsg[:-1]
            else:
                status = 'deferred'
            msgs[curmsg] = {
                'size': s[1],
                'rawdate': ' '.join(s[2:6]),
                'sender': s[-1],
                'reason': '',
                'status': status,
                }
        elif '@' in line: # XXX: pretty dumb check
            msgs[curmsg]['recipient'] = line.strip()
        elif line.lstrip(' ')[0] == '(':
            msgs[curmsg]['reason'] = line.strip()[1:-1].replace('\n', ' ')
        else:
            return {}
    return msgs

def parse_ml():
    '''
    Read and parse messages from /var/log/mail.log
    XXX: can be optimised as per parse_mq
    '''
    lines = 0
    msgs = {}
    with open('/var/log/maillog', 'rb') as f:
        for line in f.readlines():
            lines += 1
            if lines % 100000 == 0:
                # Technically off by one
                print >> sys.stderr, 'Processed %s lines (%s messages)...' % (lines, len(msgs))
            try:
                l = line.strip().split()
                if l[4][:13] == 'postfix/smtpd' and l[6][:7] == 'client=':
                    curmsg = l[5].rstrip(':')
                    if curmsg not in msgs:
                        msgs[curmsg] = {
                            'source_ip': l[6].rsplit('[')[-1].rstrip(']'),
                            'date': parse_syslog_date(' '.join(l[0:3])),
                        }
                elif False and l[4][:15] == 'postfix/cleanup' and l[6][:11] == 'message-id=': # dont want msgid right now
                    curmsg = l[5].rstrip(':')
                    if curmsg in msgs:
                        msgid = l[6].split('=', 1)[1]
                        if msgid[0] == '<' and msgid[-1] == '>':
                            # Not all message-ids are wrapped in < brackets >
                            msgid = msgid[1:-1]
                        msgs[curmsg]['message-id'] = msgid
                elif l[4][:12] == 'postfix/qmgr' and l[6][:5] == 'from=':
                    curmsg = l[5].rstrip(':')
                    if curmsg in msgs:
                        msgs[curmsg]['sender'] = l[6].split('<', 1)[1].rsplit('>')[0]
                elif l[4][:13] == 'postfix/smtp[' and any([i[:7] == 'status=' for i in l]):
                    curmsg = l[5].rstrip(':')
                    if curmsg in msgs:
                        status_field = [i for i in l if i[:7] == 'status='][0]
                        status = status_field.split('=')[1]
                        msgs[curmsg]['delivery-status'] = status
            except StandardError:
                print >> sys.stderr, 'Warning: could not parse log line: %s' % repr(line)
    print >> sys.stderr, 'Processed %s lines (%s messages)...' % (lines, len(msgs))
    return msgs

def parse_mailq_date(d, now):
    '''
    Convert mailq plain text date string to unix epoch time
    '''
    _, mon_str, day, time_str = d.split()
    hour, minute, second = time_str.split(':')
    d = datetime.datetime(year=now.year, month=MONTH_MAP[mon_str], day=int(day), hour=int(hour), minute=int(minute), second=int(second))
    # Catch messages generated "last year" (eg in Dec when you're running apq on Jan 1)
    if d > now:
        d = datetime.datetime(year=now.year-1, month=MONTH_MAP[mon_str], day=int(day), hour=int(hour), minute=int(minute), second=int(second))
    #return float(d.strftime('%s'))
    return float((d - UNIX_EPOCH).total_seconds())

def parse_syslog_date(d):
    '''
    Parse a date in syslog's format (Sep 5 10:30:36) and return a UNIX time
    XXX: can be optimised as per parse_mailq_date
    '''
    t = time.strptime(d + ' ' + time.strftime('%Y'), '%b %d %H:%M:%S %Y')
    if t > time.localtime():
        t = time.strptime(d + ' ' + str(int(time.strftime('%Y')-1)), '%b %d %H:%M:%S %Y')
    return time.mktime(t)

def filter_on_msg_key(msgs, pattern, key):
    '''
    Filter msgs, returning only ones where 'key' exists and the value matches regex 'pattern'.
    '''
    pat = re.compile(pattern, re.IGNORECASE)
    msgs = dict((msgid, data) for (msgid, data) in msgs.iteritems() if key in data and re.search(pat, data[key]))
    return msgs

def filter_on_msg_age(msgs, condition, age):
    '''
    Filter msgs, returning only items where key 'date' meets 'condition' maxage/minage checking against 'age'.
    '''
    assert condition in ['minage', 'maxage']
    # Determine age in seconds
    if age[-1] == 's':
        age_secs = int(age[:-1])
    elif age[-1] == 'm':
        age_secs = int(age[:-1]) * 60
    elif age[-1] == 'h':
        age_secs = int(age[:-1]) * 60 * 60
    elif age[-1] == 'd':
        age_secs = int(age[:-1]) * 60 * 60 * 24
    # Create lambda
    now = datetime.datetime.now()
    if condition == 'minage':
        f = lambda d: (now - datetime.datetime.fromtimestamp(d)).total_seconds() >= age_secs
    elif condition == 'maxage':
        f = lambda d: (now - datetime.datetime.fromtimestamp(d)).total_seconds() <= age_secs
    # Filter
    msgs = dict((msgid, data) for (msgid, data) in msgs.iteritems() if f(data['date']))
    return msgs

def format_msgs_for_output(msgs):
    '''
    Format msgs for output. Currently replaces time_struct dates with a string
    '''
    for msgid in msgs:
        if 'date' in msgs[msgid]:
            msgs[msgid]['date'] = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(msgs[msgid]['date']))
    return msgs

def parse_args():
    '''
    Parse commandline arguments
    '''
    parser = argparse.ArgumentParser(description='Parse postfix mail queue.')
    parser.add_argument('-j', '--json', action='store_true', help='JSON output (default)')
    parser.add_argument('-y', '--yaml', action='store_true', help='YAML output')
    parser.add_argument('-c', '--count', action='store_true', help='Return only the count of matching items')
    parser.add_argument('--log', action='store_true', help='Experimental: Search /var/log/mail.log as well.')
    parser.add_argument('--mailq-data', default=None, help='Use this file\'s contents instead of calling mailq')
    parser.add_argument('--reason', '-m', default=None, help='Select messages with a reason matching this regex')
    parser.add_argument('--recipient', '-r', default=None, help='Select messages with a recipient matching this regex')
    parser.add_argument('--sender', '-s', default=None, help='Select messages with a sender matching this regex')
    parser.add_argument('--parse-date', action='store_true', default=None, help='Parse dates into a more machine-readable format (slow) (implied by minage/maxage)')
    parser.add_argument('--maxage', '-n', default=None, help='Select messages younger than the given age. Format: age[{d,h,m,s}]. Defaults to seconds. eg: 3600, 1h')
    parser.add_argument('--minage', '-o', default=None, help='Select messages older than the given age. Format: age[{d,h,m,s}]. Defaults to seconds. eg: 3600, 1h')
    parser.add_argument('--exclude-active', '-x', action='store_true', help='Exclude items in the queue that are active')
    parser.add_argument('--only-active', action='store_true', help='Only include items in the queue that are active')

    args = parser.parse_args()

    if args.minage and args.minage[-1].isdigit():
        args.minage += 's'
    elif args.minage and args.minage[-1] not in 'smhd':
        print >> sys.stderr, 'Error: --minage format is incorrect. Examples: 1800s, 30m'
        sys.exit(1)
    if args.maxage and args.maxage[-1].isdigit():
        args.maxage += 's'
    elif args.maxage and args.maxage[-1] not in 'smhd':
        print >> sys.stderr, 'Error: --maxage format is incorrect. Examples: 1800s, 30m'
        sys.exit(1)
    if args.exclude_active and args.only_active:
        print >> sys.stderr, 'Error: --exclude-active and --only-active are mutually exclusive'
        sys.exit(1)

    return args

def output_msgs(args, msgs):
    '''
    Take msgs and format it as requested.
    '''
    if args.count:
        print len(msgs)
    else:
        msgs = format_msgs_for_output(msgs)
        if args.yaml:
            try:
                import yaml
            except ImportError:
                print >> sys.stderr, 'Error: Can\'t import yaml. Try installing python-yaml.'
                sys.exit(1)
            print yaml.dump(msgs)
        else:
            import json
            print json.dumps(msgs, indent=2)

def parse_msg_dates(msgs, now):
    new_msgs = {}
    for msgid, data in msgs.iteritems():
        if 'date' not in data:
            data['date'] = parse_mailq_date(data['rawdate'], now)
            new_msgs[msgid] = data
    return new_msgs

def main():
    '''
    Main function
    '''
    args = parse_args()

    # Load messages
    msgs = {}
    if args.log:
        msgs.update(parse_ml())
    msgs.update(parse_mq(args))

    # Prepare data
    if args.parse_date or args.minage or args.maxage:
        now = datetime.datetime.now()
        msgs = parse_msg_dates(msgs, now)

    # Filter messages
    if args.reason:
        msgs = filter_on_msg_key(msgs, args.reason, 'reason')
    if args.sender:
        msgs = filter_on_msg_key(msgs, args.sender, 'sender')
    if args.recipient:
        msgs = filter_on_msg_key(msgs, args.recipient, 'recipient')
    if args.minage:
        msgs = filter_on_msg_age(msgs, 'minage', args.minage)
    if args.maxage:
        msgs = filter_on_msg_age(msgs, 'maxage', args.maxage)
    if args.exclude_active:
        msgs = dict((msgid, data) for (msgid, data) in msgs.iteritems() if data.get('status') != 'active')
    elif args.only_active:
        msgs = dict((msgid, data) for (msgid, data) in msgs.iteritems() if data.get('status') == 'active')

    output_msgs(args, msgs)

if __name__ == '__main__':
    try:
        main()
    except KeyboardInterrupt:
        print ''
        sys.exit(1)
