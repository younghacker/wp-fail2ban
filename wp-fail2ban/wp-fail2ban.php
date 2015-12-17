<?php
/**
 * Plugin Name: WP fail2ban
 * Plugin URI: https://charles.lecklider.org/wordpress/wp-fail2ban/
 * Description: Write all login attempts to syslog for integration with fail2ban.
 * Version: 2.3.1
 * Author: Charles Lecklider
 * Author URI: https://charles.lecklider.org/
 * License: GPL2
 * SPDX-License-Identifier: GPL-2.0
 */

/**
 *  Copyright 2012-15  Charles Lecklider  (email : wordpress@charles.lecklider.org)
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License, version 2, as
 *  published by the Free Software Foundation.
 *
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */


function f2b_openlog() {
        openlog('wordpress('.$_SERVER['HTTP_HOST'].')',
                         LOG_NDELAY|LOG_PID,
                         defined(WP_FAIL2BAN_LOG) ? WP_FAIL2BAN_LOG : LOG_AUTH);
}

function f2b_bail() {
        ob_end_clean();
        header('HTTP/1.0 403 Forbidden');
        header('Content-Type: text/plain');
        exit('Forbidden');
}

function f2b_remote_addr() {
        if (defined('WP_FAIL2BAN_PROXIES')) {
                if (array_key_exists('HTTP_X_FORWARDED_FOR',$_SERVER)) {
                        $ip = ip2long($_SERVER['REMOTE_ADDR']);
                        foreach(explode(',',WP_FAIL2BAN_PROXIES) as $proxy) {
                                if (2 == count($cidr = explode('/',$proxy))) {
                                        $net = ip2long($cidr[0]);
                                        $mask = ~ ( pow(2, (32 - $cidr[1])) - 1 );
                                } else {
                                        $net = ip2long($proxy);
                                        $mask = -1;
                                }
                                if ($net == ($ip & $mask)) {
                                        return (false===($len = strpos($_SERVER['HTTP_X_FORWARDED_FOR'],',')))
                                                        ? $_SERVER['HTTP_X_FORWARDED_FOR']
                                                        : substr($_SERVER['HTTP_X_FORWARDED_FOR'],0,$len);
                                }
                        }
                }
        }
        return $_SERVER['REMOTE_ADDR'];
}

if (defined('WP_FAIL2BAN_BLOCKED_USERS')) {
        function f2b_blocked_users($user, $username, $password){
                if (!empty($username) && preg_match('/'.WP_FAIL2BAN_BLOCKED_USERS.'/i', $username)) {
                        f2b_openlog();
                        syslog(LOG_NOTICE,"Blocked authentication attempt for $username from ".f2b_remote_addr());
                        f2b_bail();
                }
                return $user;
        }
        add_filter( 'authenticate','f2b_blocked_users',1,3);
}

if (defined('WP_FAIL2BAN_BLOCK_USER_ENUMERATION') && true === WP_FAIL2BAN_BLOCK_USER_ENUMERATION) {
        function f2b_block_user_enumeration($redirect_url, $requested_url){
                if (intval(@$_GET['author'])) {
                        f2b_openlog();
                        syslog(LOG_NOTICE,'Blocked user enumeration attempt from '.f2b_remote_addr());
                        bail();
                }
                return $redirect_url;
        }
        add_filter( 'redirect_canonical','f2b_block_user_enumeration',10,2);
}

function f2b_login($user_login, $user) {
        f2b_openlog();
        syslog(LOG_INFO,"Accepted password for $user_login from ".f2b_remote_addr());
}

function f2b_login_failed($username) {
        f2b_openlog();
        syslog(LOG_NOTICE,"Authentication failure for $username from ".f2b_remote_addr());
}

add_action( 'wp_login','f2b_login',10,2);
add_action( 'wp_login_failed','f2b_login_failed');

if (defined('WP_FAIL2BAN_LOG_PINGBACKS') && true === WP_FAIL2BAN_LOG_PINGBACKS) {
        function f2b_log_pingbacks($call) {
                if ('pingback.ping' == $call) {
                        openlog(LOG_USER,'WP_FAIL2BAN_PINGBACK_LOG');
                        syslog(LOG_INFO,"Pingback requested from ".remote_addr());
                }
        }
        add_action( 'xmlrpc_call','f2b_log_pingbacks');
}
