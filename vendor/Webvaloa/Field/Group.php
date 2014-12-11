<?php
/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>
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

namespace Webvaloa\Field;

use Libvaloa\Db;
use Webvaloa\Cache;
use RuntimeException;

class Group
{

    private $groupID;
    private $object;
    private $cache;

    public function __construct($groupID = false)
    {
        $this->cache = new Cache();

        $this->object = new Db\Object('field_group', \Webvaloa\Webvaloa::DBConnection());
        $this->groupID = $groupID;

        if ($this->groupID) {
            $this->object->byID($this->groupID);
        }
    }

    public function __set($k, $v)
    {
        $this->object->$k = $v;
    }

    public function __get($k)
    {
        try {
            $v = $this->object->$k;

            return $v;
        } catch (OutOfBoundsException $e) {
            return false;
        }
    }

    public function save()
    {
        return $this->object->save();
    }

    public function delete()
    {
        // Delete fields
        $fields = $this->fields();
        foreach ($fields as $k => $v) {
            $this->deleteField($v->id);
        }

        // Delete group from categories
        $this->deleteFromCategories();

        // Delete group
        return $this->object->delete();
    }

    public function deleteField($fieldID)
    {
        if (!$this->groupID) {
            throw new RuntimeException('GroupID must be set before running deleteField');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = "
            DELETE FROM field
            WHERE field_group_id = ?
            AND id = ?";

        $stmt = $db->prepare($query);
        $stmt->set((int) $this->groupID);
        $stmt->set((int) $fieldID);

        try {
            $stmt->execute();

            return true;
        } catch (Exception $e) {

        }
    }

    public function deleteFromCategories()
    {
        if (!$this->groupID) {
            throw new RuntimeException('GroupID must be set before running deleteFromCategories');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = "
            DELETE FROM category_field_group
            WHERE field_group_id = ?";

        $stmt = $db->prepare($query);
        $stmt->set((int) $this->groupID);

        try {
            $stmt->execute();

            return true;
        } catch (Exception $e) {

        }
    }

    public function addCategory($category_id)
    {
        if (!$this->groupID) {
            throw new RuntimeException('GroupID must be set before running addCategory');
        }

        $object = new Db\Object('category_field_group', \Webvaloa\Webvaloa::DBConnection());
        $object->field_group_id = $this->groupID;
        $object->category_id = $category_id;
        $object->recursive = 0;
        $object->save();
    }

    public function categories()
    {
        if (!$this->groupID) {
            throw new RuntimeException('GroupID must be set before running categories');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = "
            SELECT category_id
            FROM category_field_group
            WHERE field_group_id = ?";

        $stmt = $db->prepare($query);
        $stmt->set((int) $this->groupID);

        try {
            $stmt->execute();

            foreach ($stmt as $row) {
                $categories[] = $row->category_id;
            }

            if (isset($categories)) {
                return $categories;
            }

            return array();
        } catch (Exception $e) {

        }
    }

    public function groups()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = "
            SELECT *
            FROM field_group
            ORDER BY name";

        $stmt = $db->prepare($query);

        try {
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {

        }
    }

    public function globals()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = "
            SELECT *
            FROM field_group
            WHERE global = 1
            ORDER BY name";

        $stmt = $db->prepare($query);

        try {
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {

        }
    }

    public function fields()
    {
        if (!$this->groupID) {
            throw new RuntimeException('GroupID must be set before running fields');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = "
            SELECT *
            FROM field
            WHERE field_group_id = ?
            ORDER BY ordering ASC";

        $stmt = $db->prepare($query);
        $stmt->set((int) $this->groupID);

        try {
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {

        }
    }

}
