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

namespace Webvaloa\Field;

use Libvaloa\Db;
use Libvaloa\Debug\Debug;
use Webvaloa\Helpers\Filesystem;
use Webvaloa\Helpers\Path;
use stdClass;

/**
 * Class Field
 * @package Webvaloa\Field
 */
class Field
{
    /**
     * @var Db\Item
     */
    private $object;

    /**
     * @var bool
     */
    private $fieldID;

    /**
     * @var
     */
    private $contentID;

    /**
     * @var Path
     */
    private $pathHelper;

    /**
     * @var array
     */
    public $fields = array();

    /**
     * Field constructor.
     * @param bool $fieldID
     * @param bool $contentID
     */
    public function __construct($fieldID = false, $contentID = false)
    {
        $this->pathHelper = new Path();
        $this->object = new Db\Item('field', \Webvaloa\Webvaloa::DBConnection());
        $this->fieldID = $fieldID;

        if ($this->fieldID) {
            $this->object->byID($this->fieldID);
        }
    }

    /**
     * @param $k
     * @param $v
     */
    public function __set($k, $v)
    {
        $this->object->$k = $v;
    }

    /**
     * @param $k
     * @return null|string
     */
    public function __get($k)
    {
        return $this->object->$k;
    }

    /**
     * @return mixed
     */
    public function save()
    {
        return $this->object->save();
    }

    /**
     *
     */
    public function delete()
    {
        return $this->object->delete();
    }

    /**
     * @param $name
     * @return bool
     */
    public function findByName($name)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT *
            FROM field
            WHERE name = ?
            LIMIT 1';

        $stmt = $db->prepare($query);
        $stmt->set($name);

        try {
            $stmt->execute();

            $f = $stmt->fetch();
            if (isset($f->id)) {
                return $f;
            }

            // Field with this name not found
            return false;
        } catch (Exception $e) {
        }
    }

    /**
     * @return array
     */
    public function fields()
    {
        foreach ($this->pathHelper->getSystemPaths() as $path) {
            try {
                $fs = new Filesystem($path . '/Webvaloa/Field/Fields');
                $files = $fs->files();

                if (is_array($files)) {
                    foreach ($files as $file) {
                        if (substr($file->filename, -4) != '.php') {
                            continue;
                        }
                        $field = substr($file->filename, 0, -4);
                        $this->fields[$field] = $field;
                    }
                }
            } catch (\RuntimeException $e) {
                Debug::__print($e->getMessage());
                Debug::__print($path);
            }
        }

        Debug::__print('Available fields:');
        Debug::__print($this->fields);

        return $this->fields;
    }

    /**
     * @return array|bool
     */
    public function fieldSettings()
    {
        $fields = $this->fields();

        foreach ($fields as $k => $v) {
            $fieldClass = '\Webvaloa\Field\Fields\\'.$v;

            $f = new $fieldClass();

            // Load field by id instead to get settings
            if (isset($this->fieldID) && is_numeric($this->fieldID)) {
                $tmp = $this->object->type;
                if ($tmp == $v) {
                    $f = new $fieldClass($this->fieldID);
                }
            }

            $fieldSettings = new stdClass();
            $fieldSettings->field = $v;
            $fieldSettings->settings = $f->getSettings();
            $retval[] = $fieldSettings;
        }

        if (isset($retval)) {
            return $retval;
        }

        return false;
    }
}
