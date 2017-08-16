<?php

/**
 * The Initial Developer of the Original Code is
 * 2017 Tarmo Alexander Sundström <ta@sundstrom.im>
 *
 * Contributor(s):
 *
 * This is free and unencumbered software released into the public domain.
 *
 * Anyone is free to copy, modify, publish, use, compile, sell, or
 * distribute this software, either in source code form or as a compiled
 * binary, for any purpose, commercial or non-commercial, and by any
 * means.
 *
 * In jurisdictions that recognize copyright laws, the author or authors
 * of this software dedicate any and all copyright interest in the
 * software to the public domain. We make this dedication for the benefit
 * of the public at large and to the detriment of our heirs and
 * successors. We intend this dedication to be an overt act of
 * relinquishment in perpetuity of all present and future rights to this
 * software under copyright law.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
 * OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 * For more information, please refer to <http://unlicense.org/>
 */

namespace Webvaloa\Mail;

use PHPMailer;
use Libvaloa\Debug;
use Webvaloa\Configuration;

class Mail
{
    public $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer();
        $this->mailer->CharSet = "UTF-8";

        $config = new Configuration();

        if ($config->phpmailer) {
            if (is_array($config->phpmailer)) {
                foreach ($config->phpmailer as $k => $v) {
                    $this->mailer->{$k} = $v;
                }
            }
        }
    }

    public function setTo($email, $name)
    {
        $this->mailer->addAddress($email, $name);
        return $this;
    }

    public function setSubject($subject)
    {
        $this->mailer->Subject = $subject;
        return $this;
    }

    public function setHtmlMessage($message)
    {
        $this->mailer->msgHTML($message);
        return $this;
    }

    public function setMessage($message)
    {
        $this->mailer->AltBody = $message;
        return $this;
    }

    public function addAttachment($path, $filename = null)
    {
        $this->mailer->addAttachment($path."/".$filename);
        return $this;
    }

    public function addAttachmentFilename($filename)
    {
        $this->mailer->addAttachment($filename);
        return $this;
    }

    public function setFrom($email, $name)
    {
        $this->mailer->setFrom($email, $name);
        return $this;
    }
    
    public function addCC($email, $name)
    {
        $this->mailer->addCC($email, $name);
        return $this;
    }

    public function send()
    {
        $mail = $this->mailer->send();
        Debug::__print($this->mailer->ErrorInfo);
        return $mail;
    }
}
