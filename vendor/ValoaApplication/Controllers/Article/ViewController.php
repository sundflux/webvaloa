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
namespace ValoaApplication\Controllers\Article;

use Libvaloa\Debug;
use Webvaloa\Cache;
use Webvaloa\Article;
use Webvaloa\Category;
use Webvaloa\Helpers\Article as ArticleHelper;
use Webvaloa\Helpers\ArticleAssociation;

class ViewController extends \Webvaloa\Application
{
    private $cache;

    public function __construct()
    {
        $this->cache = new Cache();
    }

    public function index($id = false)
    {
        // Check if we got alias instead
        if (!is_numeric($id) && strlen($id) > 0) {
            $query = '
                SELECT id
                FROM content
                WHERE alias = ?
                AND published = 1
                ORDER BY id DESC';

            $stmt = $this->db->prepare($query);
            $stmt->set($id);
            $stmt->execute();
            $row = $stmt->fetch();
            if (isset($row->id)) {
                $id = $row->id;
            }
        }

        if (!$id || !is_numeric($id)) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        // Try loading associated article
        $association = new ArticleAssociation($id);
        $association->setLocale(\Webvaloa\Webvaloa::getLocale());
        if ($associatedID = $association->getAssociatedId()) {
            $id = $associatedID;
        }

        // Load article
        $article = new Article($id);
        $articleHelper = new ArticleHelper($id);
        $this->view->id = $id;
        $this->view->article = $articleHelper->article;

        // Set template overrides
        $catId = $article->getCategory();

        $category = new Category($catId[0]);
        $category->loadCategory();

        // Template override
        if ($tmp = $category->getTemplate()) {
            $tmp = str_replace('.xsl', '', $tmp);
            if ($tmp && !empty($tmp)) {
                $this->ui->properties['override_template'] = $tmp;
            }
        }

        // Layout override
        if ($tmp = $category->getLayout()) {
            $tmp = str_replace('.xsl', '', $tmp);
            if ($tmp && !empty($tmp)) {
                $this->ui->properties['override_layout'] = $tmp;
            }
        }

        Debug::__print($this->view->article);
    }
}
