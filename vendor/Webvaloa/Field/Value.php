<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.io>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2014 Tarmo Alexander Sundström <ta@sundstrom.io>
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
use UnexpectedValueException;

/**
 * Class Value.
 */
class Value
{
    /**
     * @var bool
     */
    private $articleID;

    /**
     * @var bool
     */
    private $fieldID;

    /**
     * @var bool
     */
    private $fieldValue;

    /**
     * @var bool
     */
    private $fieldLocale;

    /**
     * @var
     */
    private $fieldOrdering;

    /**
     * Value constructor.
     *
     * @param bool $articleID
     */
    public function __construct($articleID = false)
    {
        $this->fieldID = false;
        $this->fieldValue = false;
        $this->fieldLocale = false;
        $this->fieldOrdering = 0;

        if (strstr($articleID, ':')) {
            $parts = explode(':', $articleID);
            $articleID = $parts[0];
            $this->fieldOrdering = $parts[1];
        }

        $this->articleID = $articleID;
    }

    /**
     *
     */
    public function dropValues()
    {
        if (!is_numeric($this->articleID)) {
            throw new UnexpectedValueException('Expected ArticleID ');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            DELETE FROM content_field_value
            WHERE content_id = ?';

        if ($this->fieldLocale !== false) {
            $query .= ' AND locale = ? ';
        }

        $stmt = $db->prepare($query);
        $stmt->set((int) $this->articleID);

        if ($this->fieldLocale !== false) {
            $stmt->set($this->fieldLocale);
        }

        try {
            $stmt->execute();
        } catch (Exception $e) {
            Debug::__print($e->getMessage());
        }
    }

    /**
     * @param $fieldID
     */
    public function fieldID($fieldID)
    {
        $this->fieldID = $fieldID;
    }

    /**
     * @param $value
     */
    public function fieldValue($value)
    {
        if ($this->fieldID) {
            $value = $this->onSave($value);
        }

        $this->fieldValue = $value;
    }

    /**
     * @param $locale
     */
    public function fieldLocale($locale)
    {
        $this->fieldLocale = $locale;
    }

    /**
     * @param $ordering
     */
    public function fieldOrdering($ordering)
    {
        $this->fieldOrdering = $ordering;
    }

    /**
     *
     */
    public function insert()
    {
        if (!is_numeric($this->articleID)) {
            throw new UnexpectedValueException('Expected ArticleID ');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        foreach ($this->fieldValue as $k => $v) {
            $object = new Db\Item('content_field_value', \Webvaloa\Webvaloa::DBConnection());
            $object->content_id = $this->articleID;
            $object->field_id = $this->fieldID;
            $object->value = $v;
            $object->locale = $this->fieldLocale;
            $object->ordering = $this->fieldOrdering;
            $object->save();
        }
    }

    /**
     * @param $field_id
     *
     * @return array|bool
     */
    public function getValues($field_id)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        if (is_numeric($this->articleID)) {
            $query = '
                SELECT *
                FROM content_field_value
                WHERE field_id = ?
                AND content_id = ?';
        } else {
            $query = '
                SELECT *
                FROM content_field_value
                WHERE field_id = ?';
        }

        if ($this->fieldOrdering !== false) {
            $query .= ' AND ordering = ? ';
        }

        if ($this->fieldLocale !== false) {
            $query .= ' AND locale = ? ';
        }

        $stmt = $db->prepare($query);

        try {
            $stmt->set((int) $field_id);

            if (is_numeric($this->articleID)) {
                $stmt->set((int) $this->articleID);
            }

            if ($this->fieldOrdering !== false) {
                $stmt->set((int) $this->fieldOrdering);
            }

            if ($this->fieldLocale !== false) {
                $stmt->set($this->fieldLocale);
            }

            $stmt->execute();

            foreach ($stmt as $row) {
                $values[] = $row;
            }

            if (isset($values)) {
                return $values;
            }

            return false;
        } catch (Exception $e) {
        }

        return false;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    private function onSave($value)
    {
        if (!$this->fieldID) {
            return $value;
        }

        $field = new Field($this->fieldID);
        $fieldClass = '\Webvaloa\Field\Fields\\'.$field->type;
        $f = new $fieldClass($field->id);

        $m = 'onSave';
        if (method_exists($f, $m)) {
            $value = $f->{$m}($value);
        }

        return $value;
    }
}
