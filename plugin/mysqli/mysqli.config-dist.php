<?php
/**
 * Copyright 2009 HouYu Li <karadog@gmail.com>
 *
 * This file is part of PHP Micro Kernel.
 *
 * PHP Micro Kernel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PHP Micro Kernel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PHP Micro Kernel.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('LPLUGINS')) die('Access violation error!');

/**
 * Database configurations
 */
$GLOBALS['db_configs'] = array(
    'conn1' => array(
        'host'      => 'localhost',     // MySQL server hostname
        'user'      => 'root',          // User for connecting MySQL server
        'passwd'    => '',              // User password
        'database'  => 'test',          // The database to be used
        'charset'   => 'utf8',          // The connection charset
        'prefix'    => 'pr3_',          // The prefix of tables in the database
        'default'   => true             // Use as default connection
    ),
/**
 * Another mysqli connection configuration
    'conn2' => array(
        'host'      => 'localhost',
        'user'      => 'root',
        'passwd'    => '',
        'database'  => 'test',
        'charset'   => 'utf8',
        'prefix'    => 'pr3_',
        'default'   => false
    ),
 */
    'enable_table_cache'    => false
); // $GLOBALS['db_configs']
