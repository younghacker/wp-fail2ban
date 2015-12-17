# change fail2ban setup
##### Add new section into:
```
/etc/fail2ban/jail.conf
```
with content:
```
[apache-wp-login]
enabled  = true
filter   = apache-wp-login
action   = iptables-HTTP[name=CMS]
logpath  = /var/log/messages
maxretry = 3
findtime = 120
```

Add two files (iptables-HTTP.conf and apache-wp-login.conf) from repository's
folders to folders on your server and restart fail2ban.
```
# service fail2ban restart
Stopping fail2ban:                                         [  OK  ]
Starting fail2ban:                                         [  OK  ]
```
