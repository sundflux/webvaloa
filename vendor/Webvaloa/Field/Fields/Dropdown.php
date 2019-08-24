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

namespace Webvaloa\Field\Fields;

use DOMDocument;
use stdClass;
use Libvaloa\Debug\Debug;
use Webvaloa\Field\Field;
use Webvaloa\Field\Value;

/**
 * Class Dropdown.
 */
class Dropdown
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
     * Dropdown constructor.
     *
     * @param bool $fieldID
     */
    public function __construct($fieldID = false)
    {
        $this->fieldID = $fieldID;

        if (is_numeric($this->fieldID)) {
            $this->field = new Field($this->fieldID);
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
            'Dropdown',
        );
    }

    /**
     * @return array
     */
    public function getParams()
    {
        $values = '';
        if (isset($this->fieldID) && is_numeric($this->fieldID)) {
            $values = $this->field->settings;
            if (!empty($values)) {
                $values = (array) json_decode($values);

                Debug::__print('Dropdown values:');
                foreach ($values as $k => $v) {
                    Debug::__print($k);
                    Debug::__print($v);

                    if ($k == 'value') {
                        $keys = $v;
                    }
                    if ($k == 'text') {
                        $vals = $v;
                    }
                }
            }
        }

        if (isset($keys)) {
            $value = new Value();
            $values = $value->getValues($this->fieldID);

            foreach ($keys as $k => $v) {
                $d = new stdClass();
                $d->key = $keys[$k];
                $d->value = $vals[$k];

                if (isset($values[0]->value) && $values[0]->value == $d->key) {
                    $d->selected = 'selected';
                }

                $retval[] = $d;
            }

            if (isset($retval)) {
                return $retval;
            }
        }

        return array();
    }

    /**
     * @return string
     */
    public function getSettings()
    {
        $dom = new DOMDocument();

        $values = '';
        if (isset($this->fieldID) && is_numeric($this->fieldID)) {
            $values = $this->field->settings;
            if (!empty($values)) {
                $values = (array) json_decode($values);

                Debug::__print('Dropdown values:');
                foreach ($values as $k => $v) {
                    Debug::__print($k);
                    Debug::__print($v);

                    if ($k == 'value') {
                        $keys = $v;
                    }
                    if ($k == 'text') {
                        $vals = $v;
                    }
                }
            }
        }

        if (isset($keys)) {
            Debug::__print('Keys:');
            Debug::__print($keys);
        } else {
            $keys[0] = '';
        }

        if (isset($vals)) {
            Debug::__print('Values:');
            Debug::__print($vals);
        } else {
            $vals[0] = '';
        }

        // Title
        $span = $dom->createElement('span', \Webvaloa\Webvaloa::translate('ENTER_VALUES', 'FieldDropdown'));
        $dom->appendChild($span);
        $br = $dom->createElement('br');
        $dom->appendChild($br);

        // Dropdown value row:

        $i = 0;
        foreach ($keys as $k => $v) {
            $opt = $dom->createElement('div');
            $opt->setAttribute('class', 'dropdown-row');

            $in = $dom->createElement('input');
            $in->setAttribute('type', 'text');
            $in->setAttribute('placeholder', \Webvaloa\Webvaloa::translate('VALUE', 'FieldDropdown'));
            $in->setAttribute('class', 'form-control pull-left');
            $in->setAttribute('style', 'max-width: 45%');
            $in->setAttribute('name', 'DropdownSettings[value][]');

            if (isset($keys[$k])) {
                $in->setAttribute('value', $keys[$k]);
            }

            $opt->appendChild($in);

            $in = $dom->createElement('input');
            $in->setAttribute('type', 'text');
            $in->setAttribute('placeholder', \Webvaloa\Webvaloa::translate('TEXT', 'FieldDropdown'));
            $in->setAttribute('class', 'form-control pull-left');
            $in->setAttribute('style', 'max-width: 45%');
            $in->setAttribute('name', 'DropdownSettings[text][]');

            if (isset($vals[$k])) {
                $in->setAttribute('value', $vals[$k]);
            }

            $opt->appendChild($in);

            // Remove button
            $in = $dom->createElement('button');
            $in->setAttribute('type', 'button');

            if ($i == 0) {
                $in->setAttribute('class', 'btn btn-default dropdown-delete-row hidden pull-left');
            } else {
                $in->setAttribute('class', 'btn btn-default dropdown-delete-row pull-left');
            }

            // Font awesome minus icon
            $plus = $dom->createElement('i');
            $plus->setAttribute('class', 'fa fa-minus');
            $in->appendChild($plus);

            // Add button
            $opt->appendChild($in);

            // <br>, end of row
            $br = $dom->createElement('br');
            $opt->appendChild($br);

            // Add whole thing to dom
            $dom->appendChild($opt);

            ++$i;
        }

        // <br>, end of row
        $br = $dom->createElement('br');
        $dom->appendChild($br);

        $buttondiv = $dom->createElement('div');
        $buttondiv->setAttribute('class', 'add-new-dropdown-row');

        $br = $dom->createElement('br');
        $buttondiv->appendChild($br);

        // Add button
        $in = $dom->createElement('button');
        $in->setAttribute('type', 'button');
        $in->setAttribute('id', 'add-dropdown-row');
        $in->setAttribute('class', 'btn btn-success');

        // Font awesome plus icon
        $plus = $dom->createElement('i');
        $plus->setAttribute('class', 'fa fa-plus');
        $in->appendChild($plus);

        // Add button
        $buttondiv->appendChild($in);
        $dom->appendChild($buttondiv);

        // JS
        $js = $dom->createElement('script', file_get_contents(dirname(__FILE__).'/Dropdown.js'));
        $js->setAttribute('type', 'text/javascript');
        $dom->appendChild($js);

        // Return settings html
        return $dom->saveHTML();
    }
}
