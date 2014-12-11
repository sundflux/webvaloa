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

namespace ValoaApplication\Controllers\Error;

use Libvaloa\Debug;

class ErrorController extends \Webvaloa\Application
{

    private $backend;

    public function __construct()
    {
        $this->ui->addCSS("/css/Error.css");
    }

    public function index()
    {
        if (isset($_SESSION['WEBVALOA_EXCEPTION'])) {
            // Errors
            $this->view->errorMessage = $_SESSION['WEBVALOA_EXCEPTION']->getMessage();
            $trace = $_SESSION['WEBVALOA_EXCEPTION']->getTrace();
            $this->view->errorClass = $trace[0]['class'];

            // Debugs
            Debug::__print($trace);
            Debug::__print($_SESSION['WEBVALOA_EXCEPTION']);

            // Unset the error message
            unset($_SESSION['WEBVALOA_EXCEPTION']);
        }
    }

}
