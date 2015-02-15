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

use Libvaloa\Debug;
use Libvaloa\Controller\Redirect;

use Webvaloa;
use Webvaloa\Article;
use Webvaloa\Category;
use Webvaloa\Version;
use Webvaloa\Field\Group;
use Webvaloa\Field\Field;
use Webvaloa\Field\Value;
use Webvaloa\Field\Fields;
use Webvaloa\Helpers\Pagination;
use Webvaloa\Helpers\ArticleAssociation;
use Webvaloa\Controller\Request\Response;

use stdClass;
use UnexpectedValueException;

class ArticleController extends \Webvaloa\Application
{

    const MODE_NONE = 0;
    const MODE_ADD = 1;
    const MODE_EDIT = 2;

    public function __construct()
    {
        $this->ui->addJS('/js/Loader.js');
        $this->ui->addCSS('/css/Loader.css');
    }

    public function index($page = 1, $category_id = false)
    {
        $this->ui->addJS('/js/Content_Article.js');
        $this->ui->addCSS('/css/Content_Field.css');
        $this->ui->addTemplate('pagination');
        $this->view->category_id = $category_id;

        $q = "";

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $this->view->search = $_GET['search'];
            $q .= " AND title LIKE ? ";
        }

        if ($category_id) {
            if (!is_numeric($category_id)) {
                throw new UnexpectedValueException('Malformed category id');
            }

            $q .= " AND content_category.category_id = ? ";
        }

        // Count articles
        $queryCount = "
            SELECT COUNT(content.id) as c
            FROM content, content_category
            WHERE content.id = content_category.content_id
            AND published > -1
            AND associated_content_id IS NULL
            {$q}
            ORDER BY content.id DESC";

        $stmt = $this->db->prepare($queryCount);
        try {
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $stmt->set('%' . $_GET['search'] . '%');
            }

            if ($category_id) {
                $stmt->set((int) $category_id);
            }

            $stmt->execute();
            $row = $stmt->fetch();
            $count = (int) $row->c;
        } catch (Exception $e) {

        }

        $pagination = new Pagination;
        $this->view->pages = $pagination->pages((int) $page, $count);
        $this->view->pages->url = '/content_article/';

        // Get articles
        $query = "
            SELECT content.id,user_id, publish_up, publish_down, title, locale, published
            FROM content, content_category
            WHERE content.id = content_category.content_id
            AND published > -1
            AND associated_content_id IS NULL
            {$q}
            ORDER BY content.id DESC";

        $query = $pagination->prepare($query);

        $stmt = $this->db->prepare($query);
        try {
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $stmt->set('%' . $_GET['search'] . '%');
            }

            if ($category_id) {
                $stmt->set((int) $category_id);
            }

            $stmt->execute();
            $this->view->articles = $stmt->fetchAll();
        } catch (Exception $e) {

        }

    }

    public function trash($id = false)
    {
        $article = new Article($id);
        $article->trash();

        $this->ui->addMessage(\Webvaloa\Webvaloa::translate('ARTICLE_TRASHED'));

        Redirect::to('content_article');
    }

    public function add($categoryID = false)
    {
        $this->ui->addJS('/js/Fields/Frontend.js');
        $this->ui->addJS('/js/Content_Article.js');
        $this->ui->addCSS('/css/Content_Field.css');
        $this->ui->setPageRoot('article');

        $this->view->title = \Webvaloa\Webvaloa::translate('ADD_ARTICLE');
        $this->view->article_id = false;
        $this->view->mode = self::MODE_NONE;

        $category = new Category();

        $this->view->category_id = $this->view->categoryID = $categoryID;
        $this->view->categories = $category->categories();

        $this->view->article = new stdClass;
        $this->view->article->published = 0;

        // Unset redirect on save when adding new article
        if(isset($_SESSION['onSaveRedirect'])) {
            unset($_SESSION['onSaveRedirect']);
        }

        if (is_numeric($categoryID)) {
            $this->view->mode = self::MODE_ADD;

            $this->initializeFieldsView($categoryID);
        }
    }

    public function edit($articleID = false)
    {
        $this->ui->addJS('/js/Fields/Frontend.js');
        $this->ui->addJS('/js/Content_Article.js');
        $this->ui->addCSS('/css/Content_Field.css');
        $this->ui->setPageRoot('article');

        if (!$articleID || !is_numeric($articleID)) {
            throw new UnexpectedValueException('Article not found');
        }

        // Redirect on save
        if (isset($_GET['ref']) && isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            $url = $this->request->getBaseUri();
            $l = strlen($url);

            // The referer must match the base uri for redirect
            if(substr($_SERVER['HTTP_REFERER'], 0, $l) == $url) {
                $_SESSION['onSaveRedirect'] = $_SERVER['HTTP_REFERER'];

                $this->view->onSaveRedirect = $_SESSION['onSaveRedirect'];
            }
        } else {
            if(isset($_SESSION['onSaveRedirect'])) {
                unset($_SESSION['onSaveRedirect']);
            }
        }

        // Try loading associated article
        $association = new ArticleAssociation($articleID);
        $association->setLocale(\Webvaloa\Webvaloa::getLocale());

        Debug::__print('Assocation: article id ' . $articleID);

        // Create association if it doesn't exist
        if (!$associatedID = $association->getAssociatedId()) {
            $associatedID = $association->createAssociation();
        }

        Debug::__print('Assocation: article id after association check for ' . $associatedID);

        $this->view->title = \Webvaloa\Webvaloa::translate('EDIT_ARTICLE');
        $this->view->articleID = $this->view->article_id = $articleID;
        $this->view->mode = self::MODE_EDIT;

        $category = new Category();
        $this->view->categories = $category->categories();

        // Load article
        $article = new Article($associatedID);
        $this->view->article = $article->article;

        // Get primary category. In theory webvaloa/db schema
        // supports articles in multiple categories, but
        // for now we only use one.
        $category = $article->getCategory();

        $this->view->category_id = $category[0];

        $this->initializeFieldsView($category[0]);

        // Load earlier version of article view. Just overwrite the view
        if (isset($_GET['version']) && is_numeric($_GET['version']) && is_numeric($articleID)) {
            $v = new Version;
            if ($view = $v->loadVersion($_GET['version'])) {
                $this->view = $view;
            }
        }

        // Load version history
        if (is_numeric($articleID)) {
            $v = new Version;
            $v->target_table = 'content';
            $v->target_id = $articleID;
            try {
                $this->view->history = $v->getVersions();
            } catch (Exception $e) {

            }
        }
    }

    public function globals()
    {
        $this->ui->addJS('/js/Fields/Frontend.js');
        $this->ui->addCSS('/css/Content_Field.css');
        $this->ui->setPageRoot('article');

        $this->view->title = \Webvaloa\Webvaloa::translate('SETTINGS');
        $this->view->articleID = $this->view->article_id = $categoryID = 0;
        $this->view->mode = self::MODE_EDIT;

        $category = new Category();

        $this->view->category_id = $this->view->categoryID = $categoryID;
        $this->view->categories = $category->categories();

        // Load article
        $article = new Article(0);
        $this->view->article = $article->article;

        if (is_numeric($categoryID)) {
            $this->view->mode = self::MODE_ADD;

            $this->initializeFieldsView($categoryID);
        }
    }

    public function save()
    {
        if (!isset($_POST['category_id'])) {
            throw new RuntimeException('Could not find a category');
        }

        try {
            $this->db->beginTransaction();

            if (isset($_SESSION['__previous_version'])) {
                // Save previous view
                if ($_POST['article_id'] == $_SESSION['__previous_version']->article->id) {
                    $v = new Version;
                    $v->target_table = 'content';
                    $v->target_id = $_POST['article_id'];
                    $v->content = $_SESSION['__previous_version'];
                    $v->save();
                }

                unset($_SESSION['__previous_version']);
            }

            if (is_numeric($_POST['article_id']) && $_POST['article_id'] > 0) {
                // Try loading associated article
                $association = new ArticleAssociation($_POST['article_id']);
                $association->setLocale(\Webvaloa\Webvaloa::getLocale());

                Debug::__print('Assocation: article id ' . $_POST['article_id']);

                // Create association if it doesn't exist
                if (!$associatedID = $association->getAssociatedId()) {
                    $associatedID = $association->createAssociation();
                }

                Debug::__print('Assocation: article id after association check for ' . $associatedID);
            }

            // Save article
            if (isset($associatedID) && is_numeric($associatedID)) {
                $id = $associatedID;
            } else {
                if (isset($_POST['article_id'])) {
                    $id = $_POST['article_id'];
                }
            }

            $article = new Article($id);

            if (!isset($id) || !is_numeric($id)) {
                $id = $article->insert();

                // Add a category for it
                $article->addCategory($_POST['category_id']);

                // Set article title
                $article->setTitle($_POST['title']);

                $this->ui->addMessage(\Webvaloa\Webvaloa::translate('ARTICLE_ADDED'));
            } elseif (is_numeric($id) && $id > 0) {
                // Set article title
                $article->setTitle($_POST['title']);

                $this->ui->addMessage(\Webvaloa\Webvaloa::translate('ARTICLE_SAVED'));
            } elseif ($id == 0) {
                // Saving global fields

                $this->ui->addMessage(\Webvaloa\Webvaloa::translate('SAVED'));
            }

            // Publish article
            if (isset($_POST['published']) && $_POST['published'] == 1 && (isset($id) && $id > 0)) {
                $article->publish();
            }

            // Unpublish article
            if (isset($_POST['published']) && $_POST['published'] == 0 && (isset($id) && $id > 0)) {
                $article->unpublish();
            }

            // Set alias
            if (isset($_POST['alias']) && !empty($_POST['alias'])) {
                $article->alias($_POST['alias']);
            } else {
                $article->alias($_POST['title']);
            }

            $skip = array(
                'article_id',
                'category_id'
            );

            // Drop old fields
            $value = new Value($id);
            $value->dropValues();

            // Group ordering counter
            $i = 0;

            // Save fields
            foreach ($_POST as $uniqid => $fieldValue) {
                // First level is the uniqid

                // Skip these post variables
                if (in_array($uniqid, $skip)) {
                    continue;
                }

                if (!is_array($fieldValue)) {
                    continue;
                }

                foreach ($fieldValue as $k => $v) {
                    // Skip these post variables
                    if (in_array($k, $skip)) {
                        continue;
                    }

                    // This is the group separator, increase group ordering
                    if ($k == 'group_separator') {
                        $i++;
                        continue;
                    }

                    // Find field id
                    $field = new Field;
                    $f = $field->findByName($k);
                    $fieldID = $f->id;

                    // Insert field data if found
                    if ($fieldID) {
                        $value->fieldID($fieldID);
                        $value->fieldValue($v);
                        $value->fieldLocale($this->locale);
                        $value->fieldOrdering($i);
                        $value->insert();
                    }
                }
            }

            // Publish up/down
            if (isset($_POST['publish_up'])) {
                $article->setPublishUp($_POST['publish_up']);
            }
            if(isset($_POST['publish_down'])) {
                $article->setPublishDown($_POST['publish_down']);
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();

            $this->ui->addError(\Webvaloa\Webvaloa::translate('SAVING_ARTICLE_FAILED'));
        }

        // Get original id back for the associated article
        if (isset($association) && $tmp = $association->getId()) {
            $id = $tmp;
        }

        if ($id == 0) {
            // Redirect back to globals edit view
            Redirect::to('content_article/globals');
        } else {
            // Redirect back to referer
            if(isset($_SESSION['onSaveRedirect']) && !empty($_SESSION['onSaveRedirect'])) {
                $url = $_SESSION['onSaveRedirect'];
                unset($_SESSION['onSaveRedirect']);
                Redirect::to($url);
            } else {
                // Default redirect, black to edit view
                Redirect::to('content_article/edit/' . $id);
            }
        }
    }

    private function initializeFieldsView($categoryID)
    {
        $category = new Category($categoryID);
        $this->view->category = $category->category;

        // Always include these fields:
        $this->view->fieldTypes[] = 'Datetimepicker';

        $groups = $category->groups();
        $fields = $category->fields();

        // Initialize empty fields for groups
        foreach ($groups as $k => $v) {
            // Keep index by field group id
            if (@!isset($index[$v->field_group_id])) {
                $groupindex[$v] = new stdClass;
                $groupindex[$v]->i = 0;
            }
            $i = $groupindex[$v]->i;

            if (!isset($repeatables[$v])) {
                $repeatables[$v] = new stdClass;
            }

            if (!isset($repeatables[$v]->repeatable[$i])) {
                $repeatables[$v]->repeatable[$i] = new stdClass;
            }

            foreach ($fields as $field) {
                if ($v != $field->field_group_id) {
                    continue;
                }

                $repeatables[$v]->repeatable[$i]->fields[$field->name] = clone $field;
                $repeatables[$v]->repeatable[$i]->fields[$field->name]->uniqid = uniqid();
                $repeatables[$v]->repeatable[$i]->fields[$field->name]->values[0] = '';

                // Get params
                $fieldClass = '\Webvaloa\Field\Fields\\' . $field->type;

                // Articleid not set when adding new one
                if (!isset($this->view->articleID)) {
                    $this->view->articleID = false;
                }

                $f = new $fieldClass($field->id, $this->view->articleID);
                $repeatables[$v]->repeatable[$i]->fields[$field->name]->params = $f->getParams();

                // Collect field types
                $this->view->fieldTypes[] = $field->type;
            }

            $i++;
            $groupindex[$v]->i = $i;
        }

        // Put data to fields
        if (isset($this->view->article->fieldValues)) {
            foreach ($this->view->article->fieldValues as $repeatable => $v) {
                // Keep index by field group id
                if (!isset($index[$v->field_group_id])) {
                    $index[$v->field_group_id] = new stdClass;
                    $index[$v->field_group_id]->i = 0;
                }
                $i = $index[$v->field_group_id]->i;

                if (!isset($repeatables[$v->field_group_id])) {
                    $repeatables[$v->field_group_id] = new stdClass;
                }

                if (!isset($repeatables[$v->field_group_id]->repeatable[$i])) {
                    $repeatables[$v->field_group_id]->repeatable[$i] = new stdClass;
                }

                // First group is always guaranteed to have initial field schema.
                // However repeated groups might differ IF the saved field is empty,
                // so make sure every field inside the repeated groups has at LEAST
                // the initial fields.
                if ($i > 0 && isset($repeatables[$v->field_group_id]->repeatable[0]->fields)) {
                    foreach ($repeatables[$v->field_group_id]->repeatable[0]->fields as $initialField => $initialFieldObject) {
                        if (!isset($v->fieldValues[$initialField])) {
                            $v->fieldValues[$initialField][0] = '';
                        }
                    }
                }

                // Fill values to fields
                foreach ($v->fieldValues as $fieldName => $value) {
                    if (!is_object($fields[$fieldName])) {
                        continue;
                    }

                    $field = clone $fields[$fieldName];
                    $field->values = $value;
                    $field->uniqid = uniqid();

                    // Fields with repeatable > 1 are 'special',
                    // data is loaded with ajax or other methods.
                    if ($field->repeatable > 1) {
                        $field->values = '';
                    }

                    foreach ($field as $_fieldName => $_fieldValue) {
                        if (!isset($repeatables[$v->field_group_id]->repeatable[$i]->fields[$fieldName])) {
                            $repeatables[$v->field_group_id]->repeatable[$i]->fields[$fieldName] = new stdClass;
                        }

                        $repeatables[$v->field_group_id]->repeatable[$i]->fields[$fieldName]->$_fieldName = $_fieldValue;
                    }
                }

                $i++;
                $index[$v->field_group_id]->i = $i;
            }
        }

        // Sort groups and set repeatables data to group
        foreach ($groups as $k => $v) {
            try {
                $group = new Group($v);

                $tmp[$v] = new stdClass;
                $tmp[$v]->id = $v;
                $tmp[$v]->uniqid = uniqid();
                $tmp[$v]->name = $group->name;
                $tmp[$v]->translation = $group->translation;
                $tmp[$v]->repeatable = $group->repeatable;

                if (isset($repeatables[$v])) {
                    $tmp[$v]->repeatable_group = $repeatables[$v];
                }
            } catch (OutOfBoundsException $e) {
                // Group assigned to this groups has been deleted

                Debug::__print('Group not found!');
                Debug::__print($e->getMessage);
            }
        }

        // Put fields to view
        $this->view->fields = $tmp;

        // Get unique field types and include their media
        $this->view->fieldTypes = array_unique($this->view->fieldTypes);
        foreach ($this->view->fieldTypes as $k => $type) {
            $this->ui->addJS('/js/Fields/' . $type . '.js');

            $fieldClass = '\Webvaloa\Field\Fields\\' . $type;
            $f = new $fieldClass();

            // Get field media
            foreach ($f->getCSS() as $css) {
                $this->ui->addCSS($css);
            }

            foreach ($f->getJS() as $js) {
                $this->ui->addJS($js);
            }

            foreach ($f->getTemplate() as $template) {
                $this->ui->addTemplate($template);
            }
        }

        // Save view for versioning
        if ($this->view->mode == self::MODE_EDIT) {
            $_SESSION['__previous_version'] = $this->view;
        }
    }

    /**
     * Helper to return field parameters as JSON
     */
    public function fieldParams($fieldID = false, $articleID = false)
    {
        // Get field type first
        $f = new Field($fieldID, $articleID);
        $type = (string) $f->type;

        if (empty($type)) {
            Response::JSON(array());
            exit;
        }

        $fieldClass = '\Webvaloa\Field\Fields\\' . $type;
        $f = new $fieldClass($fieldID, $articleID);

        $p = $f->getParams();
        Response::JSON($p);
    }

}
