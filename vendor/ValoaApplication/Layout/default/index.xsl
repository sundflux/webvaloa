<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">
    <xsl:output method="html"
        version="5.0"
        encoding="utf-8"
        indent="yes"
        omit-xml-declaration="yes" />

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

    <xsl:template match="/page">
        <html>
            <xsl:attribute name="lang">en</xsl:attribute>
            <head>
                <meta name="viewport" content="width=device-width" />

                <title>
                    <xsl:value-of select="/page/module/*/sitetitle"/>
                </title>
                <base href="{common/basehref}"/>

                <!-- Latest compiled and minified CSS -->
                <link rel="stylesheet" href="{/page/common/basepath}/public/Layout/bootstrap/css/bootstrap.min.css" />
                <link rel="stylesheet" href="{/page/common/basepath}/public/Layout/bootstrap/css/bootstrap-theme.min.css" />
                <link rel="stylesheet" href="{/page/common/basepath}/public/Layout/bootstrap/css/valoa.css" />
                <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" />

                <!-- Component CSS -->
                <xsl:for-each select="common/css">
                    <link rel="stylesheet" type="text/css" href="{../basepath}/public/Layout{.}" title="style"/>
                </xsl:for-each>

                <link rel="shortcut icon" href="{common/basehref}/public/Layout/{/page/common/layout}/favicon.ico" />

                <!-- Javascripts: -->

                <!-- jQuery -->
                <script src="{/page/common/basepath}/public/Layout/jquery/jquery-1.11.0.min.js"></script>

                <!-- Latest compiled and minified JavaScript -->
                <script src="{/page/common/basepath}/public/Layout/bootstrap/js/bootstrap.min.js"></script>

                <!-- Javascripts -->
                <xsl:for-each select="common/js">
                    <script type="text/javascript" src="{../basepath}/public/Layout{.}"/>
                </xsl:for-each>
            </head>
            <body>
                <div class="container main">
                    <!-- Messages -->
                    <div id="messages">
                        <xsl:call-template name="messages" />
                    </div>

                    <!-- Component output -->
                    <xsl:apply-templates select="/page/module"/>
                    <xsl:apply-templates select="navi"/>
                </div>
                <br/>
            </body>
        </html>
    </xsl:template>
    
    <xsl:template name="messages">
        <!-- Messages -->
        <xsl:if test="/page/common/messages">
            <xsl:for-each select="/page/common/messages">
                <xsl:choose>
                    <xsl:when test="type='message'">
                        <div class="alert alert-success">
                            <xsl:value-of select="item"/>
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&#215;</button>
                        </div>
                    </xsl:when>
                    <xsl:when test="type='error'">
                        <div class="alert alert-danger">
                            <xsl:value-of select="item"/>
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&#215;</button>
                        </div>
                    </xsl:when>
                    <xsl:otherwise>
                        <div class="alert alert-info">
                            <xsl:value-of select="item"/>
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&#215;</button>
                        </div>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:for-each>
        </xsl:if>
    </xsl:template>
    
    <xsl:template name="loader">
        <div id="loader">
            <div class="loader-wrapper">
                <div id="loader_1" class="loader">
                </div>
                <div id="loader_2" class="loader">
                </div>
                <div id="loader_3" class="loader">
                </div>
                <div id="loader_4" class="loader">
                </div>
                <div id="loader_5" class="loader">
                </div>
                <div id="loader_6" class="loader">
                </div>
                <div id="loader_7" class="loader">
                </div>
                <div id="loader_8" class="loader">
                </div>
            </div>
        </div>        
    </xsl:template>

</xsl:stylesheet>
