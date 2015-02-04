<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

	<xsl:template match="index">
        <h1><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','COMPONENTS')"/></h1>
        <hr/>

        <form method="get" action="{/page/common/basepath}/extension_component">
            <div class="row">
                <div class="col-lg-9">

                </div>
                <div class="col-lg-3">
                    <div class="input-group webvaloa-search-form">
                        <input type="text" value="{search}" name="search" class="form-control" id="search" placeholder="{php:function('\Webvaloa\Webvaloa::translate','SEARCH')}" />
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="submit">
                                <i class="fa fa-search"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </form>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ID')"/>
                    </th>
                    <th>
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','COMPONENT')"/>
                    </th>
                    <th> </th>
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="components">
                    <tr data-id="{id}">
                        <td>
                            <xsl:value-of select="id"/>
                        </td>
                        <td>
                            <xsl:if test="system_component = '1'">
                                <span class="text-muted pull-right">
                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SYSTEM_COMPONENT')"/>
                                </span>
                            </xsl:if>

                            <xsl:value-of select="controller"/>                                
                        </td>
                        <td class="footable-last-column">
                            <div class="btn-group">
                                <a href="{/page/common/basepath}/settings/{controller}" class="btn btn-primary">
                                    <i class="fa fa-wrench"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SETTINGS')"/>
                                </a>
                                <a href="{/page/common/basepath}/extension_component/toggle/{id}/{../pages/page}?token={../token}">
                                    <xsl:if test="system_component = '1'">
                                        <xsl:attribute name="class">btn btn-primary disabled</xsl:attribute>
                                    </xsl:if>
                                    <xsl:if test="system_component = '0' and blocked = '0'">
                                        <xsl:attribute name="class">btn btn-primary active toggle-component</xsl:attribute>
                                    </xsl:if>
                                    <xsl:if test="system_component = '0' and blocked = '1'">
                                        <xsl:attribute name="class">btn btn-primary toggle-component</xsl:attribute>
                                    </xsl:if>

                                    <i class="fa fa-check"></i>
                                </a>
                                <a href="{/page/common/basepath}/extension_component/uninstall/{controller}?token={../token}" class="btn btn-danger confirm" data-message="{php:function('\Webvaloa\Webvaloa::translate','ARE_YOU_SURE')}">
                                    <xsl:if test="system_component = '1'">
                                        <xsl:attribute name="class">btn btn-primary disabled</xsl:attribute>
                                    </xsl:if>

                                    <i class="fa fa-minus"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                </xsl:for-each>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">
                        <xsl:call-template name="pagination">
                            <xsl:with-param name="url"><xsl:value-of select="/page/common/basepath"/><xsl:value-of select="pages/url"/></xsl:with-param>
                            <xsl:with-param name="pageCurrent"><xsl:value-of select="pages/page"/></xsl:with-param>
                            <xsl:with-param name="pageNext"><xsl:value-of select="pages/pageNext"/></xsl:with-param>
                            <xsl:with-param name="pagePrev"><xsl:value-of select="pages/pagePrev"/></xsl:with-param>
                            <xsl:with-param name="pageCount"><xsl:value-of select="pages/pages"/></xsl:with-param>
                        </xsl:call-template>
                    </td>
                </tr>
            </tfoot>            
        </table>
            
	</xsl:template>

    <xsl:template match="uninstall">
        <h1><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','WARNING')"/>!</h1>
        <hr/>
        <p><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','COMPONENT_UNINSTALL_NOTICE')"/></p>
        
        <div id="messages">
            <xsl:call-template name="messages" />
        </div>

        <pre><xsl:value-of select="schema"/></pre>

        <form method="get" action="{/page/common/basepath}/extension_component" class="pull-left">
            <input type="submit" class="btn btn-default" value="{php:function('\Webvaloa\Webvaloa::translate','CANCEL')}"/>
        </form>

        <form method="get" action="{/page/common/basepath}/extension_component/uninstall/{controller}">
            <input type="hidden" name="token" value="{token}"/>
            <input type="hidden" name="verify" value="1"/>

            <input type="submit" class="btn btn-danger pull-right" value="{php:function('\Webvaloa\Webvaloa::translate','CONTINUE')}"/>
        </form>
    </xsl:template>

</xsl:stylesheet>
