<?php
/**
 * Copyright 2009 HouYu Li <karadog@gmail.com>
 *
 * This file is part of LoadingPlugins.
 *
 * LoadingPlugins is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * LoadingPlugins is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with LoadingPlugins.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('LPLUGINS')) die('Access violation error!');

/**
 * Check the value whether it's in a valid number format
 *
 * @param string $var The value to be checked
 * @return bool
 */
function v_is_numeric($var) {
    return is_numeric($var);
} // v_is_numeric($var)

/**
 * Check the string whether it's empty
 * It will strip HTML tags and entities automatically
 *
 * @param string $var The string to be checked
 * @return bool
 */
function v_is_empty($var) {
    $var = strip_tags($var);
    $var = str_replace('&nbsp;', '', $var);
    return v_custom_match('/^\s*$/', $var);
} // v_is_empty($var)

/**
 * Check the value whether it's in a valid email format
 *
 * @param string $var The value to be checked
 * @return bool
 */
function v_is_email($var) {
    $email_parts = explode('@', $var);
    if (sizeof($email_parts) != 2) {
        return false;
    }

    /* check the name part */
    if (preg_match('/^\..*$/', trim($email_parts[0])) ||
        preg_match('/^.*\.$/', trim($email_parts[0])) ||
        preg_match('/^\..*\.$/', trim($email_parts[0]))) {
        return false;
    }
    if (!preg_match('/^[0-9a-zA-Z\!#\$%\*\/\?\|\^\{\}`~&\'\+\-=_\.]+$/', trim($email_parts[0]))) {
        return false;
    }

    /* check the domain part */
    if (preg_match('/\.\./', trim($email_parts[1]))) {
        return false;
    }
    $domain_parts = explode('.', $email_parts[1]);
    if (sizeof($domain_parts) < 2) {
        return false;
    }
    foreach ($domain_parts as $s) {
        $s = trim($s);
        if (preg_match('/^\-.*$/', $s) ||
            preg_match('/^.*\-$/', $s) ||
            preg_match('/^\-.*\-$/', $s)) {
            return false;
        }
        if (!preg_match('/^[0-9a-zA-Z\-]+$/', $s)) {
            return false;
        }
    }

    return true;
} // v_is_email($var)

/**
 * Check the string according to the given pattern
 *
 * @param string $regexp The pattern you want to test on the input string
 * @param string $var The input string
 * @return bool
 */
function v_custom_match($regexp, $var) {
    return preg_match($regexp, $var);
} // v_custom_match($regexp, $var)
