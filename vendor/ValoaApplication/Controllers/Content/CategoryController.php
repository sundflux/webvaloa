<?php
/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>
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

namespace ValoaApplication\Controllers\Content;

use stdClass;

use Libvaloa\Debug;
use Libvaloa\Controller\Redirect;

use Webvaloa\Tag;
use Webvaloa\Category;
use Webvaloa\Helpers\Pagination;
use Webvaloa\Security;

class CategoryController extends \Webvaloa\Application
{

    public function __construct()
    {
        $this->ui->addJS('/js/Loader.js');
        $this->ui->addJS('/js/Content_Category.js');
        $this->ui->addCSS('/css/Loader.css');
        $this->ui->addTemplate('pagination');

        $this->view->token = Security::getToken();
    }

    public function index($page = 1)
    {
        $q = "";

        if (isset($_GET['search'])) {
            $this->view->search = $_GET['search'];
            $q = " AND category LIKE ?";
        }

        $pagination = new Pagination;
        $this->view->pages = $pagination->pages((int) $page, $pagination->countTable('category', 'WHERE deleted = 0'));
        $this->view->pages->url = '/content_category/';

        $tag = new Tag;
        $starredTagId = $tag->findTagByName('Starred');

        $query = $pagination->prepare('
            SELECT category.*,
                (SELECT COUNT(content.id) as article_count
                    FROM content, content_category
                    WHERE content.id = content_category.content_id
                    AND content_category.category_id = category.id
                    AND content.published > 0 ) as article_count,
                (SELECT COUNT(category_tag.id) as starred
                    FROM category_tag
                    WHERE category.id = category_tag.category_id
                    AND category_tag.tag_id = ' . (int) $starredTagId->id . ') as starred
            FROM
            category
            WHERE deleted = 0
            ' . $q . '
            ORDER BY article_count
            DESC');

        try {
            $stmt = $this->db->prepare($query);

            if (isset($q) && !empty($q)) {
                $stmt->set('%' . $_GET['search'] . '%');
            }

            $stmt->execute();

            $this->view->categories = $stmt->fetchAll();
        } catch (Exception $e) {
            Debug::__print($e->getMessage());
        }
    }

    public function add()
    {
        if (isset($_POST['category']) && !empty($_POST['category'])) {
            $category = new Category;
            $category->addCategory($_POST['category']);
            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('CATEGORY_ADDED'));
        }

        Redirect::to('content_category');
    }

    public function toggle($categoryID)
    {
        $category = new Category($categoryID);

        if ($category->isStarred()) {
            $category->removeStarred();
        } else {
            $category->addStarred();
        }
    }

    public function layouts($id = false)
    {
        if (!$id) {
            return false;
        }

        $category = new Category((int) $id);
        $category->loadCategory();

        $layouts = $category->getAvailableLayouts();
        $templates = $category->getAvailableTemplates();

        $override = $category->getLayout();
        $listOverride = $category->getListLayout();
        $templateOverride = $category->getTemplate();

        // Article view overrides
        foreach ($layouts as $k => $v) {
            $template = new stdClass;
            $template->template = $v;

            if ($v == $override) {
                $template->selected = 'selected';
            }

            $overrides[] = $template;
        }

        // List view overrides
        foreach ($layouts as $k => $v) {
            $template = new stdClass;
            $template->template = $v;

            if ($v == $listOverride) {
                $template->selected = 'selected';
            }

            $listOverrides[] = $template;
        }

        // Template overrides
        foreach ($templates as $k => $v) {
            $template = new stdClass;
            $template->template = $v;

            if ($v == $templateOverride) {
                $template->selected = 'selected';
            }

            $templateOverrides[] = $template;
        }

        $this->view->overrides = $overrides;
        $this->view->listOverrides = $listOverrides;
        $this->view->templateOverrides = $templateOverrides;
    }

    public function edit()
    {
        if (!isset($_POST['category_id']) || empty($_POST['category_id'])) {
            Redirect::to('content_category');
        }

        $query = "
            UPDATE category SET category = ?
            WHERE id = ?";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->set($_POST['category']);
            $stmt->set($_POST['category_id']);
            $stmt->execute();

            // Update layout overrides
            $cat = new Category($_POST['category_id']);
            $cat->loadCategory();

            if (isset($_POST['override_template'])) {
                $cat->setTemplate($_POST['override_template']);
            }

            if (isset($_POST['override'])) {
                $cat->setLayout($_POST['override']);
            }

            if (isset($_POST['override_list'])) {
                $cat->setListLayout($_POST['override_list']);
            }

            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('CATEGORY_EDITED'));
        } catch (Exception $e) {

        }

        Redirect::to('content_category');
    }

    public function delete($id)
    {
        $query = "
            UPDATE category SET deleted = 1
            WHERE id = ?";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->set((int) $id);
            $stmt->execute();

            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('CATEGORY_DELETED'));
        } catch (Exception $e) {

        }

        Redirect::to('content_category');
    }

}
