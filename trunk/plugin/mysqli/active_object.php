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
 * The Object-Relation Mapping(ORM) class
 */
class ActiveObject {
    /**
     * The MySQLi connection instance holder
     *
     * @access protected
     * @var object
     */
    protected $mydb = false;

    /**
     * $prefix is the prefix of data tables
     *
     * @access protected
     * @var string
     */
    protected $prefix;

    /**
     * Data table name represented in database
     *
     * @access public
     * @var string
     */
    public $table_name = false;

    /**
     * Record table object
     *
     * @access public
     * @var object
     */
    public $table_object = false;

    /**
     * Indicate whether this object is new created
     *
     * @access protected
     * @var bool
     */
    protected $stat_new = false;

    /**
     * The auto_increment field name
     *
     * @access protected
     * @var string
     */
    protected $aikey = false;

    /**
     * The class name for the current object
     *
     * @access protected
     * @var string
     */
    protected $class_name;

    /**
     * Validations require true
     *
     * @access protected
     * @var array
     */
    protected $yes_validate = array();

    /**
     * Validations require false
     *
     * @access protected
     * @var array
     */
    protected $no_validate = array();

    /**
     * 1 -> 1 children relation objects
     *
     * @access public
     * @var array
     */
    public $has_one = array();

    /**
     * 1 -> many children relation objects
     *
     * @access public
     * @var array
     */
    public $has_many = array();

    /**
     * 1 -> 1 parent relation objects
     *
     * @access public
     * @var array
     */
    public $belong_to = array();

    /**
     * 1 -> many parent relation objects
     *
     * @access public
     * @var array
     */
    public $belong_to_many = array();

    /**
     * Array for holding parent objects
     *
     * @access public
     * @var array
     */
    public $parents = array();

    /**
     * Array for holding children objects
     *
     * @access public
     * @var array
     */
    public $children = array();

    /**
     * Error message holder
     *
     * @access public
     * @var string
     */
    public $errmsg;

    /**
     * ActiveObject constructor
     *
     * @param int $aikey_val The auto_increment key value for loading an existing ActiveObject
     * @param bool $stat_new Whether the RecordObject is newly created or loaded from an existing record
     */
    public function __construct($aikey_val = false, $stat_new = true) {
        $global_db_configs =& $GLOBALS['db_configs'];
        $table_cache_dir = $global_db_configs['table_cache_dir'];
        $this->class_name = get_class($this);

        /* Get default MySQLi connection instance */
        $this->mydb =& MyDb::get_instance();
        if (!$this->mydb) return false;
        $this->prefix = $this->mydb->get_prefix();

        /* Generate table name if no custom table name given */
        if (!$this->table_name) {
            $this->table_name = pluralize(transform_class_name($this->class_name));
        }

        /* Load table info */
        //$this->table_object =& $this->_getRecordTable($this->_table_name);
        $table_object_cache_file = $table_cache_dir.DS.$this->table_name.'.cache';
        if ($global_db_configs['enable_table_cache']) {
            if (file_exists($table_object_cache_file)) {
                $table_object_cache = file_get_contents($table_object_cache_file);
                $this->table_object = unserialize($table_object_cache);
            }
        }
        if (!$this->table_object) {
            $rs_table = $this->mydb->exec_query("DESCRIBE `{$this->prefix}{$this->table_name}`");
            if (!$rs_table) return false;
            $this->table_object = new stdClass();
            $this->table_object->fields = array();
            $this->table_object->pkeys = array();
            $this->table_object->aikey = false;
            while ($field = $rs_table->fetch_object()) {
                $this->table_object->fields[] = $field;
                if (strpos(strtolower($field->Key), 'pri') !== false) {
                    $this->table_object->pkeys[] = $field->Field;
                }
                if (strpos(strtolower($field->Extra), 'auto_increment') !== false) {
                    $this->table_object->aikey = $field->Field;
                }
            }
            if (!$this->table_object->aikey && $this->aikey) {
                $this->table_object->aikey = $this->aikey;
            }
            $rs_table->free();
        }
        if (!$this->table_object) return false;
        if ($global_db_configs['enable_table_cache'] &&
            is_writable($table_object_cache_file)) {
            file_put_contents($table_object_cache_file, serialize($this->table_object));
        }
        //

        /* Set default object status */
        $this->stat_new = $stat_new;

        /* load object data if the $aikey_val is not false */
        if ($aikey_val !== false) {
            if (!$this->table_object->aikey) {
                return false;
            } else {
                $sql = "SELECT * FROM `{$this->prefix}{$this->table_name}` "
                    ."WHERE `{$this->table_object->aikey}`=?";
                $rs = $this->mydb->exec_query($sql, array($aikey_val));
                if (!$rs) return false;
                $row = $rs->fetch_assoc();
                if (NULL != $row) {
                    $this->set($row);
                    $this->stat_new = false;
                }
                $rs->free();
            }
        }
    } // ActiveObject::__construct($aikey_val = false, $stat_new = true)

    /**
     * Set object data
     *
     * @access public
     * @param array $params Parameters in key=>value pairs while key represents the property of the object
     */
    public function set($params) {
        if ($this->stat_new) {
            foreach ($this->table_object->fields as $field) {
                $attr_name = $field->Field;
                if (isset($params[$attr_name])) {
                    $this->$attr_name = $params[$attr_name];
                } else {
                    $this->$attr_name = $field->Default;
                }
            }
        } else {
            foreach ($params as $attr_name => $value) {
                $this->$attr_name = $value;
            }
        }
    } // ActiveObject->set($params)

    /**
     * Insert new object data
     *
     * @access protected
     * @return bool
     */
    protected function insert() {
        $fields = "";
        $value_place_holders = "";
        $values = array();

        foreach ($this->table_object->fields as $field) {
            if ($field->Field != $this->table_object->aikey) {
                $attr_name = $field->Field;
                $fields .= ", `$attr_name`";
                $value_place_holders .= ", ?";
                if (!isset($this->$attr_name)) {
                    $values[] = $field->Default;
                } else {
                    $values[] = $this->$attr_name;
                }
            }
        }
        $fields = substr($fields, 2);
        $value_place_holders = substr($value_place_holders, 2);

        $sql = "INSERT INTO `{$this->prefix}{$this->table_name}` ($fields) VALUES "
            ."($value_place_holders)";
        $rs = $this->mydb->exec_query($sql, $values);
        if ($rs) {
            if ($this->table_object->aikey) {
                $insert_id = $this->mydb->insert_id();
                $ai_attr_name = $this->table_object->aikey;
                $this->$ai_attr_name = $insert_id;
            }

            $this->stat_new = false;
        }

        return $rs;
    } // ActiveObject->insert()

    /**
     * Update object data
     *
     * @access protected
     * @return bool
     */
    protected function update() {
        if (!$this->table_object->aikey) {
            return false;
        } else {
            $set_fields = "";
            $values = array();

            foreach ($this->table_object->fields as $field) {
                if ($field->Field != $this->table_object->aikey) {
                    $attr_name = $field->Field;
                    $set_fields .= ", `$attr_name`=?";
                    $values[] = $this->$attr_name;
                }
            }
            $set_fields = substr($set_fields, 2);

            $ai_attr_name = $this->table_object->aikey;
            $where = "`$ai_attr_name`=?";
            $values[] = $this->$ai_attr_name;

            $sql = "UPDATE `{$this->prefix}{$this->table_name}` SET $set_fields "
                ."WHERE $where";
            $rs = $this->mydb->exec_query($sql, $values);
            return $rs;
        }
    } // ActiveObject->update()

    /**
     * Insert or save object data
     *
     * @access public
     * @return bool
     */
    public function save() {
        /* validate data required */
        // TODO : how to check whether the object has the specified attribute.
        $error_msg = '';
        if (sizeof($this->yes_validate) >= 1) {
            foreach ($this->yes_validate as $validate => $attr_array) {
                if (sizeof($attr_array) == 0) {
                    continue;
                }
                if ($validate == '_regexp_') {
                    foreach ($attr_array as $attr) {
                        $attr_name = $attr[1];
                        if (!v_custom_match($attr[0], $this->$attr_name)) {
                            $error_msg .= $attr[2]."\n";
                        }
                    }
                } else {
                    foreach ($attr_array as $attr) {
                        $attr_name = $attr[0];
                        $validator_name = 'v_'.$validate;
                        if (!$validator_name($this->$attr_name)) {
                            $error_msg .= $attr[1]."\n";
                        }
                    }
                }
            }
        }
        if (sizeof($this->no_validate) >= 1) {
            foreach ($this->no_validate as $validate => $attr_array) {
                if (sizeof($attr_array) == 0) {
                    continue;
                }
                if ($validate == '_regexp_') {
                    foreach ($attr_array as $attr) {
                        $attr_name = $attr[1];
                        if (v_custom_match($attr[0], $this->$attr_name)) {
                            $error_msg .= $attr[2]."\n";
                        }
                    }
                } else {
                    foreach ($attr_array as $attr) {
                        $attr_name = $attr[0];
                        $validator_name = 'v_'.$validate;
                        if ($validator_name($this->$attr_name)) {
                            $error_msg .= $attr[1]."\n";
                        }
                    }
                }
            }
        }
        if (!empty($error_msg)) {
            $this->errmsg = $error_msg;
            return false;
        }

        /* execute update */
        if ($this->stat_new) {
            return $this->insert();
        } else {
            return $this->update();
        }
    } // ActiveObject->save()

    /**
     * Delete current object record
     *
     * @access public
     * @return bool
     */
    public function delete() {
        $pkey_size = sizeof($this->table_object->pkeys);

        if (!$this->table_object->aikey && $pkey_size == 0) {
            return false;
        } else {
            if (!$this->stat_new) {
                $where = "";
                $params = array();
                if ($this->table_object->aikey) {
                    $attr_name = $this->table_object->aikey;
                    $where .= " AND `$attr_name`=?";
                    $params[] = $this->$attr_name;
                }
                if ($pkey_size >= 1) {
                    for ($i = 0; $i < $pkey_size; $i++) {
                        $attr_name = $this->table_object->pkeys[$i];
                        $where .= " AND `$attr_name`=?";
                        $params[] = $this->$attr_name;
                    }
                }
                $where = substr($where, 4);

                $sql = "DELETE FROM `{$this->prefix}{$this->table_name}` WHERE $where";
                $rs = $this->mydb->exec_query($sql, $params);
                return $rs;
            } else {
                return false;
            }
        }
    } // ActiveObject->delete()

    /**
     * Load related objects
     *
     * @access public
     * @param array $target_objs Only load objects defined in this array
     */
    public function load_children($target_objs = array()) {
        /* the has_one relation */
        if (sizeof($this->has_one) >= 1) {
            foreach ($this->has_one as $class_name) {
                if (!empty($target_objs) &&
                    !in_array($class_name, $target_objs)) {
                    continue;
                }
                $object = new $class_name();
                if (in_array($this->class_name, $object->belong_to)) {
                    $t_class_name = transform_class_name($this->class_name);
                    $ai_attr_name = $this->table_object->aikey;
                    $this->children[$class_name] =
                        ActiveObject::find($class_name, "{$t_class_name}_id=?",
                        array($this->$ai_attr_name));
                } else {
                    $this->errmsg = 'Broken relation: '.$this->class_name
                        .' has_one '.$class_name.'!'."\n";
                }
                unset($object);
            }
        }

        /* the has_many relation */
        if (sizeof($this->has_many) >= 1) {
            foreach ($this->has_many as $class_name) {
                if (!empty($target_objs) &&
                    !in_array($class_name, $target_objs)) {
                    continue;
                }
                $object = new $class_name();
                if (in_array($this->class_name, $object->belong_to)) {
                    $t_class_name = transform_class_name($this->class_name);
                    $ai_attr_name = $this->table_object->aikey;
                    $this->children[$class_name] =
                        ActiveObject::find_all($class_name, "{$t_class_name}_id=?",
                        array($this->$ai_attr_name));
                } else if (in_array($this->class_name, $object->belong_to_many)) {
                        $t_class_name = transform_class_name($this->class_name);
                        $t_class_name_s = transform_class_name($class_name);
                        $ai_attr_name = $this->table_object->aikey;
                        $ai_attr_name_s = $object->table_object->aikey;
                        $table_name_s = $this->prefix.$object->table_name;
                        $table_name_r = $this->prefix.$t_class_name.'_'.$t_class_name_s;
                        $id_r = $t_class_name.'_id';
                        $id_r_s = $t_class_name_s.'_id';
                        $sql = "SELECT `$table_name_s`.* FROM `$table_name_s`, "
                            ."`$table_name_r` WHERE "
                            ."`$table_name_s`.`$ai_attr_name_s`=`$table_name_r`.`$id_r_s` "
                            ."AND `$table_name_r`.`$id_r`=?";

                        $rs = $this->mydb->exec_query($sql, array($this->$ai_attr_name));
                        if (!$rs) continue;
                        $this->children[$class_name] = array();
                        while ($row = $rs->fetch_object($class_name, array(false, false))) {
                            $this->children[$class_name][] = $row;
                        }
                        $rs->free();
                    } else {
                        $this->errmsg = 'Broken relation: '.$this->class_name
                            .' has_many '.$class_name.'!'."\n";
                    }
                unset($object);
            }
        }
    } // ActiveObject->load_children($target_objs = array())

    /**
     * Load related objects
     *
     * @access public
     * @param array $target_objs Only load objects defined in this array
     */
    public function load_parents($target_objs = array()) {
        /* the belong_to relation */
        if (sizeof($this->belong_to) >= 1) {
            foreach ($this->belong_to as $class_name) {
                if (!empty($target_objs) &&
                    !in_array($class_name, $target_objs)) {
                    continue;
                }
                $object = new $class_name();
                if (in_array($this->class_name, $object->has_one) ||
                    in_array($this->class_name, $object->has_many)) {
                        $t_class_name_m = transform_class_name($class_name);
                        $id_r_m = $t_class_name_m.'_id';
                        $ai_attr_name_m = $object->table_object->aikey;
                        $this->parents[$class_name] =
                            ActiveObject::find("$ai_attr_name_m=?",
                            array($this->$id_r_m));
                    } else {
                        $this->errmsg = 'Broken relation: '.$this->class_name
                            .' belong_to '.$class_name.'!'."\n";
                    }
                unset($object);
            }
        }

        /* the belong_to_many relation */
        if (sizeof($this->belong_to_many) >= 1) {
            foreach ($this->belong_to_many as $class_name) {
                if (!empty($target_objs) &&
                    !in_array($class_name, $target_objs)) {
                    continue;
                }
                $object = new $class_name();
                if (in_array($this->class_name, $object->has_many)) {
                    $t_class_name = transform_class_name($this->class_name);
                    $t_class_name_m = transform_class_name($class_name);
                    $ai_attr_name = $this->table_object->aikey;
                    $ai_attr_name_m = $object->table_object->aikey;
                    $table_name_m = $this->prefix.$object->table_name;
                    $table_name_r = $this->prefix.$t_class_name_m.'_'.$t_class_name;
                    $id_r = $t_class_name.'_id';
                    $id_r_m = $t_class_name_m.'_id';
                    $sql = "SELECT `$table_name_m`.* FROM `$table_name_m`, "
                        ."`$table_name_r` WHERE "
                        ."`$table_name_m`.`$ai_attr_name_m`=`$table_name_r`.`$id_r_m` "
                        ."AND `$table_name_r`.`$id_r`=?";

                    $rs = $this->mydb->exec_query($sql, array($this->$ai_attr_name));
                    if (!$rs) continue;
                    $this->parents[$class_name] = array();
                    while ($row = $rs->fetch_object($class_name, array(false, false))) {
                        $this->parents[$class_name][] = $row;
                    }
                    $rs->free();
                } else {
                    $this->errmsg = 'Broken relation: '.$this->class_name
                        .' belong_to_many '.$class_name.'!'."\n";
                }
                unset($object);
            }
        }
    } // ActiveObject->load_parents($target_objs = array())

    /**
     * Fix relation with children and parents
     *
     * @access public
     * @param array $target_objs Target class name along with object IDs to be fixed.
     */
    public function fix_relations($target_objs = array()) {
        if (!is_array($target_objs)) return false;
        $this->errmsg = '';

        if (sizeof($target_objs) > 0) {
            // Only fix relation with these given objects.
            foreach ($target_obje as $class_name => $obj_ids) {
                if (!$obj_ids) {
                    $this->errmsg .= 'Unable to fix relation with '.$class_name
                        .' with invalid object IDs!'."\n";
                    continue;
                }
                if (in_array($class_name, $this->has_one)) {
                    if (!is_numeric($obj_ids)) {
                        $this->errmsg .= 'Unable to fix relation with '.$class_name
                            .' with invalid object ID '.$obj_ids.'!'."\n";
                        continue;
                    }
                	$object = new $class_name($obj_ids);
	                if (in_array($this->class_name, $object->belong_to)) {
	                    $t_class_name = transform_class_name($this->class_name);
                        $id_r = $t_class_name.'_id';
	                    $ai_attr_name = $this->table_object->aikey;
	                    $object->$id_r = $this->$ai_attr_name;
	                    $object->save();
	                } else {
	                    $this->errmsg = 'Broken relation: '.$this->class_name
	                        .' has_one '.$class_name.'!'."\n";
	                }
	                unset($object);
                }
                if (in_array($class_name, $this->has_many)) {
                    if (!is_array($obj_ids)) {
                        $this->errmsg .= 'Unable to fix relation with '.$class_name
                            .' with invalid type of object IDs!'."\n";
                        continue;
                    }
                    $object = new $class_name();
                    
                    $t_class_name = transform_class_name($this->class_name);
                    $t_class_name_s = transform_class_name($class_name);
                    $id_r = $t_class_name.'_id';
                    $ai_attr_name = $this->table_object->aikey;
                    $ai_attr_name_s = $object->table_object->aikey;
                    if (sizeof($obj_ids) > 1) {
                    	$id_param = array();
                    	$s_obj_ids = "";
                    	foreach ($obj_ids as $id) {
                    	   $s_obj_ids .= ",?";
                    	   $id_param[] = $id;
                    	}
                    	$id_where = " AND `$ai_attr_name_s` IN (".substr($s_obj_ids, 1).")";
                    } else {
                    	$id_where = " AND `$ai_attr_name_s`=?";
                        $id_param = array($obj_ids[0]);
                    }
                    
	                if (in_array($this->class_name, $object->belong_to)) {
	                    $t_class_name = transform_class_name($this->class_name);
	                    $id_r = $t_class_name.'_id';
	                    $ai_attr_name = $this->table_object->aikey;
	                    foreach ($obj_ids as $obj_id) {
	                    	if (!is_numeric($obj_id)) {
	                    	    $this->errmsg .= 'Unable to fix relation with '.$class_name
                                    .' with invalid object ID '.$obj_ids.'!'."\n";
                                continue;
	                    	}
	                    	$target_obj = new $class_name($obj_id);
	                    	$target_obj->$id_r = $this->$ai_attr_name;
	                    	$target_obj->save();
	                    	unset($target_obj);
	                    }
	                } else if (in_array($this->class_name, $object->belong_to_many)) {
                        $t_class_name = transform_class_name($this->class_name);
                        $t_class_name_s = transform_class_name($class_name);
                        $ai_attr_name = $this->table_object->aikey;
                        $ai_attr_name_s = $object->table_object->aikey;
                        $table_name_s = $this->prefix.$object->table_name;
                        $table_name_r = $this->prefix.$t_class_name.'_'.$t_class_name_s;
                        $id_r = $t_class_name.'_id';
                        $id_r_s = $t_class_name_s.'_id';
                        $sql = "SELECT `$table_name_s`.* FROM `$table_name_s`, "
                            ."`$table_name_r` WHERE "
                            ."`$table_name_s`.`$ai_attr_name_s`=`$table_name_r`.`$id_r_s` "
                            ."AND `$table_name_r`.`$id_r`=?";

                        $rs = $this->mydb->exec_query($sql, array($this->$ai_attr_name));
                    } else {
                        $this->errmsg = 'Broken relation: '.$this->class_name
                            .' has_many '.$class_name.'!'."\n";
                    }
	                unset($object);
                }
                if (in_array($class_name, $this->belong_to)) {
                    
                }
                if (in_array($class_name, $this->belong_to_many)) {
                    
                }
            }
        }
    } // ActiveObject->fix_relations($target_objs = array())

    /* Static data accessor */
    /**
     * The MySQLi connection instance holder for static accessors
     *
     * @access private
     * @var object
     */
    private static $_mydb = false;

    /**
     * $prefix is the prefix of data tables for static accessors
     *
     * @access private
     * @var string
     */
    private static $_prefix = false;

    /**
     * Transfer class name to table name
     *
     * @access private
     * @static
     * @param string $class_name The name of requested class
     * @return string The translated table name
     */
    private static function _get_table_name($class_name) {
        return pluralize(transform_class_name($class_name));
    } // ActiveObject::_get_table_name($class_name)

    /**
     * Find the first matched record
     * Only first record is returned
     *
     * @access public
     * @final
     * @static
     * @param string $class_name The name of requested class
     * @param string $where The WHERE SQL used for filtering records
     * @param array $params Parameters used for replacing place holders in the WHERE SQL
     * @param string $more_sql Additional SQL conditions to sort, group or limit records selection
     * @return object
     */
    final public static function find($class_name, $where = false, $params = array(), $more_sql = false) {
        if (!self::$_mydb) self::$_mydb = MyDb::get_instance();
        if (!self::$_prefix) self::$_prefix = self::$_mydb->get_prefix();

        $table_name = self::_get_table_name($class_name);
        $sql = "SELECT * FROM `".self::$_prefix."$table_name`";
        if ($where !== false) {
            $sql .= " WHERE $where";
        }
        if ($more_sql !== false) {
            $sql .= " $more_sql";
        }
        $rs = self::$_mydb->exec_query($sql, $params);
        if (!$rs) return false;
        $object = $rs->fetch_object($class_name,
            array(false, false));
        $rs->free();
        if (NULL != $object) {
            return $object;
        } else {
            return false;
        }
    } // ActiveObject::find($class_name, where = false, $params = array(), $more_sql = false)

    /**
     * Find all matched record
     *
     * @access public
     * @final
     * @static
     * @param string $class_name The name of requested class
     * @param string $where The WHERE SQL used for filtering records
     * @param array $params Parameters used for replacing place holders in the WHERE SQL
     * @param string $more_sql Additional SQL conditions to sort, group or limit records selection
     * @return array
     */
    final public static function find_all($class_name, $where = false, $params = array(), $more_sql = false) {
        if (!self::$_mydb) self::$_mydb = MyDb::get_instance();
        if (!self::$_prefix) self::$_prefix = self::$_mydb->get_prefix();

        $objects = array();

        $table_name = self::_get_table_name($class_name);
        $sql = "SELECT * FROM `".self::$_prefix."$table_name`";
        if ($where !== false) {
            $sql .= " WHERE $where";
        }
        if ($more_sql !== false) {
            $sql .= " $more_sql";
        }
        $rs = self::$_mydb->exec_query($sql, $params);
        if (!$rs) return false;
        while ($row = $rs->fetch_object($class_name, array(false, false))) {
            $objects[] = $row;
        }
        $rs->free();
        if (sizeof($objects) > 0) {
            return $objects;
        } else {
            return false;
        }
    } // ActiveObject::find_all($class_name, $where = false, $params = array(), $more_sql = false)

    /**
     * Count record
     *
     * @access public
     * @static
     * @final
     * @param string $class_name The name of requested class
     * @param string $where The WHERE SQL used for counting records
     * @param array $params Parameters used for replacing place holders in the WHERE SQL
     * @return int
     */
    final public static function count($class_name, $where = false, $params = array()) {
        if (!self::$_mydb) self::$_mydb = MyDb::get_instance();
        if (!self::$_prefix) self::$_prefix = self::$_mydb->get_prefix();

        $table_name = self::_get_table_name($class_name);
        $sql = "SELECT COUNT(*) FROM `".self::$_prefix."$table_name`";
        if ($where !== false) {
            $sql .= " WHERE $where";
        }
        $rs = self::$_mydb->exec_query($sql, $params);
        if (!$rs) return 0;
        $row = $rs->fetch_row();
        $rs->free();
        return $row[0];
    } // ActiveObject::count($class_name, $where = false, $params = array())
    // Static data accessor

    /* Pagination */

    /**
     * The general list row count
     *
     * @access private
     * @static
     * @var integet
     */
    private static $_list_size = 15;

    /**
     * Get paginated data by class name
     *
     * @access public
     * @static
     * @final
     * @param string $class_name The class name of the object
     * @param string $where SQL condition
     * @param array $params Parameters for SQL variable substitution
     * @param string $more_sql ORDER or GROUP statements
     * @param string $page_param The name of the parameter indicates the page number
     * @return array
     */
    final public static function paginate($class_name, $where = false,
        $params = array(), $more_sql = false, $page_param = 'p') {
        self::_list_size();

        $result = array();

        $record_num = self::count($class_name, $where, $params);
        if ($record_num > 0) {
            $curr_page = get_var($page_param, false, 1);
            $result['pager'] =& self::_gen_pager_links($curr_page, $obj_cnt, $page_param);

            $start_index = self::$_list_size * ($curr_page - 1);
            if ($more_sql == false) {
                $more_sql = '';
            }
            $more_sql .= " LIMIT ".$start_index.", ".self::$_list_size;
            $result['data'] = ActiveObject::find_all($class_name, $where, $params, $more_sql);
        }

        if (sizeof($result) > 0) {
            return $result;
        } else {
            return false;
        }
    } // ActiveObject::paginate($class_name, $where = false,
      //    $params = array(), $more_sql = false, $page_param = 'p')

    /**
     * Generate record data by page based on given SQL
     *
     * @access public
     * @static
     * @final
     * @param string $sql The main SQL for query records
     * @param array $params Parameters used for replacing place holders in the SQL
     * @param string $more_sql Additional SQL conditions to sort, group or limit records selection
     * @param string $page_param The parameter name for identifying current page number
     * @return array
     */
    final public static function paginate_sql($sql, $params = array(),
        $more_sql = false, $page_param = 'p') {
        if (!self::$_mydb) self::$_mydb = MyDb::get_instance();
        self::_list_size();
        $result = array('pager' => false, 'data' => false);

        // get total record num
        $sql_part = spliti('from', $sql);
        $count_sql = "SELECT COUNT(*) FROM".$sql_part[1];
        $rs = self::$_mydb->exec_query($count_sql, $params);
        if (!$rs) {
            $rec_cnt = 0;
        } else {
            $row = $rs->fetch_row();
            $rs->free();
            $rec_cnt = $row[0];
        }

        if ($rec_cnt > 0) {
            // get current page
            $curr_page = get_var($page_param, false, 1);
            $result['pager'] =& self::_gen_pager_links($curr_page, $rec_cnt, $page_param);

            // get start index
            $start_index = self::$_list_size * ($curr_page - 1);
            // generate sql
            if ($more_sql !== false) {
                $sql .= " ".$more_sql;
            }
            $sql .= " LIMIT ".$start_index.", ".self::$_list_size;
            // get objects
            $rs = self::$_mydb->exec_query($sql, $params);
            if ($rs) {
                while ($obj = $rs->fetch_object()) {
                    $result['data'][] = $obj;
                }
                $rs->free();
            }
        }

        if (!$result['pager'] || !$result['data']) {
            return false;
        } else {
            return $result;
        }
    } // ActiveObject::paginate_sql($sql, $params = array(),
      //    $more_sql = false, $page_param = 'p')

    /**
     * Generate record data by page based on given SQL
     * and with a custom SQL for counting record number
     *
     * @access public
     * @static
     * @final
     * @param string $sql The main SQL for query records
     * @param string $count_sql The SQL for counting record number
     * @param array $params Parameters used for replacing place holders in the SQL
     * @param array $count_params Parameters used for replacing place holders in the COUNT SQL
     * @param string $more_sql Additional SQL conditions to sort, group or limit records selection
     * @param string $page_param The parameter name for identifying current page number
     * @return array
     */
    final public static function paginate_sql_count($sql, $count_sql,
        $params = array(), $count_params = array(), $more_sql = false, $page_param = 'p') {
        if (!self::$_mydb) self::$_mydb = MyDb::get_instance();
        self::_list_size();
        $result = array('pager' => false, 'data' => false);

        // get total record num
        $rs = self::$_mydb->exec_query($count_sql, $count_params);
        if (!$rs) {
            $rec_cnt = 0;
        } else {
            $row = $rs->fetch_row();
            $rs->free();
            $rec_cnt = $row[0];
        }

        if ($rec_cnt > 0) {
            // get current page
            $curr_page = get_var($page_param, false, 1);
            $result['pager'] =& self::_gen_pager_links($curr_page, $rec_cnt, $page_param);

            // get start index
            $start_index = self::$_list_size * ($curr_page - 1);
            // generate sql
            if ($more_sql !== false) {
                $sql .= " ".$more_sql;
            }
            $sql .= " LIMIT ".$start_index.", ".self::$_list_size;
            // get objects
            $rs = self::$_mydb->exec_query($sql, $params);
            if ($rs) {
                while ($obj = $rs->fetch_object()) {
                    $result['data'][] = $obj;
                }
                $rs->free();
            }
        }

        if (!$result['pager'] || !$result['data']) {
            return false;
        } else {
            return $result;
        }
    } // ActiveObject::paginate_sql_count($sql, $count_sql,
      //    $params = array(), $count_params = array(), $more_sql = false, $page_param = 'p')

    /**
     * Generate URL for page link
     *
     * @access private
     * @static
     * @param integer $curr_page The current page
     * @param integer $rec_cnt Total record number of the selection
     * @param string $page_param The parameter name for identifying current page number
     * @return array
     */
    private static function _gen_pager_links($curr_page, $rec_cnt, $page_param) {
        $pager = false;

        // get total page num
        $pages = ceil($rec_cnt / self::$_list_size);

        // correct page
        if ($pages < 1) $pages = 1;
        if ($curr_page > $pages) $curr_page = $pages;
        if ($curr_page < 1) $curr_page = 1;
        // get all pages
        $prev_page = ($curr_page == 1)?1:$curr_page - 1;
        $next_page = ($curr_page == $pages)?$pages:$curr_page + 1;
        // set pages
        $pager['first'] = self::_gen_pager_uri(1, $page_param);
        $pager['prev'] = self::_gen_pager_uri($prev_page, $page_param);
        $pager['next'] = self::_gen_pager_uri($next_page, $page_param);
        $pager['last'] = self::_gen_pager_uri($pages, $page_param);
        $pager['curr'] = $curr_page;
        $pager['total'] = $pages;

        return $pager;
    } // ActiveObject::_gen_pager_links($curr_page, $rec_cnt, $page_param)

    /**
     * Generate URL query string
     *
     * @access private
     * @static
     * @param <type> $page
     * @param <type> $page_param
     * @return string
     */
    private static function _gen_pager_uri($page, $page_param) {
        $uri = 'index.php?'.$page_param.'='.$page;

        if (isset($_GET)){
            foreach ($_GET as $key => $value){
                if ($key != $page_param){
                    $uri .= '&amp;'.$key.'='.urlencode($value);
                }
            }
        }
        if (isset($_POST)){
            foreach ($_POST as $key => $value){
                if ($key != $page_param){
                    $uri .= '&amp;'.$key.'='.urlencode($value);
                }
            }
        }

        return $uri;
    } // ActiveObject::_gen_pager_uri($page, $page_param)

    /**
     * Initialize list size
     */
    private static function _list_size() {
        $global_sys_configs =& $GLOBALS['sys_configs'];
        if (isset($global_sys_configs['list_size']) &&
            is_numeric($global_sys_configs['list_size'])) {
            self::$_list_size = intval($global_sys_configs['list_size']);
        }
    }
    // Pagination
}
