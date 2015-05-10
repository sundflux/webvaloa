<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2014 Tarmo Alexander Sundström <ta@sundstrom.im>
 *
 * All Rights Reserved.
 *
 * Contributor(s):
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

namespace Webvaloa;

use Libvaloa\Db;
use stdClass;

/**
 * Set configuration variables to DB.
 */
class Configuration
{
    private $db;
    private $config;
    private $componentID;
    private $reload;

    public function __construct($component = false)
    {
        $this->reload = false;
        $this->config = false;
        $this->componentID = null;
        $this->db = \Webvaloa\Webvaloa::DBConnection();

        if ($component) {
            $tmp = new Component($component);
            $this->componentID = $tmp->id;
        }
    }

    /**
     * Insert a configuration key/value to DB.
     *
     * @param mixed $k
     * @param mixed $v
     */
    public function __set($k, $v)
    {
        if (!$this->config) {
            $this->loadConfiguration();
        }

        // Check that configuration key exists
        $conf = $this->configuration();

        if (is_array($conf) || is_object($conf)) {
            foreach ($conf as $tmp => $c) {
                $keys[] = $c->key;
            }
        }

        if (isset($keys) && !in_array($k, $keys)) {
            return false;
        }

        try {
            if (isset($this->config->$k)) {
                $item = $this->config->$k;

                // Edit existing config row
                $object = new Db\Object('configuration', $this->db);
                $object->byID($item->id);
                $object->value = $v;
                $object->save();
            } else {
                // Insert new config row
                $object = new Db\Object('configuration', $this->db);
                $object->key = $k;
                $object->value = $v;

                // TODO: type should support more types than just text
                $object->type = 'text';
                $object->component_id = $this->componentID;
                $object->save();
            }
        } catch (PDOException $e) {
        }
    }

    /**
     * Get a configuration variable.
     *
     * @param mixed $k
     *
     * @return mixed
     */
    public function __get($k)
    {
        if (!$this->config) {
            $this->loadConfiguration();
        }

        if (isset($this->config->$k) && !empty($this->config->$k)) {
            return $this->config->$k;
        }

        return false;
    }

    /**
     * Load all configuration variables.
     *
     * @return type
     */
    public function loadConfiguration()
    {
        $name = 'config'.(int) $this->componentID;

        $query = '
            SELECT *
            FROM configuration';

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute();

            foreach ($stmt as $row) {
                if ($this->componentID) {
                    if ($this->componentID != $row->component_id) {
                        continue;
                    }
                }

                $k = $row->key;

                if (!$this->config) {
                    $this->config = new stdClass();
                }

                if (!empty($row->values)) {
                    $tmp = explode(',', $row->values);
                    unset($row->values);

                    foreach ($tmp as $c => $v) {
                        $option = new stdClass();
                        $option->value = $v;
                        $option->translation = 'SETTING_'.strtoupper($v);
                        $row->values[] = (object) $option;
                    }
                }

                $this->config->$k = $row;
            }

            return $this->config;
        } catch (Exception $e) {
        }
    }

    public function delete($id = false)
    {
        if (!$this->componentID || !is_numeric($this->componentID)) {
            return false;
        }

        $query = '
            DELETE FROM configuration
            WHERE component_id = ?';

        // Delete only a single item
        if ($id && is_numeric($id)) {
            $query .= ' AND id = ?';
        }

        try {
            $stmt = $this->db->prepare($query);

            $stmt->set($this->componentID);
            if ($id && is_numeric($id)) {
                $stmt->set($id);
            }

            $stmt->execute();
        } catch (Exception $e) {
        }
    }

    public function configuration()
    {
        if (!$this->config) {
            $this->loadConfiguration();
        }

        return $this->config;
    }
}
