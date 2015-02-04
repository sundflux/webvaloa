<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

    <xsl:template match="index">
        <h1>
            <button class="btn btn-success pull-right load-controllers add-role" data-toggle="modal" data-target="#add-role">
                <i class="fa fa-plus"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD')"/>
            </button>
            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ROLES')"/>
        </h1>
        <hr/>
        
        <form method="get" action="{/page/common/basepath}/user_role">
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
        
        <table class="table table-striped footable" data-filter="#search">
            <thead>
                <tr>
                    <th>
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ID')"/>
                    </th>
                    <th>
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ROLE')"/>
                    </th>
                    <th> </th>
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="roles">
                    <tr>
                        <td>
                            <xsl:value-of select="id"/>
                        </td>
                        <td>
                            <xsl:if test="system_role = '1'">
                                <xsl:attribute name="class">text-muted</xsl:attribute>
                            </xsl:if>
                            
                            <xsl:value-of select="role"/>
                        </td>
                        <td class="footable-last-column">
                            <div class="btn-group">
                                <a href="#" class="btn btn-primary edit-role load-controllers" data-id="{id}" data-role="{role}" data-toggle="modal" data-target="#add-role">
                                    <i class="fa fa-pencil"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EDIT')"/>
                                </a>

                                <a class="btn btn-danger confirm" data-message="{php:function('\Webvaloa\Webvaloa::translate','ARE_YOU_SURE')}">
                                    <xsl:choose>
                                        <xsl:when test="system_role = '1'">  
                                            <xsl:attribute name="href">javascript:;</xsl:attribute>
                                            <xsl:attribute name="disabled">disabled</xsl:attribute>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:attribute name="href">
                                                <xsl:value-of select="/page/common/basepath"/>/user_role/delete/<xsl:value-of select="id"/>?token=<xsl:value-of select="../token"/>
                                            </xsl:attribute>
                                        </xsl:otherwise>
                                    </xsl:choose>
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
        
        <div class="modal fade" id="add-role" tabindex="-1" role="dialog" aria-labelledby="add-role-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&#215;</button>
                        <h4 class="modal-title" id="add-role-label">
                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD_ROLE')"/>
                        </h4>
                    </div>
                    <div class="">
                        <form method="post" action="{/page/common/basepath}/user_role/add?token={token}" accept-charset="{/page/common/encoding}">
                            <input type="hidden" value="" name="role_id" id="role_id"/>

                            <div class="modal-body">

                                <div class="form-group input-group-lg">
                                    <label for="inputRole">
                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ROLE_NAME')" />
                                    </label>
                                    <input type="text" name="role" class="form-control" id="inputRole" placeholder="{php:function('\Webvaloa\Webvaloa::translate','ROLE_NAME')}" value="{role}" required="required" />
                                </div>

                                <div class="form-group input-group-lg">
                                    <label for="inputRole">
                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CONTROLLER_PERMISSIONS')" />
                                    </label>
                                    <div id="controllers-list"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','LOADING_CONTROLLERS')" /></div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CLOSE')"/>
                                </button>
                                <button type="submit" class="btn btn-success" id="add-user-button">
                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SAVE')"/>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>        
        
        <div id="basehref" class="hide">
            <xsl:value-of select="/page/common/basehref"/>
        </div>

        <!-- Translation helpers -->
        <div id="translate" class="hide">
            <span id="translation-add" data-translation-string="{php:function('\Webvaloa\Webvaloa::translate','ADD_ROLE')}"/>
            <span id="translation-edit" data-translation-string="{php:function('\Webvaloa\Webvaloa::translate','EDIT_ROLE')}"/>
        </div>        
    </xsl:template>

    <xsl:template match="controllers">
        <select name="components[]" multiple="multiple" class="form-control components">
            <xsl:for-each select="components">
                <option value="{id}">
                    <xsl:if test="selected">
                        <xsl:attribute name="selected">selected</xsl:attribute>
                    </xsl:if>
                    <xsl:value-of select="controller"/>
                </option>
            </xsl:for-each>
        </select>
    </xsl:template>

</xsl:stylesheet>
