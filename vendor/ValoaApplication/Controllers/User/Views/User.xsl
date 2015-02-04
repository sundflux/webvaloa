<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

    <xsl:template match="index">
        <h1>
            <button class="btn btn-success pull-right load-roles" data-id="" data-toggle="modal" data-target="#add-user-modal">
                <i class="fa fa-plus"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD')"/>
            </button>
            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','USERS')"/>
        </h1>
        <hr/>           
        
        <form method="get" action="{/page/common/basepath}/user">
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
                    <th></th>
                    <th>
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ID')"/>
                    </th>
                    <th data-hide="phone,tablet">
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIRSTNAME')"/>
                    </th>
                    <th data-hide="phone,tablet">
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','LASTNAME')"/>
                    </th>
                    <th>
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','USERNAME')"/>
                    </th>
                    <th data-hide="phone,tablet">
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EMAIL')"/>
                    </th>
                    <th> </th>
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="users">
                    <tr data-id="{id}" data-firstname="{firstname}" data-lastname="{lastname}" data-username="{login}" data-email="{email}">
                        <td>
                            <img src="{gravatar}" alt="" />
                        </td>
                        <td>
                            <xsl:value-of select="id"/>
                        </td>
                        <td data-hide="phone,tablet">
                            <xsl:value-of select="firstname"/>
                        </td>
                        <td data-hide="phone,tablet">
                            <xsl:value-of select="lastname"/>
                        </td>
                        <td>
                            <xsl:value-of select="login"/>
                        </td>
                        <td data-hide="phone,tablet">
                            <xsl:value-of select="email"/>
                        </td>
                        <td class="footable-last-column">
                            <div class="btn-group">
                                <a href="#" class="btn btn-primary" data-id="{id}" data-toggle="modal" data-target="#edit-user">
                                    <i class="fa fa-pencil"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EDIT')"/>
                                </a>

                                <xsl:choose>
                                    <xsl:when test="id = ../user_id">
                                        <a href="#" class="btn btn-danger" disabled="disabled">
                                            <i class="fa fa-minus"></i>
                                        </a>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <a href="{/page/common/basepath}/user/delete/{id}?token={../token}" class="btn btn-danger confirm" data-message="{php:function('\Webvaloa\Webvaloa::translate','ARE_YOU_SURE')}">
                                            <i class="fa fa-minus"></i>
                                        </a>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </div>
                        </td>
                    </tr>
                </xsl:for-each>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7">
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

        <div class="modal fade" id="edit-user" tabindex="-1" role="dialog" aria-labelledby="edit-user-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&#215;</button>
                        <h4 class="modal-title" id="edit-user-label">
                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EDIT_USER')"/>
                        </h4>
                    </div>
                    <div class="">
                        <form method="post" action="{/page/common/basepath}/user/edit?token={token}" accept-charset="{/page/common/encoding}">

                            <div class="modal-body">
                                <ul class="nav nav-tabs nav-justified" id="edit-user-info-tab" data-tabs="tabs">
                                    <li class="active">
                                        <a href="#edit-user-info" data-toggle="tab">
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','USER_INFO')"/>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#edit-user-roles" data-toggle="tab">
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ROLES')"/>
                                        </a>
                                    </li>
                                </ul>
                                <br/>

                                <input type="hidden" name="id" id="editInputUserID"/>

                                <div id="edit-user-info-tab-content" class="tab-content">
                                    <div class="tab-pane active fade in" id="edit-user-info">
                                
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group input-group-lg">
                                                    <label for="editInputFirstname">
                                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIRSTNAME')" />
                                                    </label>
                                                    <input type="text" name="firstname" class="form-control" id="editInputFirstname" placeholder="{php:function('\Webvaloa\Webvaloa::translate','FIRSTNAME')}" required="required" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group input-group-lg">
                                                    <label for="editInputLastname">
                                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','LASTNAME')" />
                                                    </label>
                                                    <input type="text" name="lastname" class="form-control" id="eidtInputLastname" placeholder="{php:function('\Webvaloa\Webvaloa::translate','LASTNAME')}" required="required" />
                                                </div>
                                            </div>                   
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group input-group-lg">
                                                    <label for="editInputEmail">
                                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EMAIL')" />
                                                    </label>
                                                    <input type="email" name="email" class="form-control" id="editInputEmail" placeholder="{php:function('\Webvaloa\Webvaloa::translate','EMAIL')}" required="required" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group input-group-lg">
                                                    <label for="editInputUsername">
                                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CUSTOM_USERNAME')" required="required" />
                                                    </label>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="input-group  input-group-lg">
                                                                <span class="input-group-addon">
                                                                    <input type="checkbox" id="editCheckboxUsername" onclick="User.toggleDisabled('editInputUsername')"/>
                                                                </span>
                                                                <input type="text" class="form-control" id="editInputUsername" name="username" disabled="disabled"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr/>

                                        <div class="form-group input-group-lg">
                                            <label for="editInputPassword">
                                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','NEW_PASSWORD')" />
                                            </label>
                                            <input type="password" name="password" class="form-control" id="editInputPassword" placeholder="{php:function('\Webvaloa\Webvaloa::translate','PASSWORD')}">
                                                <xsl:attribute name="pattern">.{8,64}</xsl:attribute>
                                            </input>
                                        </div>

                                        <div class="form-group input-group-lg">
                                            <label for="editInputPassword2">
                                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','NEW_PASSWORD_CONFIRM')" />
                                            </label>
                                            <input type="password" name="password2" class="form-control" id="editInputPassword2" placeholder="{php:function('\Webvaloa\Webvaloa::translate','PASSWORD_CONFIRM')}">
                                                <xsl:attribute name="pattern">.{8,64}</xsl:attribute>
                                            </input>
                                        </div>

                                    </div>
                                    
                                    <div class="tab-pane fade in" id="edit-user-roles">
                                        <div id="edit-user-roles-holder"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','LOADING')" /></div>
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
        
        <div class="modal fade" id="add-user-modal" tabindex="-1" role="dialog" aria-labelledby="add-user-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&#215;</button>
                        <h4 class="modal-title" id="add-user-label">
                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD_USER')"/>
                        </h4>
                    </div>
                    <div class="">
                        <form method="post" action="{/page/common/basepath}/user/add?token={token}" accept-charset="{/page/common/encoding}">

                            <div class="modal-body">
                                <ul class="nav nav-tabs nav-justified" id="user-info-tab" data-tabs="tabs">
                                    <li class="active">
                                        <a href="#user-info" data-toggle="tab">
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','USER_INFO')"/>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#user-roles" data-toggle="tab">
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ROLES')"/>
                                        </a>
                                    </li>
                                </ul>
                                <br/>

                                <input type="hidden" name="user_id" value="{user_id}" id="inputUserID"/>

                                <div id="user-info-tab-content" class="tab-content">
                                    <div class="tab-pane active fade in" id="user-info">
                                
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group input-group-lg">
                                                    <label for="inputFirstname">
                                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIRSTNAME')" />
                                                    </label>
                                                    <input type="text" name="firstname" class="form-control" id="inputFirstname" placeholder="{php:function('\Webvaloa\Webvaloa::translate','FIRSTNAME')}" value="{firstname}" required="required" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group input-group-lg">
                                                    <label for="inputLastname">
                                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','LASTNAME')" />
                                                    </label>
                                                    <input type="text" name="lastname" class="form-control" id="inputLastname" placeholder="{php:function('\Webvaloa\Webvaloa::translate','LASTNAME')}" value="{lastname}" required="required" />
                                                </div>
                                            </div>                   
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group input-group-lg">
                                                    <label for="inputEmail">
                                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EMAIL')" />
                                                    </label>
                                                    <input type="email" name="email" class="form-control" id="inputEmail" placeholder="{php:function('\Webvaloa\Webvaloa::translate','EMAIL')}" value="{email}" required="required" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group input-group-lg">
                                                    <label for="inputUsername">
                                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CUSTOM_USERNAME')" required="required" />
                                                    </label>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="input-group  input-group-lg">
                                                                <span class="input-group-addon">
                                                                    <input type="checkbox" onclick="User.toggleDisabled('inputUsername')">
                                                                        <xsl:if test="username != ''">
                                                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                                                        </xsl:if>
                                                                    </input>
                                                                </span>
                                                                <input type="text" class="form-control" id="inputUsername" name="username" value="{username}">
                                                                    <xsl:if test="not(username) or username = ''">
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
                                            <label for="inputPassword">
                                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PASSWORD')" />
                                            </label>
                                            <input type="password" name="password" class="form-control" id="inputPassword" placeholder="{php:function('\Webvaloa\Webvaloa::translate','PASSWORD')}" required="required">
                                                <xsl:attribute name="pattern">.{8,64}</xsl:attribute>
                                            </input>
                                        </div>

                                        <div class="form-group input-group-lg">
                                            <label for="inputPassword2">
                                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PASSWORD_CONFIRM')" />
                                            </label>
                                            <input type="password" name="password2" class="form-control" id="inputPassword2" placeholder="{php:function('\Webvaloa\Webvaloa::translate','PASSWORD_CONFIRM')}" required="required">
                                                <xsl:attribute name="pattern">.{8,64}</xsl:attribute>
                                            </input>
                                        </div>

                                    </div>
                                    
                                    <div class="tab-pane fade in" id="user-roles">
                                        <div id="add-user-roles-holder"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','LOADING')" /></div>
                                    </div>                                    
                                    
                                </div>                              
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CLOSE')"/>
                                </button>
                                <button type="submit" class="btn btn-success" id="add-user-button">
                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD')"/>
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
    </xsl:template>

    <xsl:template match="roles">
        <xsl:value-of select="useríd"/>
        <select name="roles[]" multiple="multiple" class="form-control roles">
            <xsl:for-each select="_roles">
                <option value="{id}">
                    <xsl:if test="selected">
                        <xsl:attribute name="selected">selected</xsl:attribute>
                    </xsl:if>
                    <xsl:value-of select="role"/>
                </option>
            </xsl:for-each>
        </select>
    </xsl:template>

</xsl:stylesheet>
