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

use Libvaloa\Debug\Debug;
use Webvaloa\Cache;
use Webvaloa\Article;
use Webvaloa\Category;
use Webvaloa\Helpers\Article as ArticleHelper;
use Webvaloa\Helpers\Category as CategoryHelper;
use Webvaloa\ContentAccess;

class ListController extends \Webvaloa\Application
{
    private $cache;

    public function __construct()
    {
        $this->cache = new Cache();
        $this->ui->addTemplate('pagination');
    }

    public function index($id = false, $page = 1)
    {
        if (!$id || !is_numeric($id)) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        // Limit pagination from 1 to 100
        $limit = 10;
        if (isset($_GET['limit'])) {
            $limit = (int) $_GET['limit'];

            if ($limit > 100) {
                $limit = 100;
            }

            if ($limit < 1) {
                $limit = 1;
            }
        }

        // Check permissions
        if (!$this->checkPermissions($id)) {
            Debug::__print('Oops, no permissions');

            header('HTTP/1.0 404 Not Found');
            exit;
        }

        // Load category
        $category = new Category($id);
        $category->loadCategory();

        // Template override
        if ($tmp = $category->getTemplate()) {
            $tmp = str_replace('.xsl', '', $tmp);
            if ($tmp && !empty($tmp)) {
                $this->ui->properties['override_template'] = $tmp;
            }
        }

        // Layout override
        if ($tmp = $category->getListLayout()) {
            $tmp = str_replace('.xsl', '', $tmp);
            if ($tmp && !empty($tmp)) {
                $this->ui->properties['override_layout'] = $tmp;
            }
        }

        // Load article list
        $list = new CategoryHelper($id);
        $list->page = $page;
        $list->limit = $limit;
        $this->view->items = $list->getArticles();
        $this->view->category_id = $id;

        // Load articles
        foreach ($this->view->items->items as $k => $v) {
            $articleHelper = new ArticleHelper($v->id);
            $v->article = $articleHelper->article;
            $articles[$k] = $v;
        }

        if (isset($articles)) {
            $this->view->items->items = $articles;
        }

        Debug::__print($this->view);
    }

    private function checkPermissions($categoryId)
    {
        try {
            $contentAccess = new ContentAccess($categoryId);

            return $contentAccess->checkPermissions();
        } catch (\RuntimeException $e) {
            Debug::__print($e->getMessage());
        } catch (\Exception $e) {
            Debug::__print($e->getMessage());
        }

        return false;
    }
}
