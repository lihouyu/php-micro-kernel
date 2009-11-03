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
 * Make plural word
 *
 * @param string $noun The word to be processed
 * @return string
 */
function pluralize($noun) {
    if (preg_match('/(s|sh|ch|x)$/i', $noun)) {
        $pl_noun = $noun.'es';
    } else if (preg_match('/y$/i', $noun)) {
        if (preg_match('/([aeiou]y)$/i', $noun)) {
            $pl_noun = $noun.'s';
        } else {
            $pl_noun = substr($noun, 0, strlen($noun) - 1).'ies';
        }
    } else {
        $pl_noun = $noun.'s';
    }

    return $pl_noun;
} // pluralize($noun)

/**
 * Generate table name according to object name
 *
 * @param string $class_name The class name to be processed
 * @return string
 */
function transform_class_name($class_name) {
    $t_class_name = '';

    // Get upcase letter index
    $up_case_idx = array();

    for ($i = 1; $i < strlen($class_name); $i++) {
        if (ord($class_name[$i]) >= 65 &&
            ord($class_name[$i]) <= 90) {
            $up_case_idx[] = $i;
        }
    }
    //

    if (sizeof($up_case_idx) == 0) {
        $t_class_name = $class_name;
    } else {
        $start_idx = 0;
        for($i = 0; $i < sizeof($up_case_idx); $i++) {
            $t_class_name .= '_'.substr($class_name,
                $start_idx, $up_case_idx[$i] - $start_idx);
            $start_idx = $up_case_idx[$i];
        }
        $t_class_name .= '_'.substr($class_name,
                $start_idx);

        $t_class_name = substr($t_class_name, 1);
    }

    return strtolower($t_class_name);
} // transform_class_name($class_name)
