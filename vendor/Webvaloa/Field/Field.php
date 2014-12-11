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
use Libvaloa\Debug;
use stdClass;

class Field
{

    private $object;
    private $fieldID;
    private $contentID;

    public $fields = array();

    public function __construct($fieldID = false, $contentID = false)
    {
        $this->object = new Db\Object('field', \Webvaloa\Webvaloa::DBConnection());
        $this->fieldID = $fieldID;

        if ($this->fieldID) {
            $this->object->byID($this->fieldID);
        }
    }

    public function __set($k, $v)
    {
        $this->object->$k = $v;
    }

    public function __get($k)
    {
        return $this->object->$k;
    }

    public function save()
    {
        return $this->object->save();
    }

    public function delete()
    {
        return $this->object->delete();
    }

    public function findByName($name)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = "
            SELECT *
            FROM field
            WHERE name = ?
            LIMIT 1";

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

    public function fields()
    {
        $search[] = LIBVALOA_INSTALLPATH . "/Webvaloa/Field/Fields";
        $search[] = LIBVALOA_EXTENSIONSPATH . "/Webvaloa/Field/Fields";

        foreach ($search as $path) {
            Debug::__print($path);
            if ($handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {
                    if (substr($entry, -4) == ".php") {
                        $this->fields[] = substr($entry, 0, -4);
                    }
                }
                closedir($handle);
            }
        }

        $this->fields = array_unique($this->fields);

        Debug::__print('Available fields:');
        Debug::__print($this->fields);

        return $this->fields;
    }

    public function fieldSettings()
    {
        $fields = $this->fields();

        foreach ($fields as $k => $v) {
            $fieldClass = '\Webvaloa\Field\Fields\\' . $v;

            $f = new $fieldClass();

            // Load field by id instead to get settings
            if (isset($this->fieldID) && is_numeric($this->fieldID)) {
                $tmp = $this->object->type;
                if ($tmp == $v) {
                    $f = new $fieldClass($this->fieldID);
                }
            }

            $fieldSettings = new stdClass;
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
