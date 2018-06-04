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
use Webvaloa\Field\Value;

/**
 * Class Tags
 * @package Webvaloa\Field\Fields
 */
class Tags
{
    /**
     * @var
     */
    private $field;

    /**
     * @var int|string
     */
    private $fieldID;

    /**
     * @var int|string
     */
    private $contentID;

    /**
     * @var
     */
    private $ordering;

    /**
     * Tags constructor.
     * @param bool $fieldID
     * @param bool $contentID
     */
    public function __construct($fieldID = false, $contentID = false)
    {
        $this->ordering = false;

        if (strstr($contentID, ':')) {
            $parts = explode(':', $contentID);
            $contentID = $parts[0];
            $this->ordering = $parts[1];
        }

        if (is_numeric($fieldID)) {
            $this->fieldID = $fieldID;
        }

        if (is_numeric($contentID)) {
            $this->contentID = $contentID;
        }
    }

    /**
     * @return array
     */
    public function getJS()
    {
        return array(
            '/jquery/plugins/jquery.typeahead.js',
        );
    }

    /**
     * @return array
     */
    public function getCSS()
    {
        return array(
            '/css/Tags.css',
            '/css/Typeahead.css',
        );
    }

    /**
     * @return array
     */
    public function getTemplate()
    {
        return array(
            'Tags',
        );
    }

    /**
     * @return array
     */
    public function getParams()
    {
        if ($this->fieldID == false || $this->contentID == false) {
            return array();
        }

        if ($this->ordering) {
            $this->contentID = $this->contentID.':'.$this->ordering;
        }

        $value = new Value($this->contentID);
        $values = $value->getValues($this->fieldID);

        if (!is_array($values) && !is_object($values)) {
            return array();
        }

        foreach ($values as $k => $v) {
            if (empty($v)) {
                continue;
            }

            $tag = new stdClass();
            $tag->key = $k;
            $tag->value = $v;
            $retval[] = $tag;
        }

        if (isset($retval)) {
            return $retval;
        }

        return array();
    }

    /**
     * @return string
     */
    public function getSettings()
    {
        $dom = new DOMDocument();

        // JS
        $js = $dom->createElement('script', file_get_contents(dirname(__FILE__).'/Tags.js'));
        $js->setAttribute('type', 'text/javascript');
        $dom->appendChild($js);

        // Return settings html
        return $dom->saveHTML();
    }
}
