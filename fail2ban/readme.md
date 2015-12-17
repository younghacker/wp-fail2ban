# change fail2ban setup

##### iptables
I don't like how fail2ban add rules into iptables. The first rule that need to be on my opinion is a 
```
-A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT
```
I like when all fail2ban rules added into special tables. To do that need to prepare iptables and write own fail2ban action file.
```
*filter
# ...
:HTTP - [0:0]
:CMS - [0:0]
# ...
-A INPUT -m state --state NEW -m tcp -p tcp -m multiport --dports 80,443 -j HTTP
# ...
-A HTTP -j CMS
-A HTTP -j ACCEPT
-A CMS -j RETURN
# ...
COMMIT
```
When fail2ban will add block rule it appear in `CMS` table.

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
There is used filter `apache-wp-login` and `iptables-HTTP` with `CMS` table name
Add two files (`iptables-HTTP.conf` and `apache-wp-login.conf`) from repository's
folders to folders on your server and restart fail2ban.
```
# service fail2ban restart
Stopping fail2ban:                                         [  OK  ]
Starting fail2ban:                                         [  OK  ]
```
