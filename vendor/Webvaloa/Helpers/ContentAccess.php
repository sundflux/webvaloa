<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2017 Tarmo Alexander Sundström <ta@sundstrom.im>
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

use Libvaloa\Debug;
use Webvaloa\User;
use Webvaloa\Category;
use Webvaloa\Role;

class ContentAccess
{
    private $article;
    private $category;
    private $categoryId;
    private $isAdministrator;

    public function __construct($contentItem = false)
    {
        $this->article = false;
        $this->category = false;
        $this->categoryId = false;
        $this->isAdministrator = false;

        if ($contentItem === false) {
            return;
        }

        // Check if we're administrator
        if ($this->checkIsAdministrator()) {
            return true;
        }

        // Check permissions by article object
        if ($contentItem instanceof \Webvaloa\Article) {
            $this->article = $contentItem;

            Debug::__print('Checking permissions for Article object');
            Debug::__print($this->article);
        }

        // Check permissions by category object
        if ($contentItem instanceof \Webvaloa\Category) {
            $this->categoryId = $contentItem->id;

            Debug::__print('Checking permissions for Category object');
            Debug::__print($this->categoryId);
        }

        // Check permissions by category id
        if (is_numeric($contentItem) && !empty($contentItem)) {
            $this->categoryId = $contentItem;

            Debug::__print('Checking permissions for Category id');
            Debug::__print($this->categoryId);
        }

        // Check permissions by category name
        if (is_string($contentItem) && !empty($contentItem)) {
            $this->category = $contentItem;

            Debug::__print('NOT IMPLEMENTED: Checking permissions for Category name');
            Debug::__print($this->category);
        }
    }

    private function checkIsAdministrator()
    {
        if (!empty($_SESSION['UserID'])) {
            $role = new Role;
            $user = new User($_SESSION['UserID']);
            if ($user->hasRole($role->getRoleId('Administrator'))) {
                $this->isAdministrator = true;

                return true;
            }
        }

        return false;
    }

    public function checkPermissionsByArticleItem()
    {
        if (!$this->article instanceof \Webvaloa\Article) {
            return false;
        }

        if (!empty($_SESSION['UserID'])) {
            $user = new User($_SESSION['UserID']);
        } else {
            $user = new User();
        }

        if (empty($this->article->article->id)) {
            // This article does not have id yet,
            // so it's empty article object which can't have
            // permissions yet set.

            return true;
        }

        // Get user roles
        $userRoles = $user->roles();

        // Get categories for the article
        $categories = $this->article->getCategory();

        $access = false;
        foreach ($categories as $k => $v) {
            $category = new Category($v);

            foreach ($userRoles as $k => $role) {
                if ($category->hasRole($role)) {
                    $access = true;
                }
            }
        }

        return $access;
    }

    public function checkPermissionsByCategoryId($categoryId)
    {
        if (isset($_SESSION['UserID'])) {
            $user = new User($_SESSION['UserID']);
        } else {
            $user = new User();
        }

        // Get user roles
        $userRoles = $user->roles();

        $categories[] = $categoryId;

        $access = false;
        foreach ($categories as $k => $v) {
            $category = new Category($v);

            foreach ($userRoles as $k => $role) {
                if ($category->hasRole($role)) {
                    $access = true;
                }
            }
        }

        return $access;
    }

    public function checkPermissions()
    {
        if ($this->isAdministrator === true) {
            return true;
        }

        if ($this->article !== false) {
            return $this->checkPermissionsByArticleItem($this->article);
        }

        if ($this->article === false && !empty($this->categoryId)) {
            return $this->checkPermissionsByCategoryId($this->categoryId);
        }

        return false;
    }
}
