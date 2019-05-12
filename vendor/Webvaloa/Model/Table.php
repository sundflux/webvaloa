<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.io>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2019 Tarmo Alexander Sundström <ta@sundstrom.io>
 *
 * All Rights Reserved.
 *
 * Contributor(s):
 *
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

namespace Webvaloa\Model;

use RuntimeException;
use Libvaloa\Debug\Debug;

/**
 * Class Table
 * @package Webvaloa
 */
class Table
{
    private $table;
    private $fields = [];
    private $model;
    private $index;
    private $schema;

    public function __construct($model)
    {
        $this->model = $model;
    }

    private function parseTableFromModel()
    {
        if (empty($this->table)) {
            reset($this->model);
            $this->table = key($this->model);
        }

        return $this->table;
    }

    private function parseFieldsFromModel()
    {
        $modelName = $this->parseTableFromModel();

        foreach ($this->model[$modelName] as $fieldName => $fieldSchema) {
            if (is_array($fieldSchema)) {
                $_fieldSchema = implode(' ', $fieldSchema);
            } elseif (is_string($fieldSchema)) {
                $_fieldSchema = $fieldSchema;
            } else {
                throw new RuntimeException('Could not find field schema for model.');
            }

            $this->fields[$fieldName] = $_fieldSchema;
        }
    }

    private function parseIndexesFromModel()
    {
        if (isset($this->model['index'])) {
            $this->index = $this->model['index'];
            unset($this->model['index']);
        }
    }

    private function parseSchema()
    {
        $schema = 'CREATE TABLE IF NOT EXISTS `' . $this->parseTableFromModel() . '` (' . "\n";

        foreach ($this->fields as $field => $def) {
            $schema .= '  `'.$field.'` ' . $def . ',' . "\n";
        }

        if (!empty($this->index)) {
            foreach ($this->index as $index) {
                $schema .= '  KEY ' . $index . ',' . "\n";
            }
        }

        rtrim($schema, ',');

        $schema.= ') ENGINE=InnoDB  DEFAULT CHARSET=utf8;'. "\n";
        Debug::__print($schema);

        $this->schema = $schema;
    }

    public function getSchema()
    {
        if (empty($this->schema)) {
            $this->parseTableFromModel();
            $this->parseIndexesFromModel();
            $this->parseFieldsFromModel();
            $this->parseSchema();
        }

        Debug::__print($this->table);
        Debug::__print($this->fields);

        return $this->schema;
    }

    public function create()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();
        $db->query($this->getSchema);
    }
}
