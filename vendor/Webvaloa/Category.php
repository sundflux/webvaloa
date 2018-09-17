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

namespace Webvaloa;

use Libvaloa\Db;
use Webvaloa\Helpers\Filesystem;
use RuntimeException;

/**
 * Class Category
 * @package Webvaloa
 */
class Category
{
    /**
     * @var bool
     */
    private $id;

    /**
     * @var
     */
    private $category;

    /**
     * @var bool
     */
    private $fields;

    const GLOBAL_GROUP_ID = 0;
    const OVERRIDE_TEMPLATE = -1;
    const OVERRIDE_LAYOUT = 0;
    const OVERRIDE_LIST_LAYOUT = 1;
    const USE_TEMPLATE_DIR = 1;

    /**
     * Constructor, give controller name for actions.
     *
     * @param string $controller
     */
    public function __construct($id = false)
    {
        $this->fields = false;
        $this->groups = false;
        $this->id = $id;
    }

    /**
     * @param $k
     * @param $v
     */
    public function __set($k, $v)
    {
    }

    /**
     * @param $k
     * @return bool
     */
    public function __get($k)
    {
        if (!$this->category) {
            $this->loadCategory();
        }

        if (isset($this->category->$k)) {
            return $this->category->$k;
        }

        return false;
    }

    /**
     * @param $layout
     * @param int $type
     * @return bool
     */
    public function setLayout($layout, $type = 0)
    {
        // No category loaded yet
        if (!$this->id) {
            return false;
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $q = '';

        switch ($type) {
            case self::OVERRIDE_LAYOUT:
                $q = 'layout';
                break;

            case self::OVERRIDE_LIST_LAYOUT:
                $q = 'layout_list';
                break;

            default:
                $q = 'template';
                break;
        }

        $query = "
            UPDATE category
            SET {$q} = ?
            WHERE id = ?";

        $stmt = $db->prepare($query);
        $stmt->set($layout);
        $stmt->set((int) $this->id);

        try {
            $stmt->execute();
        } catch (Exception $e) {
        }
    }

    /**
     * @param $layout
     * @return bool
     */
    public function setTemplate($layout)
    {
        return $this->setLayout($layout, self::OVERRIDE_TEMPLATE);
    }

    /**
     * @param $layout
     * @return bool
     */
    public function setListLayout($layout)
    {
        return $this->setLayout($layout, self::OVERRIDE_LIST_LAYOUT);
    }

    /**
     * @return bool
     */
    public function getTemplate()
    {
        // No category loaded yet
        if (!$this->id) {
            return false;
        }

        return $this->category->template;
    }

    /**
     * @return bool
     */
    public function getLayout()
    {
        // No category loaded yet
        if (!$this->id) {
            return false;
        }

        return $this->category->layout;
    }

    /**
     * @return bool
     */
    public function getListLayout()
    {
        // No category loaded yet
        if (!$this->id) {
            return false;
        }

        return $this->category->layout_list;
    }

    /**
     * @param bool $useTemplateDir
     * @return array
     */
    public function getAvailableLayouts($useTemplateDir = false)
    {
        $conf = new Configuration();
        $template = $conf->template->value;

        if ($useTemplateDir) {
            $layoutDir = LIBVALOA_EXTENSIONSPATH.DIRECTORY_SEPARATOR.Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Layout'.DIRECTORY_SEPARATOR.$template;
        } else {
            $layoutDir = LIBVALOA_EXTENSIONSPATH.DIRECTORY_SEPARATOR.Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Layout'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'Article'.DIRECTORY_SEPARATOR.'Views';
        }

        if (!is_readable($layoutDir) || !is_dir($layoutDir)) {
            return array();
        }

        $fs = new Filesystem($layoutDir);
        $files = $fs->files();

        foreach ($files as $file) {
            if ($file->extension == 'xsl') {
                $templates[] = $file->filename;
            }
        }

        if (isset($templates)) {
            return $templates;
        }

        return array();
    }

    /**
     * @return array
     */
    public function getAvailableTemplates()
    {
        return $this->getAvailableLayouts(self::USE_TEMPLATE_DIR);
    }

    /**
     * Loads category data.
     */
    public function loadCategory()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT category.*
            FROM category
            WHERE category.id = ?
            LIMIT 1';

        $stmt = $db->prepare($query);
        $stmt->set($this->id);

        try {
            $stmt->execute();
            $this->category = $stmt->fetch();
            $category = $this->category;
        } catch (Exception $e) {
        }

        if (isset($category)) {
            return $category;
        }
    }

    /**
     * @return array
     */
    public function groups()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        if ($this->id == self::GLOBAL_GROUP_ID) {
            // Get global groups
            $query = '
                SELECT id as field_group_id
                FROM field_group
                WHERE field_group.global = 1
                ORDER BY field_group.name ASC';
        } else {
            // Get category groups
            $query = '
                SELECT category_field_group.field_group_id
                FROM category_field_group, field_group
                WHERE category_field_group.category_id = ?
                AND category_field_group.field_group_id = field_group.id
                AND field_group.global = 0
                ORDER BY field_group.name ASC';
        }

        $stmt = $db->prepare($query);

        if ($this->id > self::GLOBAL_GROUP_ID) {
            $stmt->set($this->id);
        }

        try {
            $stmt->execute();
            foreach ($stmt as $row) {
                $groups[] = $row->field_group_id;
            }
        } catch (Exception $e) {
        }

        if (isset($groups)) {
            return $groups;
        }

        return array();
    }

    /**
     * @return array
     */
    public function fields()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        // Get groups
        $groups = $this->groups();

        if (empty($groups)) {
            return array();
        }

        $query = '
            SELECT *
            FROM field
            WHERE field_group_id IN ( '.implode(',', $groups).' )
            ORDER BY field_group_id ASC, ordering ASC';

        $stmt = $db->prepare($query);
        try {
            $stmt->execute();

            foreach ($stmt as $row) {
                $fields[$row->name] = $row;
            }
        } catch (PDOException $e) {
        }

        if (isset($fields)) {
            return $fields;
        }

        return array();
    }

    /**
     * @return mixed
     */
    public function categories()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT category.*, (
                SELECT COUNT(content.id) as article_count
                FROM content, content_category
                WHERE content.id = content_category.content_id
                AND content_category.category_id = category.id
                AND content.published > 0 ) as article_count
            FROM
            category
            WHERE deleted = 0';

        $stmt = $db->prepare($query);

        try {
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
        }
    }

    /**
     * @param $name
     * @param null $parentID
     * @return bool
     */
    public function addCategory($name, $parentID = null)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $object = new Db\Item('category', $db);
        $object->category = $name;
        $object->parent_id = $parentID;
        $object->deleted = 0;
        $object->apply_permissions = 0;

        $this->id = $object->save();

        return $this->id;
    }

    /**
     *
     */
    public function delete()
    {
        if (!$this->id) {
            throw new RuntimeException('Category not found');
        }
    }

    /**
     * Adds field group to category.
     *
     * @param type $roleID
     *
     * @return int roleid
     */
    public function addGroup($groupID)
    {
        $groups = $this->groups();

        // Already has the group
        if (in_array($groupID, $roles)) {
            return true;
        }

        // No category loaded yet, load before adding groups
        if (!$this->id) {
            return false;
        }

        // Database connection
        $db = \Webvaloa\Webvaloa::DBConnection();

        // Insert group object
        $object = new Db\Item('category_field_group', $db);
        $object->field_group_id = $group_id;
        $object->category_id = $this->id;

        // recursiveness not implemented yet
        $object->recursive = 0;

        return $object->save();
    }

    /**
     * @return mixed
     */
    public function getStarred()
    {
        // Database connection
        $db = \Webvaloa\Webvaloa::DBConnection();

        $tag = new Tag();
        $starredTagId = $tag->findTagByName('Starred');

        $query = '
            SELECT category.id, category.category
            FROM category, category_tag
            WHERE
                category.id = category_tag.category_id
                AND category_tag.tag_id = ?
                AND category.deleted = 0';

        try {
            $stmt = $db->prepare($query);
            $stmt->set((int) $starredTagId->id);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
        }
    }

    /**
     * @return bool
     */
    public function isStarred()
    {
        // No category loaded yet, load before changing starred status
        if (!$this->id) {
            return false;
        }

        $starred = array();
        $tag = new Tag();
        $starredTagId = $tag->findTagByName('Starred');

        $tmp = $this->getStarred();
        foreach ($tmp as $k => $v) {
            $starred[] = $v->id;
        }

        if (in_array($this->id, $starred)) {
            // Already favorited
            return true;
        }

        return false;
    }

    /**
     * @return bool|void
     */
    public function addStarred()
    {
        // No category loaded yet, load before changing starred status
        if (!$this->id) {
            return false;
        }

        if ($this->isStarred()) {
            // Already starred
            return;
        }

        $tag = new Tag();
        $starredTagId = $tag->findTagByName('Starred');

        $this->addTag($starredTagId);
    }

    /**
     *
     */
    public function removeStarred()
    {
        $tag = new Tag();
        $starredTagId = $tag->findTagByName('Starred');

        $this->removeTag($starredTagId);
    }

    /**
     * @param $tag
     */
    public function addTag($tag)
    {
        // Database connection
        $db = \Webvaloa\Webvaloa::DBConnection();

        if (in_array($tag->id, $this->tags())) {
            // Already in tags
            return;
        }

        $object = new Db\Item('category_tag', $db);
        $object->category_id = $this->id;
        $object->tag_id = $tag->id;

        return $object->save();
    }

    /**
     * @param $tag
     */
    public function removeTag($tag)
    {
        // Database connection
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            DELETE FROM category_tag
            WHERE category_id = ?
            AND tag_id = ?';

        $stmt = $db->prepare($query);

        try {
            $stmt->set((int) $this->id);
            $stmt->set((int) $tag->id);
            $stmt->execute();
        } catch (Exception $e) {
        }
    }

    /**
     * @return array
     */
    public function tags()
    {
        // Database connection
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT tag_id
            FROM category_tag
            WHERE category_id = ?';

        $stmt = $db->prepare($query);

        try {
            $stmt->set((int) $this->id);
            $stmt->execute();
            foreach ($stmt as $row) {
                $tags[] = $row->id;
            }

            if (isset($tags)) {
                return $tags;
            }

            return array();
        } catch (Exception $e) {
        }
    }

    /**
     * @param $roleID
     * @return bool
     */
    public function addRole($roleID)
    {
        if (!$this->id) {
            throw new RuntimeException('CategoryID must be set before running addRole');
        }

        $roles = $this->roles();

        if (is_array($roles) && in_array($roleID, $roles)) {
            return true;
        }

        $object = new DB\Item('category_role', \Webvaloa\Webvaloa::DBConnection());
        $object->category_id = $this->id;
        $object->role_id = $roleID;

        return $object->save();
    }

    /**
     * @param $roleID
     */
    public function deleteRole($roleID)
    {
        if (!$this->id) {
            throw new RuntimeException('CategoryID must be set before running deleteRole');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            DELETE FROM category_role
            WHERE role_id = ?
            AND category_id = ?';

        $stmt = $db->prepare($query);
        $stmt->set((int) $roleID);
        $stmt->set((int) $this->id);

        try {
            $stmt->execute();
        } catch (Exception $e) {
        }
    }

    /**
     *
     */
    public function dropRoles()
    {
        // Delete user roles
        $roles = $this->roles();
        foreach ($roles as $k => $v) {
            $this->deleteRole($v);
        }
    }

    /**
     * Check if category has certain role.
     *
     * @param type $roleID
     *
     * @return bool
     */
    public function hasRole($roleID)
    {
        $roles = $this->roles();

        if (in_array($roleID, $roles)) {
            return true;
        }

        return false;
    }

    /**
     * Return all category roles.
     *
     * @return array
     */
    public function roles()
    {
        if (!$this->id || !is_numeric($this->id)) {
            $role = new Role();
            $roles[] = $role->getRoleID('Public');

            return $roles;
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT role_id
            FROM category_role
            WHERE category_id = ?';

        $stmt = $db->prepare($query);
        $stmt->set((int) $this->id);

        try {
            $stmt->execute();

            foreach ($stmt as $k => $row) {
                $roles[] = $row->role_id;
            }

            if (isset($roles)) {
                return $roles;
            }

            // No roles
            return array();
        } catch (Exception $e) {
        }
    }
}
