<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

	<xsl:template match="index">
        <h1>Plugins</h1>
        <hr/>         

        <form method="get" action="{/page/common/basepath}/extension_plugin">
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

        <table class="table">
            <thead>
                <tr>
                    <th>
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ID')"/>
                    </th>
                    <th>
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PLUGIN')"/>
                    </th>
                    <th>
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PRIORITY')"/>
                    </th>
                    <th> </th>
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="plugins">
                    <tr data-id="{id}">
                        <td>
                            <xsl:value-of select="id"/>
                        </td>
                        <td>
                            <xsl:if test="system_plugin = '1'">
                                <span class="text-muted pull-right">
                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SYSTEM_PLUGIN')"/>
                                </span>
                            </xsl:if>

                            <xsl:value-of select="plugin"/>                                
                        </td>
                        <td>
                            <xsl:value-of select="ordering"/>
                        </td>
                        <td class="footable-last-column">
                            <div class="btn-group">
                                <a href="#" class="btn btn-primary edit-plugin-button" data-id="{id}" data-priority="{ordering}" data-toggle="modal" data-target="#edit-plugin">
                                    <i class="fa fa-pencil"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EDIT')"/>
                                </a>

                                <a href="{/page/common/basepath}/extension_plugin/toggle/{id}/{../pages/page}?token={../token}">
                                    <xsl:if test="system_plugin = '1'">
                                        <xsl:attribute name="class">btn btn-primary disabled</xsl:attribute>
                                    </xsl:if>
                                    <xsl:if test="system_plugin = '0' and blocked = '0'">
                                        <xsl:attribute name="class">btn btn-primary active toggle-plugin</xsl:attribute>
                                    </xsl:if>
                                    <xsl:if test="system_plugin = '0' and blocked = '1'">
                                        <xsl:attribute name="class">btn btn-primary toggle-plugin</xsl:attribute>
                                    </xsl:if>

                                    <i class="fa fa-check"></i>
                                </a>
                                <a href="{/page/common/basepath}/extension_plugin/uninstall/{plugin}?token={../token}" class="btn btn-danger confirm" data-message="{php:function('\Webvaloa\Webvaloa::translate','ARE_YOU_SURE')}">
                                    <xsl:if test="system_plugin = '1'">
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

        <div class="modal fade" id="edit-plugin" tabindex="-1" role="dialog" aria-labelledby="edit-user-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&#215;</button>
                        <h4 class="modal-title" id="edit-user-label">
                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EDIT_PLUGIN')"/>
                        </h4>
                    </div>
                    <div class="">
                        <form method="post" action="{/page/common/basepath}/extension_plugin/edit?token={token}" accept-charset="{/page/common/encoding}">
                            <input type="hidden" name="plugin_id" value="" id="edit-plugin-id"/>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group input-group-lg">
                                            <label for="editInputPriority">
                                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PRIORITY')" />
                                            </label>
                                            <input type="text" name="priority" class="form-control" id="editInputPriority" required="required" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CLOSE')"/>
                                </button>
                                <button type="submit" class="btn btn-success" id="edit-user-button">
                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SAVE')"/>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>        

	</xsl:template>

</xsl:stylesheet>
