<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

	<xsl:template match="index">
        <h1>
            <a class="btn btn-success pull-right" href="{/page/common/basepath}/content_site/add">
                <i class="fa fa-plus"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD_MENU_ITEM')"/>
            </a>
            Site structure
        </h1>
		<div class="panel panel-default">
            <br/>
            <div id="editable">
                <xsl:apply-templates select="editablemenu/navigation" mode="editablemenu"/>
            </div>
            <br/>
		</div>
	</xsl:template>

    <xsl:template match="/page/module/*/*/navigation" mode="editablemenu">
        <xsl:call-template name="naviEditable"/>
    </xsl:template>

    <xsl:template name="naviEditable">
        <ul>
            <xsl:for-each select="sub">
                <li data-id="{id}" data-ordering="">
                    <div class="btn-group">
                        <button class="btn btn-default moveup"><i class="fa fa-angle-up"></i></button>
                        <button class="btn btn-default movedown"><i class="fa fa-angle-down"></i></button>
                    </div>
                    <button class="btn btn-default"><i class="fa fa-pencil"></i></button>
                    <span><xsl:value-of select="translation"/></span>

                    <xsl:if test="sub">
                        <xsl:call-template name="naviEditable"/>
                    </xsl:if>
                </li>
            </xsl:for-each>
        </ul>
    </xsl:template>

</xsl:stylesheet>
