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

use Libvaloa\Controller\Redirect;
use Webvaloa\Security;
use Webvaloa\Field\Group;
use Webvaloa\Field\Field;
use Webvaloa\Category;
use Webvaloa\Helpers\Field as FieldHelper;
use Webvaloa\Controller\Request\Response;
use stdClass;

class FieldController extends \Webvaloa\Application
{
    public function __construct()
    {
        $this->ui->addJS('/jquery/plugins/jquery.sortable.js');
        $this->ui->addJS('/js/Loader.js');
        $this->ui->addJS('/js/Content_Field.js');
        $this->ui->addCSS('/css/Loader.css');
        $this->ui->addCSS('/css/Content_Field.css');

        $this->view->token = Security::getToken();
    }

    public function index()
    {
        $group = new Group();

        $groups = $group->groups();

        foreach ($groups as $k => $v) {
            $group = new Group($v->id);
            $this->view->groups[$v->id] = new stdClass();
            $this->view->groups[$v->id]->group = $v;
            $this->view->groups[$v->id]->fields = $group->fields();
        }
    }

    public function toggleglobal()
    {
        $group = new Group($_POST['group_id']);

        $tmp = $group->global;
        if ($tmp == 1) {
            $global = 0;
        }
        if ($tmp == 0) {
            $global = 1;
        }
        $group->global = $global;
        $group->save();

        $this->view->retval = 1;
    }

    public function togglerepeatable()
    {
        $group = new Group($_POST['group_id']);

        $tmp = $group->repeatable;
        if ($tmp == 1) {
            $repeatable = 0;
        }
        if ($tmp == 0) {
            $repeatable = 1;
        }
        $group->repeatable = $repeatable;
        $group->save();

        $this->view->retval = 1;
    }

    public function group($group_id = false)
    {
        $this->view->group_id = $group_id;

        $category = new Category();
        $this->view->categories = $category->categories();

        // Load group data for editing
        if ($group_id && is_numeric($group_id)) {
            $group = new Group($group_id);

            $this->view->group_name =  $group->name;
            $this->view->group_label =  $group->translation;
            $this->view->repeatable =  $group->repeatable;

            // Mark selected categories
            $selected = $group->categories();
            foreach ($this->view->categories as $k => $v) {
                $this->view->categories[$k] = $v;
                if (in_array($v->id, $selected)) {
                    $this->view->categories[$k]->selected = 'selected';
                }
            }
        }

        // Bail out of not saving
        if (!isset($_POST['save'])) {
            return;
        }

        // Save group
        $group = new Group($group_id);

        $group->name = $output = preg_replace('/[^A-Za-z0-9_]/i', '', $_POST['group_name']);
        $group->translation = $_POST['group_label'];
        $group->repeatable = $_POST['repeatable_group'];
        $group->global = 0;
        $group_id = $group->save();

        $group = new Group($group_id);
        if ($group_id) {
            // Drop old categories when editing
            $group->deleteFromCategories();
        }

        // Save group categories
        if (isset($_POST['categories']) && !empty($_POST['categories'])) {
            foreach ($_POST['categories'] as $k => $v) {
                $group->addCategory($v);
            }
        }

        if ($group_id) {
            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('FIELD_GROUP_SAVED'));
        } else {
            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('FIELD_GROUP_ADDED'));
        }

        Redirect::to('content_field');
    }

    public function field($group_id = false, $field_id = false)
    {
        $this->view->group_id = $group_id;
        $this->view->field_id = $field_id;

        $field = new Field();
        $this->view->fields = $field->fields();

        if ($field_id && is_numeric($field_id)) {
            $field = new Field($field_id);
            $this->view->field_name = $field->name;
            $this->view->field_label = $field->translation;
            $this->view->help_text = $field->help_text;
            $this->view->repeatable = $field->repeatable;
            $this->view->ordering = $field->ordering;
            $this->view->field_type = $field->type;
        }

        $this->view->fieldSettings = $field->fieldSettings();

        if (!isset($_POST['save'])) {
            return;
        }

        $field = new Field($field_id);
        $field->field_group_id = $_POST['group_id'];
        $field->name = $output = preg_replace('/[^A-Za-z0-9_]/i', '', $_POST['field_name']);
        $field->translation = $_POST['field_label'];
        $field->repeatable = $_POST['repeatable_field'];
        $field->type = $_POST['field_type'];
        $field->help_text = $_POST['help_text'];
        $field->ordering = $_POST['ordering'];

        $postName = $field->type.'Settings';
        if (isset($_POST[$postName]) && !empty($_POST[$postName])) {
            $field->settings = json_encode($_POST[$postName]);
        }

        $field->save();

        if ($field_id) {
            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('FIELD_SAVED'));
        } else {
            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('FIELD_ADDED'));
        }
        Redirect::to('content_field');
    }

    public function ordering()
    {
        $i = 0;
        $group_id = $_POST['group_id'];
        $ordering = $_POST['ordering'];

        foreach ($ordering as $k => $v) {
            $field = new Field($v);
            $field->ordering = $i++;
            $field->save();
        }
        $this->view->retval = 1;
    }

    public function delete($groupID = false)
    {
        if (!is_numeric($groupID)) {
            Redirect::to('content_field');
        }

        Security::verifyReferer();
        Security::verifyToken();

        $group = new Group((int) $groupID);
        $group->delete();

        $this->ui->addMessage(\Webvaloa\Webvaloa::translate('FIELD_DELETED'));
        Redirect::to('content_field');
    }

    public function deletefield($fieldID = false)
    {
        if (!is_numeric($fieldID)) {
            Redirect::to('content_field');
        }

        Security::verifyReferer();
        Security::verifyToken();

        $field = new Field((int) $fieldID);
        $field->delete();

        $this->ui->addMessage(\Webvaloa\Webvaloa::translate('FIELD_DELETED'));
        Redirect::to('content_field');
    }

    public function validategroup($name)
    {
        $fieldHelper = new FieldHelper();

        $response = new stdClass();
        $response->formattedname = $fieldHelper->formatName($name);
        $response->exists = $fieldHelper->groupExists($response->formattedname);

        Response::JSON($response);
    }

    public function validatefield($name)
    {
        $fieldHelper = new FieldHelper();

        $response = new stdClass();
        $response->formattedname = $fieldHelper->formatName($name);
        $response->exists = $fieldHelper->fieldExists($response->formattedname);

        Response::JSON($response);
    }
}
