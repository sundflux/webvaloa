<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

    <!--
    The Initial Developer of the Original Code is
    Tarmo Alexander Sundström <ta@sundstrom.im>

    Portions created by the Initial Developer are
    Copyright (C) 2014 Tarmo Alexander Sundström <ta@sundstrom.im>

    All Rights Reserved.

    Contributor(s):

    Permission is hereby granted, free of charge, to any person obtaining a
    copy of this software and associated documentation files (the "Software"),
    to deal in the Software without restriction, including without limitation
    the rights to use, copy, modify, merge, publish, distribute, sublicense,
    and/or sell copies of the Software, and to permit persons to whom the
    Software is furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included
    in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
    THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
    IN THE SOFTWARE.
    -->

    <xsl:template name="progress-bar">
        <div class="row setup-progress visible-md visible-lg">
            <div class="col-xs-6 col-md-4">
                <span class="text-muted">
                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CHOOSE_LANGUAGE')"/>
                </span>
                <br/>
                <span class="glyphicon glyphicon-chevron-down text-muted"></span>
            </div>
            <div class="col-xs-6 col-md-4">
                <span class="text-muted">
                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','DATABASE_SETTINGS')"/>
                </span>
                <br/>
                <span class="glyphicon glyphicon-chevron-down text-muted"></span>
            </div>
            <div class="col-xs-6 col-md-4">
                <span class="text-muted">
                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADMINISTRATOR_ACCOUNT')"/>
                </span>
                <br/>
                <span class="glyphicon glyphicon-chevron-down text-muted"></span>
            </div>
        </div>
    </xsl:template>

    <xsl:template name="progress-bar-element">
        <xsl:param name="percent">0</xsl:param>
        <div id="progress-bar"
            class="progress progress-striped active"
            data-toggle="tooltip"
            data-placement="top"
            title="{php:function('\Webvaloa\Webvaloa::translate','PROGRESS_BAR_TEXT')}">
            <div class="progress-bar"  role="progressbar" aria-valuenow="{$percent}" aria-valuemin="0" aria-valuemax="100" style="width: {$percent}%">
                <span class="sr-only"></span>
            </div>
        </div>
    </xsl:template>

    <xsl:template match="index">
        <div id="wrap">
            <div id="logo">
                <img src="{/page/common/basepath}/public/media/webvaloa-logo.png" />
            </div>

            <br/>

            <form method="post"  action="{/page/common/basepath}/setup" accept-charset="{/page/common/encoding}">
                <div class="container">

                    <xsl:call-template name="progress-bar" />
                    <xsl:call-template name="progress-bar-element">
                        <xsl:with-param name="percent">15</xsl:with-param>
                    </xsl:call-template>

                    <p class="lead">
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','MY_NAME_IS')"/>&#160;<span class="webvaloa">SetupController</span>,
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','I_LIVE_IN_NAMESPACE')"/>&#160;<span class="webvaloa">Webvaloa\Controllers\Setup</span>.
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','HERE_TO_HELP')"/> :-)
                    </p>

                    <div class="row" id="footer">
                        <div class="col-md-6">
                            <div class="dropup">
                            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-expanded="true">
                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CHOOSE_LANGUAGE')"/>  
                                &#160;<span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu2">
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="{/page/common/basepath}/setup/en_US">In english</a></li>
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="{/page/common/basepath}/setup/fi_FI">Suomeksi</a></li>
                            </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            
                            <button type="submit" name="continue" class="btn btn-success pull-right">
                                <xsl:if test="notWritable">
                                    <xsl:attribute name="disabled">disabled</xsl:attribute>
                                </xsl:if>
                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ALL_GOOD')"/>&#160;<span class="glyphicon glyphicon-thumbs-up"></span>
                            </button>

                        </div>
                    </div>

                </div>
            </form>
        </div>
    </xsl:template>

    <xsl:template match="database">
        <div id="wrap">
            <div id="logo">
                <img src="{/page/common/basepath}/public/media/webvaloa-logo.png" />
            </div>

            <br/>

            <form method="post"  action="{/page/common/basepath}/setup/database" accept-charset="{/page/common/encoding}">
                <div class="container">

                    <xsl:call-template name="progress-bar" />
                    <xsl:call-template name="progress-bar-element">
                        <xsl:with-param name="percent">50</xsl:with-param>
                    </xsl:call-template>

                    <p class="lead">
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','DATABASE_HELP')"/>
                    </p>

                    <div class="form-group input-group-lg">
                        <input type="hidden" name="db_server" value="mysql" id="db-server-field"/>
                        <label for="inputHost"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','DATABASE_SERVER')" /></label>
                        <br/>
                        <div class="btn-group ">
                            <button type="button" class="btn btn-default" id="db-server-desc">
                                <xsl:choose>
                                    <xsl:when test="db_server = 'postgres'">
                                        PostgreSQL
                                    </xsl:when>
                                    <xsl:otherwise>
                                        MySQL / MariaDB
                                    </xsl:otherwise>
                                </xsl:choose>
                            </button>
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="javascript:;" onclick="Setup.db('mysql')">MySQL / MariaDB</a></li>
                                <!-- PostgreSQL support not yet done -->
                                <li class="disabled"><a href="javascript:;" onclick="return false;Setup.db('postgres')" disabled="disabled">PostgreSQL</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group input-group-lg">
                        <xsl:if test="errors['db_host']">
                            <xsl:attribute name="class">form-group input-group-lg has-error</xsl:attribute>
                        </xsl:if>
                        <label for="inputHost"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SERVER_HOSTNAME')" /></label>
                        <input type="text" name="db_host" class="form-control" id="inputHost" placeholder="{php:function('\Webvaloa\Webvaloa::translate','SERVER_HOSTNAME')}" value="{db_host}" />
                    </div>

                    <div class="form-group input-group-lg">
                        <xsl:if test="errors['db_db']">
                            <xsl:attribute name="class">form-group input-group-lg has-error</xsl:attribute>
                        </xsl:if>
                        <label for="inputDB"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','DATABASE')" /></label>
                        <input type="text" name="db_db" class="form-control" id="inputDB" placeholder="{php:function('\Webvaloa\Webvaloa::translate','DATABASE')}" value="{db_db}" />
                    </div>

                    <div class="form-group input-group-lg">
                        <xsl:if test="errors['db_user']">
                            <xsl:attribute name="class">form-group input-group-lg has-error</xsl:attribute>
                        </xsl:if>
                        <label for="inputUser"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','USERNAME')" /></label>
                        <input type="text" name="db_user" class="form-control" id="inputUser" placeholder="{php:function('\Webvaloa\Webvaloa::translate','USERNAME')}" value="{db_user}" />
                    </div>

                    <div class="form-group input-group-lg">
                        <xsl:if test="errors['db_pass']">
                            <xsl:attribute name="class">form-group input-group-lg has-error</xsl:attribute>
                        </xsl:if>
                        <label for="inputPass"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PASSWORD')" /></label>
                        <input type="password" name="db_pass" class="form-control" id="inputPass" placeholder="{php:function('\Webvaloa\Webvaloa::translate','PASSWORD')}" value="{db_pass}" />
                    </div>

                    <br/>

                    <div class="row" id="footer">
                        <div class="col-md-12">
                            <button type="submit" name="continue" class="btn btn-success pull-right">
                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ALL_GOOD')"/>&#160;<span class="glyphicon glyphicon-thumbs-up"></span>
                            </button>

                            <button type="submit" name="back" class="btn btn-default pull-left">
                                <span class="glyphicon glyphicon-chevron-left"></span>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','BACK')" />
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </xsl:template>

    <xsl:template match="admin">
        <div id="wrap">
            <div id="logo">
                <img src="{/page/common/basepath}/public/media/webvaloa-logo.png" />
            </div>

            <br/>

            <form method="post"  action="{/page/common/basepath}/setup/admin" accept-charset="{/page/common/encoding}">
                <div class="container">

                    <xsl:call-template name="progress-bar" />
                    <xsl:call-template name="progress-bar-element">
                        <xsl:with-param name="percent">85</xsl:with-param>
                    </xsl:call-template>

                    <p class="lead">
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADMIN_HELP')"/>
                    </p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group input-group-lg">
                                <label for="inputFirstname"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIRSTNAME')" />
                                    <br/>
                                    <span class="text-muted"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIRSTNAME_HELP')" /></span>
                                </label>
                                <input type="text" name="admin_firstname" class="form-control" id="inputFirstname" placeholder="{php:function('\Webvaloa\Webvaloa::translate','FIRSTNAME')}" value="{admin_firstname}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group input-group-lg">
                                <label for="inputLastname"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','LASTNAME')" />
                                    <br/>
                                    <span class="text-muted"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','LASTNAME_HELP')" /></span>
                                </label>
                                <input type="text" name="admin_lastname" class="form-control" id="inputLastname" placeholder="{php:function('\Webvaloa\Webvaloa::translate','LASTNAME')}" value="{admin_lastname}" />
                            </div>
                        </div>                   
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group input-group-lg">
                                <label for="inputEmail"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EMAIL')" />
                                    <br/>
                                    <span class="text-muted"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EMAIL_HELP')" /></span>
                                </label>
                                <input type="email" name="admin_email" class="form-control" id="inputEmail" placeholder="{php:function('\Webvaloa\Webvaloa::translate','EMAIL')}" value="{admin_email}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group input-group-lg">
                                <label for="inputUsername"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CUSTOM_USERNAME')" />
                                    <br/>
                                    <span class="text-muted"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CUSTOM_USERNAME_HELP')" /></span>
                                </label>
                               <div class="row">
                                   <div class="col-lg-12">
                                       <div class="input-group  input-group-lg">
                                           <span class="input-group-addon">
                                               <input type="checkbox" onclick="Setup.toggleDisabled('inputUsername')">
                                                   <xsl:if test="admin_username != ''">
                                                       <xsl:attribute name="checked">checked</xsl:attribute>
                                                   </xsl:if>
                                               </input>
                                            </span>
                                            <input type="text" class="form-control" id="inputUsername" name="admin_username" value="{admin_username}">
                                                <xsl:if test="not(admin_username) or admin_username = ''">
                                                    <xsl:attribute name="disabled">disabled</xsl:attribute>
                                                </xsl:if>
                                            </input>
                                      </div>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>

                    <hr/>

                    <div class="form-group input-group-lg">
                        <label for="inputPassword"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PASSWORD')" /></label>
                        <input type="password" name="admin_password" class="form-control" id="inputPassword" placeholder="{php:function('\Webvaloa\Webvaloa::translate','PASSWORD')}" />
                    </div>

                    <div class="form-group input-group-lg">
                        <label for="inputPassword2"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PASSWORD_CONFIRM')" /></label>
                        <input type="password" name="admin_password2" class="form-control" id="inputPassword2" placeholder="{php:function('\Webvaloa\Webvaloa::translate','PASSWORD_CONFIRM')}" />
                    </div>

                    <div class="form-group input-group-lg">
                        <xsl:if test="errors['tz']">
                            <xsl:attribute name="class">form-group input-group-lg has-error</xsl:attribute>
                        </xsl:if>
                        <label for="inputTZ"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TIME_ZONE')" /></label>
                        <select name="tz" class="form-control" id="inputTZ" placeholder="{php:function('\Webvaloa\Webvaloa::translate','TIME_ZONE')}">
                            <xsl:for-each select="timezones">
                                <option value="{.}">
                                    <xsl:if test="../timezone = .">
                                        <xsl:attribute name="selected">selected</xsl:attribute>
                                    </xsl:if>
                                    <xsl:value-of select="."/>
                                </option>
                            </xsl:for-each>
                        </select>
                    </div>

                    <br/>

                    <div class="row" id="footer">
                        <div class="col-md-12">
                            <button type="submit" name="continue" class="btn btn-success pull-right">
                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ALL_GOOD')"/>&#160;<span class="glyphicon glyphicon-thumbs-up"></span>
                            </button>
                            <button type="submit" name="back" class="btn btn-default pull-left">
                                <span class="glyphicon glyphicon-chevron-left"></span>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','BACK')" />
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </xsl:template>

    <xsl:template match="ready">
        <div id="wrap">
            <div id="logo">
                <img src="{/page/common/basepath}/public/media/webvaloa-logo.png" />
            </div>

            <br/>

            <div class="container">
                <p class="lead">
                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ALL_DONE')"/>
                </p>
                <p><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','REDIRECTING')"/></p>
            </div>

        </div>
        <script type="text/javascript">
            setTimeout("location.href = '<xsl:value-of select="/page/common/basehref"/>/login';",3000);
        </script>
    </xsl:template>

</xsl:stylesheet>

