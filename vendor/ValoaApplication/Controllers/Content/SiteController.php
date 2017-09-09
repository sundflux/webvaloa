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

namespace ValoaApplication\Controllers\Content;

use Webvaloa\Helpers\Navigation;
use Webvaloa\Controller\Redirect;
use Webvaloa\Article;
use Webvaloa\Category;
use stdClass;

class SiteController extends \Webvaloa\Application
{
    public function __construct()
    {
        $this->ui->addJS('/jquery/plugins/jquery.nestable.js');
        $this->ui->addCSS('/css/Content_Site.css');
        $this->ui->addJS('/js/Content_Site.js');
    }

    public function index()
    {
        $navigation = new Navigation();
        $this->view->editablemenu = new stdClass();
        $this->view->editablemenu->navigation = $navigation->get();

        $article = new Article(0);
        $this->view->contents = $article->getArticles();

        $query = '
            SELECT *
            FROM component ';

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            $this->view->components = $stmt->fetchAll();
        } catch (Exception $e) {
        }

        $query = '
            SELECT *
            FROM alias ';

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            $this->view->alias = $stmt->fetchAll();
        } catch (Exception $e) {
        }

        $query = '
            SELECT *
            FROM category ';

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            $this->view->lists = $stmt->fetchAll();
        } catch (Exception $e) {
        }
    }
    public function save()
    {
        if (!isset($_POST['json']) || empty($_POST['json'])) {
            $this->ui->addError('Failed to save');
            Redirect::to('content_site');
        }
        $json = json_decode($_POST['json']);
        if (json_last_error() === JSON_ERROR_NONE) {
            try {
                $stmt = $this->db->prepare('DELETE FROM structure WHERE locale = ?'); // Delete everything of selected locale and start from scratch
                $stmt->set(\Webvaloa\Webvaloa::getLocale());
                $stmt->execute();
            } catch (Exception $e) {
            }

            $this->sub($json);
            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('SAVED'));
        } else {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('INVALID_JSON'));
        }
        Redirect::to('content_site');
        exit;
    }
    private function sub($items, $parent = null)
    {
        foreach ($items as $sub) {
            $query = "
            INSERT INTO structure (alias, parent_id, type, target_id, target_url, translation, locale, ordering)
			VALUES (?, ?, ?, ?, ?, ?, ?, '0')";

            try {
                $stmt = $this->db->prepare($query);
                if (empty($sub->alias)) {
                    $a = $sub->name;
                        // Use transliteration to convert special letters and characters to ascii. Note: this requires setlocale with .UTF-8 to be correctly installed
                        $translit = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $a);
                    if ($translit !== false) {
                        $a = $translit;
                    }
                    $a = preg_replace('/[^A-Za-z0-9\-]/', '', strtolower(str_replace(' ', '-', $a)));
                    $sub->alias = $a;
                }
                $stmt->set($sub->alias);
                $stmt->set($parent);
                $stmt->set($sub->type);
                if ($sub->type == 'url') {
                    $stmt->set(null);
                    $stmt->set($sub->target);
                } else {
                    $stmt->set($sub->target);
                    $stmt->set(null);
                }

                $stmt->set($sub->name);

                $stmt->set(\Webvaloa\Webvaloa::getLocale());

                $stmt->execute();

                $insertedID = $this->db->lastInsertID();
            } catch (Exception $e) {
            }

            if (isset($sub->children)) {
                $this->sub($sub->children, $insertedID);
            }
        }
    }
}
