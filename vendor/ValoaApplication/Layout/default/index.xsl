<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">
    <xsl:output
    method="html"
    doctype-system="about:legacy-compat"
    indent="yes" />

    <xsl:template match="/page">
        <html>
            <xsl:attribute name="lang">en</xsl:attribute>
            <head>
               <meta charset="UTF-8"/>
               <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"/>
               <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
               <meta http-equiv="Content-Language" content="en"/>
               <meta name="msapplication-TileColor" content="#2d89ef"/>
               <meta name="theme-color" content="#4188c9"/>
               <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
               <meta name="apple-mobile-web-app-capable" content="yes"/>
               <meta name="mobile-web-app-capable" content="yes"/>
               <meta name="HandheldFriendly" content="True"/>
               <meta name="MobileOptimized" content="320"/>
               <title>
                    <xsl:choose>
                        <xsl:when test="/page/module/*/_globals/site_title/value != ''">
                            <xsl:value-of select="/page/module/*/_globals/site_title/value"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="/page/module/*/sitetitle"/>
                        </xsl:otherwise>
                    </xsl:choose>
                    <xsl:if test="/page/module/*/_globals/site_title_page/value = '1' and /page/module/*/article/title != ''">
                        <xsl:value-of select="/page/module/*/_globals/site_title_separator/value"/><xsl:value-of select="/page/module/*/article/title"/>
                    </xsl:if>
                </title>

                <base href="{common/basehref}"/>

                <!-- Global CSS -->
                <link href="{/page/common/basepath}/public/themes/default/css/dashboard.css" rel="stylesheet"/>
                <link href="{/page/common/basepath}/public/general/css/valoa.css" rel="stylesheet"/>
                <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>

                <!-- Component CSS -->
                <xsl:for-each select="common/css">
                    <link rel="stylesheet" type="text/css" href="{../basepath}/public/extensions{.}" title="style"/>
                </xsl:for-each>

                <!-- Global JS -->
                <script src="{/page/common/basepath}/public/themes/default/js/require.min.js"></script>
                <script>
                  requirejs.config({
                    baseUrl: '.'
                  });
                </script>
                <script src="{/page/common/basepath}/public/themes/default/js/dashboard.js"></script>

                <!-- Component JS -->
                <xsl:for-each select="common/js">
                    <script type="text/javascript" src="{../basepath}/public/extensions{.}"></script>
                </xsl:for-each>
            </head>
            <body>
                <div class="page">
                    <!-- Messages -->
                    <div id="messages">
                        <xsl:call-template name="messages" />
                    </div>

                    <!-- Component output -->
                    <xsl:apply-templates select="/page/module"/>

                    <!-- Example use of navigation -->
                    <!-- <xsl:apply-templates select="/page/module/*/_navigation/navigation" mode="navigation" /> -->
                </div>
                <br/>
            </body>
        </html>
    </xsl:template>
    
    <xsl:template match="/page/module/*/_navigation/navigation" mode="navigation">
        <xsl:call-template name="navi">
            <xsl:with-param name="article_id" select="/page/module/*/article/id" />
        </xsl:call-template>
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
    
</xsl:stylesheet>
