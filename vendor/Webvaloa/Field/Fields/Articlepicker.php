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
use DOMDocument;
use Webvaloa\Article;
use Webvaloa\Category;
use Webvaloa\Field\Field;
use Webvaloa\Field\Value;
use Libvaloa\Debug;

class Articlepicker
{
    private $field;

    private $fieldID;
    private $contentID;

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

    public function getJS()
    {
        return array();
    }

    public function getCSS()
    {
        return array();
    }

    public function getTemplate()
    {
        return array(
            'Articlepicker',
        );
    }

    public function getParams()
    {
        // Selected category from settings
        if (isset($this->fieldID) && is_numeric($this->fieldID)) {
            $tmp = $this->field->settings;
            if (!empty($tmp)) {
                $tmp = json_decode($tmp);
                if (isset($tmp->category)) {
                    $categoryID = $tmp->category;
                }
            }

            $value = new Value();
            $values = $value->getValues($this->fieldID);
        }

        // Get articles from this category
        if (isset($categoryID)) {
            $article = new Article(0);
            $articles = $article->getArticles($categoryID);
            foreach ($articles as $k => $v) {
                $a = new stdClass();
                $a->id = $v->id;
                $a->title = $v->title;

                if (isset($values[0]->value) && $values[0]->value == $v->id) {
                    $a->selected = 'selected';
                }

                $retval[] = $a;
            }

            if (isset($retval)) {
                return (object) $retval;
            }
        }

        return array();
    }

    public function getSettings()
    {
        $dom = new DOMDocument();
        $category = new Category();
        $categories = $category->categories();

        // Selected category from settings
        $selected = '';
        if (isset($this->fieldID) && is_numeric($this->fieldID)) {
            $tmp = $this->field->settings;
            if (!empty($tmp)) {
                $tmp = json_decode($tmp);
                if (isset($tmp->category)) {
                    $selected = $tmp->category;
                }
            }
        }

        // Title
        $span = $dom->createElement('span', \Webvaloa\Webvaloa::translate('SELECT_CATEGORY', 'FieldArticlepicker'));
        $dom->appendChild($span);
        $br = $dom->createElement('br');
        $dom->appendChild($br);

        // Create <select>
        $el = $dom->createElement('select');
        $el->setAttribute('name', 'ArticlepickerSettings[category]');
        $el->setAttribute('class', 'form-control');

        // Create an empty <option>
        $opt = $dom->createElement('option', '');
        $opt->setAttribute('value', '');
        $el->appendChild($opt);

        // Create <option>s
        foreach ($categories as $k => $v) {
            $opt = $dom->createElement('option', $v->category);
            $opt->setAttribute('value', $v->id);
            if ($v->id == $selected) {
                $opt->setAttribute('selected', 'selected');
            }

            $el->appendChild($opt);
        }

        // Append <select> to DOM tree
        $dom->appendChild($el);

        // Return settings html
        return $dom->saveHTML();
    }
}
