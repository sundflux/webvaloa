<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2015 Tarmo Alexander Sundström <ta@sundstrom.im>
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

use Exception;
use RuntimeException;
use Webvaloa\Article;
use Webvaloa\Locale\Locales as LocalesHelper;

/*
 * Associations for article translations.
 *
 * // Get associated content id for the locale
 * $association = new ArticleAssociation($id);
 * $association->setLocale('fi_FI');
 * if (!$articleId = $association->getAssociatedId()) {
 *     $articleId = $id;
 * }
 *
 * // Create association for locale
 * $association = new ArticleAssociation($id);
 * $association->setLocale('fi_FI');
 * if (!$articleId = $association->getAssociatedId()) {
 *     $articleId = $association->createAssociation();
 * }
 *
 */
class ArticleAssociation
{
    public $id;
    public $associatedId;
    private $locale;

    public function __construct($id = false)
    {
        if (!is_numeric($id)) {
            throw new Exception();
        }

        // The main content id
        $this->id = $id;

        // Content id of the associated item
        $this->associatedId = false;

        // Locale
        $this->locale = false;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    private function defaultLocale()
    {
        $this->setLocale(\Webvaloa\Webvaloa::getLocale());
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setAssociatedId($id)
    {
        $this->associatedId = $id;
    }

    public function getAssociatedId()
    {
        if (!$this->getLocale()) {
            $this->defaultLocale();
        }

        $this->setAssociatedId($this->loadAssociatedId());

        return $this->associatedId;
    }

    public function getAssociatedIds()
    {
        $localesHelper = new LocalesHelper();
        $locales = $localesHelper->locales();
        $ids[] = $this->getId();

        foreach ($locales as $locale) {
            $this->setLocale($locale);
            $this->setAssociatedId($this->loadAssociatedId());
            $ids[] = $this->associatedId;
        }

        return $ids;
    }

    private function loadAssociatedId()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $locale = $this->getLocale();
        $db = \Webvaloa\Webvaloa::DBConnection();
        $id = $this->getId();

        $article = new Article($id);
        $a = $article->article;

        // Locale matches and no associated id is set, so this must be an main article
        if ($a->locale == $this->getLocale() && empty($a->associated_content_id)) {
            return $id;
        }

        $query = '
            SELECT id
            FROM content
            WHERE associated_content_id = ?
            AND locale = ?
            ORDER BY id DESC';

        $stmt = $db->prepare($query);
        $stmt->set($id);
        $stmt->set($locale);
        $stmt->execute();

        $row = $stmt->fetch();

        if (isset($row->id)) {
            return $row->id;
        }

        // Not found
        return false;
    }

    public function createAssociation()
    {
        // Association already exists
        if ($tmp = $this->getAssociatedId()) {
            return $tmp;
        }

        if (!$this->getId()) {
            throw new RuntimeException('Association: Main article Id not set, cannot create association');
        }
        $id = $this->getId();

        $article = new Article($id);
        $a = $article->article;

        $associated = new Article();
        $associated->publish_up = $a->publish_up;
        $associated->publish_down = $a->publish_down;
        $associated->published = $a->published;
        $associated->locale = $this->getLocale();
        $associatedId = $associated->insert();

        $associated->setTitle($a->title);
        $associated->setAssociation($id);

        $categories = $article->getCategory();
        if ($categories) {
            foreach ($categories as $k => $v) {
                $associated->addCategory($v);
            }
        }

        // Default alias
        $associated->alias($a->alias.'-'.substr($this->getLocale(), 0, 2));

        $this->setAssociatedId($associatedId);

        return $associatedId;
    }
}
