# wp-fail2ban
wp fail2ban plugin backported to php 5.2

Original plugin's url https://wordpress.org/plugins/wp-fail2ban/

##### Tested with:
* Wordpress Version 4.4
* php 5.2
* httpd-2.2.29
* fail2ban-0.8.14

##### Installation
* Copy wp-fail2ban folder (wp-fail2ban.php enough) into your wp-plugins folder `wp-content/plugins/`.
* Login into wp-admin panel and activate `WP fail2ban` plugin.
* Install or change `fail2ban` and `iptables` settings follow this [instructions](https://github.com/younghacker/wp-fail2ban/tree/master/fail2ban).

##### Plugin tunning
If someone use pingback to your site too frequently you can block it.
Define variables in `wp-config.php`
```
define('WP_FAIL2BAN_LOG_PINGBACKS', true);  # 
```

##### Troubleshutting
* Add `define('WP_DEBUG', true);` into `wp-config.php` in site's public_html folder, and refresh web page, you can see an error.
* If plugin activation makes the site unavialable, login trhough ftp and rename `wp-content/plugins/wp-fail2ban` to `wp-fail2ban-1`. This will temporary disable wp-fail2ban plugin.
* If your server using nginx and/or varnish proxy/cache wp-fail2ban plugin can write to log only server's ip address, not client.ip. Configure your server depends on mode how it work.
