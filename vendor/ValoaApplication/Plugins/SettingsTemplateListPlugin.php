<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.io>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2014 Tarmo Alexander Sundström <ta@sundstrom.io>
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

namespace ValoaApplication\Plugins;

use Libvaloa\Debug\Debug;
use Webvaloa\Filesystem;
use stdClass;

/*
 * Get a list of installed templates to the view for the settings page.
 */
class SettingsTemplateListPlugin extends \Webvaloa\Plugin
{
    public function onAfterController()
    {
        // Get available templates
        try {
            $filesystem = new Filesystem(LIBVALOA_EXTENSIONSPATH.DIRECTORY_SEPARATOR.\Webvaloa\Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Layout');
            $templates = $filesystem->folders();
        } catch (\RuntimeException $e) {
            Debug::__print('Could not read layout path.');

            return;
        }

        if (!isset($templates)) {
            return;
        }

        // Look for template key in settings
        foreach ($this->view->settings as $k => $v) {
            $tmp[$k] = $v;

            if ($tmp[$k]->key == 'template') {
                foreach ($templates as $templateKey => $templateName) {
                    $obj = new stdClass();
                    $obj->value = $templateName;
                    $obj->translation = $templateName;
                    $tpls[] = $obj;
                }

                $tmp[$k]->values = $tpls;
                unset($tpls);
            }
        }

        if (isset($tmp)) {
            $this->view->settings = $tmp;
        }
    }
}
