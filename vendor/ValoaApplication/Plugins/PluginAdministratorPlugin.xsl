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

    <xsl:template name="PluginAdministratorPlugin" mode="plugin">

        <div class="navbar navbar-default" role="navigation" id="webvaloa">
            <xsl:attribute name="class">
                navbar navbar-default  
                <xsl:if test="/page/module//_settings/webvaloa_fixed_administrator_bar = 'yes'">navbar-fixed-top </xsl:if>
            </xsl:attribute>

            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="{/page/common/basehref}">
                        <xsl:choose>
                            <xsl:when test="/page/module//_settings/webvaloa_branding != ''">
                                <img src="{/page/common/basehref}/public/media/{/page/module//_settings/webvaloa_branding}" />
                            </xsl:when>
                            <xsl:otherwise>
                                <img src="{/page/common/basehref}/public/media/webvaloa-logo.png" />
                            </xsl:otherwise>
                        </xsl:choose>
                    </a>
                </div>
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <xsl:if test="/page/module//_permissions/showQuickAdd">
                            <li>&#160;&#160;&#160;</li>
                            <li>
                                <a href="#" id="quick-edit"><i class="fa fa-pencil"></i></a>
                            </li>

                            <xsl:if test="/page/module//_shortcuts != ''">
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <xsl:if test="/page/module//_shortcuts != ''">
                                            <li class="dropdown-header">
                                                <span>
                                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD', 'PluginAdministratorPlugin')"/>
                                                </span>
                                            </li>
                                            <xsl:for-each select="/page/module//_shortcuts">
                                                <li>
                                                    <a href="{/page/common/basepath}/content_article/add/{id}"><i class="fa fa-file"></i>&#160;
                                                        <xsl:value-of select="category" />
                                                    </a>
                                                </li>
                                            </xsl:for-each>
                                        </xsl:if>

                                        <xsl:if test="/page/module//_groups != ''">
                                            <li class="divider"></li>
                                            <xsl:for-each select="/page/module//_groups">
                                                <li>
                                                    <a href="{/page/common/basepath}/content_article/globals#{name}"><i class="fa fa-gear"></i>&#160;
                                                        <xsl:value-of select="translation" />
                                                    </a>
                                                </li>
                                            </xsl:for-each>
                                        </xsl:if>
                                    </ul>
                                </li>
                            </xsl:if>
                            <xsl:if test="/page/module//_permissions/showContent">
                                <li><a href="{/page/common/basepath}/content_media"><i class="fa fa-image"></i></a></li>
                            </xsl:if>
                        </xsl:if>

                        <xsl:if test="/page/module//_permissions/showContent">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CONTENT', 'PluginAdministratorPlugin')"/>&#160;
                                    <b class="caret"></b>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="dropdown-header">
                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CONTENT', 'PluginAdministratorPlugin')"/>
                                    </li>
                                    <li>
                                        <a href="{/page/common/basepath}/content_article"><i class="fa fa-file-text-o"></i>&#160;
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ARTICLES', 'PluginAdministratorPlugin')"/>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{/page/common/basepath}/content_category"><i class="fa fa-folder-o"></i>&#160;
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CATEGORIES', 'PluginAdministratorPlugin')"/>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{/page/common/basepath}/content_site"><i class="fa fa-navicon"></i>&#160;
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SITE_STRUCTURE', 'PluginAdministratorPlugin')"/>
                                        </a>
                                    </li>
                                    <xsl:if test="/page/module//_permissions/isAdmin">
                                        <li class="divider"></li>
                                        <li>
                                            <a href="{/page/common/basepath}/content_field"><i class="fa fa-database"></i>&#160;
                                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIELDS', 'PluginAdministratorPlugin')"/>
                                            </a>
                                        </li>
                                    </xsl:if>
                                    <li class="divider"></li>
                                    <li class="dropdown-header">
                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','MEDIA', 'PluginAdministratorPlugin')"/>
                                    </li>
                                    <li>
                                        <a href="{/page/common/basepath}/content_media"><i class="fa fa-image"></i>&#160;
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','MEDIA_MANAGER', 'PluginAdministratorPlugin')"/>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </xsl:if>

                        <xsl:if test="/page/module//_permissions/showUsers">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','USERS', 'PluginAdministratorPlugin')"/>&#160;
                                    <b class="caret"></b>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="{/page/common/basepath}/user"><i class="fa fa-user"></i>&#160;
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','USERS', 'PluginAdministratorPlugin')"/>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{/page/common/basepath}/user_role"><i class="fa fa-key"></i>&#160;
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ROLES', 'PluginAdministratorPlugin')"/>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </xsl:if>

                        <xsl:if test="/page/module//_permissions/showExtensions">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EXTENSIONS', 'PluginAdministratorPlugin')"/>&#160;
                                    <b class="caret"></b>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="{/page/common/basepath}/extension_install"><i class="fa fa-download"></i>&#160;
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','INSTALL', 'PluginAdministratorPlugin')"/>...</a>
                                    </li>

                                    <li>
                                        <a href="{/page/common/basepath}/extension_component"><i class="fa fa-gears"></i>&#160;
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','COMPONENTS', 'PluginAdministratorPlugin')"/>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{/page/common/basepath}/extension_plugin"><i class="fa fa-bolt"></i>&#160;
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PLUGINS', 'PluginAdministratorPlugin')"/>
                                        </a>
                                    </li>

                                    <xsl:if test="/page/module//_extensions">
                                        <li class="divider"></li>
                                        <li class="dropdown-header">
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EXTENSIONS', 'PluginAdministratorPlugin')"/>
                                        </li>
                                        <xsl:for-each select="/page/module//_extensions">
                                            <li>
                                                <a href="{link}"><i class="fa fa-gear"></i>&#160;
                                                    <xsl:value-of select="translation" />
                                                </a>
                                            </li>
                                        </xsl:for-each>
                                    </xsl:if>

                                </ul>
                            </li>
                        </xsl:if>

                        <xsl:if test="/page/module//_permissions/showSettings">
                            <li>
                                <a href="{/page/common/basepath}/settings">
                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SETTINGS', 'PluginAdministratorPlugin')"/>
                                </a>
                            </li>
                        </xsl:if>
                    </ul>

                    <ul class="nav navbar-nav navbar-right">

                        <!-- Profile bar based on http://bootsnipp.com/BhaumikPatel/snippets/68pM under MIT license. -->
                        <xsl:choose>
                            <xsl:when test="/page/module//_permissions/showProfile">
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        <i class="fa fa-user"></i>
                                        <b class="caret"></b>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <div class="navbar-content">
                                                <div class="row">
                                                    <div class="col-md-5">
                                                        <img src="{/page/module//_gravatar}"
                                                             alt="" class="img-responsive" />
                                                    </div>
                                                    <div class="col-md-7">
                                                        <span>
                                                            <a href="{/page/common/basepath}/profile">
                                                                <xsl:value-of select="/page/module//_name"/>
                                                            </a>
                                                        </span>
                                                        <p class="text-muted small">
                                                            <xsl:value-of select="/page/module//_email"/>
                                                        </p>
                                                        <div class="divider">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="navbar-footer">
                                                <div class="navbar-footer-content">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <a href="{/page/common/basepath}/password" class="btn btn-default btn-sm">
                                                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CHANGE_PASSWORD', 'PluginAdministratorPlugin')"/>
                                                            </a>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <a href="{/page/common/basepath}/login_logout?logout&amp;token={/page/module//token}" class="btn btn-default btn-sm pull-right">
                                                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SIGN_OUT', 'PluginAdministratorPlugin')"/>&#160;<i class="fa fa-power-off"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </li>                                

                            </xsl:when>

                            <!-- Show just logout button when profile is disabled -->
                            <xsl:otherwise>
                                <li>
                                    <a href="{/page/common/basehref}/login_logout?logout&amp;token={/page/module//token}">
                                        <i class="fa fa-power-off"></i>
                                    </a>
                                </li>               
                            </xsl:otherwise>
                        </xsl:choose>
                    </ul>
                </div>
            </div>
        </div>

    </xsl:template>

</xsl:stylesheet>
