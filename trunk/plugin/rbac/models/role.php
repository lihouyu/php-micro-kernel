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

final class Role extends ActiveObject {
    public function __construct($aikey_val = false, $stat_new = true) {
        $global_acl_configs =& $GLOBALS['acl_configs'];
        $this->has_many = array($global_acl_configs['user_class_name']);
        parent::__construct($aikey_val, $stat_new);
    }
}
