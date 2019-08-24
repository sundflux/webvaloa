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

/**
 * Class Navigation.
 */
class Navigation
{
    /**
     * @var
     */
    private $navi;

    /**
     * Navigation constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return stdClass
     */
    public function get()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $this->navi = new stdClass();
        $this->navi->sub = array();

        $query = '
            SELECT structure.id AS id, parent_id, structure.alias as alias, type, target_id, structure.translation,
                CASE
                    WHEN type = "content" THEN content.alias
                    WHEN type = "component" THEN component.controller
                    WHEN type = "alias" THEN alias.alias
                    WHEN type = "url" THEN structure.target_url
                ELSE NULL
                END AS target
 
            FROM structure
            LEFT JOIN content ON content.id = structure.target_id
            LEFT JOIN component ON component.id = structure.target_id
            LEFT JOIN alias ON alias.id = structure.target_id
            WHERE (structure.locale = ? OR structure.locale = ?)
     
            ORDER BY parent_id, ordering ASC';

        $stmt = $db->prepare($query);
        $stmt->set(\Webvaloa\Webvaloa::getLocale())->set($tmp = '*');

        try {
            foreach ($stmt->execute() as $row) {
                $this->navi->sub[$row->id] = $row;
                $this->navi->sub[$row->id]->route = '/'.$row->alias;

                if (!is_null($row->parent_id)) {
                    $this->navi->sub[$row->parent_id]->sub[] = $row;

                    if (isset($this->navi->sub[$row->parent_id]->route)) {
                        $this->navi->sub[$row->id]->route = $this->navi->sub[$row->parent_id]->route.$this->navi->sub[$row->id]->route;
                    }
                }
            }

            foreach ($this->navi->sub as $k => $v) {
                if (!is_null($v->parent_id)) {
                    unset($this->navi->sub[$k]);
                }
            }

            return $this->navi;
        } catch (Exception $e) {
        }
    }
}
