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

namespace Webvaloa\Helpers;

use stdClass;
use Exception;
use RuntimeException;

class Article
{
    public $article;

    public function __construct($id = false)
    {
        if (!is_numeric($id)) {
            throw new Exception();
        }

        $this->article = new stdClass();
        $this->article->id = (int) $id;
        $this->loadArticle();
        $this->loadFields();
    }

    public function loadArticle()
    {
        if (!is_numeric($this->article->id)) {
            throw new RuntimeException('Article '.$this->article->id.' not loadable');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = "
            SELECT *
            FROM content
            WHERE id = ?
            AND published > 0
            AND publish_up < NOW()
            AND (publish_down < NOW() or publish_down = '0000-00-00 00:00:00') ";

        $stmt = $db->prepare($query);
        $stmt->set((int) $this->article->id);

        try {
            $stmt->execute();
            $this->article = $stmt->fetch();
        } catch (Exception $e) {

        }
    }

    public function loadFields()
    {
        if ((!isset($this->article->id) || !is_numeric($this->article->id) || empty($this->article->id)) // Regular article check
            && (isset($this->article->id) && $this->article->id != 0)) {
            // Global fields

            throw new RuntimeException('Article not loadable');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT
                field.id as field_id,
                field.name as name,
                field.type as type,
                field.field_group_id as field_group_id,
                content_field_value.id as content_field_value_id,
                content_field_value.value as value,
                content_field_value.locale as locale,
                content_field_value.ordering as repeatable_ordering

            FROM
                field,
                content_field_value

            WHERE
                field.id = content_field_value.field_id
                AND content_field_value.locale = ?
                AND content_field_value.content_id = ?
                ORDER BY content_field_value.id ASC';

        $stmt = $db->prepare($query);
        $stmt->set(\Webvaloa\Webvaloa::getLocale());

        // global fields
        if (!isset($this->article->id)) {
            $this->article = new stdClass();
            $this->article->id = 0;
        }

        $stmt->set((int) $this->article->id);

        try {
            $stmt->execute();
            $fields = $stmt->fetchAll();

            // Sort fields by group
            foreach ($fields as $k => $field) {

                // Format field value for viewing
                $fieldClass = '\Webvaloa\Field\Fields\\'.$field->type;
                $f = new $fieldClass($field->field_id);
                $m = 'onLoad';
                if (method_exists($f, $m)) {
                    $field->value = $f->{$m}($field->value);
                }

                $values[$field->name][] = $field->value;
            }

            if (isset($values)) {
                $this->article->fieldValues = (object) $values;
            }

            if (!isset($this->article->fieldValues)) {
                $this->article->fieldValues = array();
            }
        } catch (Exception $e) {

        }
    }

    public static function tags()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = "
            SELECT DISTINCT
                content_field_value.value as tag

            FROM
                content_field_value,
                field

            WHERE
                field.id = content_field_value.field_id
                AND field.type = 'Tags'
                AND content_field_value.value != ''

            ORDER BY
                tag";

        $stmt = $db->prepare($query);

        try {
            $stmt->execute();
            foreach ($stmt as $row) {
                $tags[] = $row->tag;
            }

            if (isset($tags)) {
                return $tags;
            }
        } catch (Exception $e) {
        }

        return array();
    }
}
