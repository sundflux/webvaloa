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

namespace Webvaloa\Field\Fields;

use stdClass;
use Webvaloa\Field\Field;
use Webvaloa\Field\Value;
use Libvaloa\Debug\Debug;

/**
 * Class Datalist
 *
 * @package Webvaloa\Field\Fields
 */
class Datalist
{
    /**
     * @var stdClass
     */
    private $field;

    /**
     * @var bool
     */
    private $fieldID;

    /**
     * @var
     */
    private $contentID;

    /**
     * Datalist constructor.
     *
     * @param bool $fieldID
     * @param bool $contentID
     */
    public function __construct($fieldID = false, $contentID = false)
    {
        $this->fieldID = $fieldID;

        if (is_numeric($this->fieldID)) {
            $this->field = new Field($this->fieldID);

            Debug::__print('Loaded field '.$this->fieldID);
            Debug::__print($this->field);
        } else {
            $this->field = new stdClass();
        }
    }

    /**
     * @return array
     */
    public function getJS()
    {
        return array();
    }

    /**
     * @return array
     */
    public function getCSS()
    {
        return array();
    }

    /**
     * @return array
     */
    public function getTemplate()
    {
        return array(
            'Datalist',
        );
    }

    /**
     * @return array|bool
     */
    public function getParams()
    {
        if (isset($this->fieldID) && is_numeric($this->fieldID)) {
            // Get values for this field
            $db = \Webvaloa\Webvaloa::DBConnection();

            $query = '
            SELECT *
            FROM content_field_value
            WHERE field_id = ?
            GROUP BY value';

            $stmt = $db->prepare($query);
            $stmt->set($this->fieldID);

            try {
                $stmt->execute();

                $f = $stmt->fetchAll();
                foreach ($f as $k => $v) {
                    $a = new stdClass();
                    $a->id = $v->id;
                    $a->title = $v->value;

                    if (!empty($a->title)) {
                        $retval[] = $a;
                    }
                }

                if (isset($retval)) {
                    return $retval;
                }

                return false;
            } catch (Exception $e) {
            }
        }

        return array();
    }

    /**
     *
     */
    public function getSettings()
    {
    }
}
