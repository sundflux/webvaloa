<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

    <xsl:template match="index">
        <h1>
            <a class="btn btn-success pull-right" href="{/page/common/basepath}/content_field/group">
                <i class="fa fa-plus"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD_GROUP')"/>
            </a>
            
            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIELDS')"/>
        </h1>
        <hr/>
        
        <div class="fields-holder">
            <xsl:for-each select="groups">
                <div>
                    <div class="btn-group pull-right group-actions">
                        <a type="button" class="btn btn-default" href="{/page/common/basepath}/content_field/group/{group/id}">
                            <i class="fa fa-pencil"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EDIT')"/>
                        </a>
                        <a type="button" class="btn btn-default add-field" data-target-id="{group/id}" href="{/page/common/basepath}/content_field/field/{group/id}">
                            <i class="fa fa-plus"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD_FIELD')"/>
                        </a>                        
                        <button type="button" data-toggle="tooltip" data-action="global" data-container="body" data-placement="top" title="{php:function('\Webvaloa\Webvaloa::translate','GLOBAL_GROUP')}" data-target-id="{group/id}">
                            <xsl:choose>
                                <xsl:when test="group/global = '1'">
                                    <xsl:attribute name="class">btn btn-default field-tooltip active</xsl:attribute>
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:attribute name="class">btn btn-default field-tooltip</xsl:attribute>
                                </xsl:otherwise>
                            </xsl:choose>                            
                            <i class="fa fa-certificate"></i>
                        </button>                    
                        <button type="button" data-toggle="tooltip" data-action="repeatable" data-container="body" data-placement="top" title="{php:function('\Webvaloa\Webvaloa::translate','REPEATABLE')}" data-target-id="{group/id}">
                            <xsl:choose>
                                <xsl:when test="group/repeatable = '1'">
                                    <xsl:attribute name="class">btn btn-default field-tooltip active</xsl:attribute>
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:attribute name="class">btn btn-default field-tooltip</xsl:attribute>
                                </xsl:otherwise>
                            </xsl:choose>
                            <i class="fa fa-repeat"></i>
                        </button>                    
                        <a class="btn btn-danger confirm" title="Delete" href="{/page/common/basepath}/content_field/delete/{group/id}?token={../token}">
                            <i class="fa fa-minus"></i>
                        </a>
                    </div>                
                    <h2><xsl:value-of select="group/translation"/></h2>
                    <hr class="clear"/>
                    <div class="panel panel-default fields-holder">
                        <ul class="sortable" data-id="{group/id}">
                            <li class="alert alert-holder"><small class="text-muted"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIELDS')"/></small></li>
                            <xsl:for-each select="fields">
                                <li class="" data-id="{id}">
                                    <div class="btn-group pull-right field-actions">
                                      <a class="btn btn-primary" href="{/page/common/basepath}/content_field/field/{../group/id}/{id}"><i class="fa fa-pencil"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EDIT')"/></a>
                                      <a class="btn btn-danger confirm" href="{/page/common/basepath}/content_field/deletefield/{id}?token={../../token}"><i class="fa fa-minus"></i></a>
                                    </div>

                                    <small class="text-muted pull-right"><xsl:value-of select="type"/></small>
                                    <i class="fa fa-sort"></i>
                                    <span class="field-title"><xsl:value-of select="translation"/></span>
                                </li>
                            </xsl:for-each>
                        </ul>
                    </div>
                </div>   
            </xsl:for-each>
        </div>

        <div id="basehref" class="hide">
            <xsl:value-of select="/page/common/basehref"/>
        </div>
        
        <xsl:call-template name="loader"/>

        <!-- Translation helpers -->
        <div id="translate" class="hide">
            <span id="translation-delete" data-translation-string="{php:function('\Webvaloa\Webvaloa::translate','DELETE')}?"/>
        </div>

        <span id="token" class="hide"><xsl:value-of select="token"/></span>
    </xsl:template>
    
    <xsl:template match="toggleglobal">
        <xsl:value-of select="retval"/>
    </xsl:template>
    
    <xsl:template match="togglerepeatable">
        <xsl:value-of select="retval"/>
    </xsl:template>    
    
    <xsl:template match="ordering">
        <xsl:value-of select="retval"/>
    </xsl:template>

    <xsl:template match="group">
        <form method="post" action="{/page/common/basepath}/content_field/group/{group_id}" accept-charset="{/page/common/encoding}">
            <h1>
                <xsl:choose>
                    <xsl:when test="group_id != ''">
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EDIT_FIELD_GROUP')"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD_FIELD_GROUP')"/>
                    </xsl:otherwise>
                </xsl:choose>
                <button type="submit" class="btn btn-success pull-right fields-savebutton confirm" name="save">
                    <xsl:choose>
                        <xsl:when test="group_id != ''">
                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SAVE')"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD')"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </button>
            </h1>
            <hr/>
            
            <div class="form-group">
                <label class="col-sm-4 control-label" for="group_name"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','GROUP_NAME')"/></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="group_name" value="{group_name}" name="group_name" placeholder="group name" />
                    <p class="help-block"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','GROUP_NAME_INFO')"/></p>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label" for="group_label"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','GROUP_LABEL')"/></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="group_label" value="{group_label}" name="group_label" placeholder="group label" />
                    <p class="help-block"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','GROUP_LABEL_INFO')"/></p>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label" for="repeatable_group"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','REPEATABLE')"/></label>
                <div class="col-sm-8">
                    <select class="form-control" id="repeatable_group" name="repeatable_group">
                        <option value="0"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TEXT_NO')"/></option>
                        <option value="1"><xsl:if test="repeatable = '1'"><xsl:attribute name ="selected">selected</xsl:attribute></xsl:if><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TEXT_YES')"/></option>
                    </select>
                    <p class="help-block"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','REPEATABLE_INFO')"/></p>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label" for="categories"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SHOW_IN_CATEGORIES')"/></label>
                <div class="col-sm-8">
                    <select class="form-control categories-list" id="categories" name="categories[]" multiple="multiple">
                        <xsl:for-each select="categories">
                            <option value="{id}">
                                <xsl:if test="selected">
                                    <xsl:attribute name="selected">selected</xsl:attribute>
                                </xsl:if>
                                <xsl:value-of select="category"/>
                            </option>
                        </xsl:for-each>
                    </select>
                    <p class="help-block"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SHOW_IN_CATEGORIES_INFO')"/></p>
                </div>
            </div>

        </form>

        <!-- Translation helpers -->
        <div id="translate" class="hide">
            <span id="translation-delete" data-translation-string="{php:function('\Webvaloa\Webvaloa::translate','SAVE_CHANGES')}?"/>
        </div>

        <div id="basehref" class="hide">
            <xsl:value-of select="/page/common/basehref"/>
        </div>
        
        <xsl:call-template name="loader"/>        
    </xsl:template>

    <xsl:template match="field">
        <form method="post" action="{/page/common/basepath}/content_field/field/{group_id}/{field_id}" accept-charset="{/page/common/encoding}">
            <h1>
                <xsl:choose>
                    <xsl:when test="field_id != ''">
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EDIT_FIELD')"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD_FIELD')"/>
                    </xsl:otherwise>
                </xsl:choose>

                <button type="submit" class="btn btn-success pull-right fields-savebutton confirm" name="save">
                    <xsl:choose>
                        <xsl:when test="field_id != ''">
                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SAVE')"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD')"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </button>
            </h1>

            <hr/>

            <input type="hidden" name="group_id" id="group_id" value="{group_id}"/>
            <input type="hidden" name="ordering">
                <xsl:choose>
                    <xsl:when test="ordering != ''">
                        <xsl:attribute name="value"><xsl:value-of select="ordering"/></xsl:attribute>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:attribute name="value">0</xsl:attribute>
                    </xsl:otherwise>
                </xsl:choose>
            </input>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="field_name"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIELD_NAME')"/></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="field_name" value="{field_name}" name="field_name" placeholder="field name" />
                    <p class="help-block"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIELD_NAME_INFO')"/></p>
                </div>
            </div>                         
            <div class="form-group">
                <label class="col-sm-4 control-label" for="field_label"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIELD_LABEL')"/></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="field_label" value="{field_label}" name="field_label" placeholder="field name" />
                    <p class="help-block"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIELD_LABEL_INFO')"/></p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" for="help_text"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIELD_HELP_TEXT')"/></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="help_text" value="{help_text}" name="help_text" placeholder="help text" />
                    <p class="help-block"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIELD_HELP_TEXT_INFO')"/></p>
                </div>
            </div>
            <div class="form-group" id="repeatable_field_holder">
                <label class="col-sm-4 control-label" for="repeatable_field"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','REPEATABLE')"/></label>
                <div class="col-sm-8">
                    <select class="form-control" name="repeatable_field" id="repeatable_field">
                        <option value="0"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TEXT_NO')"/></option>
                        <option value="1"><xsl:if test="repeatable = '1'"><xsl:attribute name ="selected">selected</xsl:attribute></xsl:if><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TEXT_YES')"/></option>
                        <option value="2"><xsl:if test="repeatable = '2'"><xsl:attribute name ="selected">selected</xsl:attribute></xsl:if><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TEXT_NOREPEAT')"/></option>
                    </select>
                    <p class="help-block"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','REPEATABLE_INFO')"/></p>
                </div>
            </div>                         
            <div class="form-group">
                <label class="col-sm-4 control-label" for="field_type"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TYPE')"/></label>
                <div class="col-sm-8">
                    <select class="form-control" id="field_type" name="field_type">
                        <option value=""></option>
                        <xsl:for-each select="fields">
                            <option value="{.}">
                                <xsl:if test="../field_type = ."><xsl:attribute name ="selected">selected</xsl:attribute></xsl:if>
                                <xsl:value-of select="."/>
                            </option>
                        </xsl:for-each>
                    </select>
                    <p class="help-block"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIELD_TYPE')"/></p>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label" for="field_settings"> </label>
                <div class="col-sm-8">

                    <xsl:for-each select="fieldSettings">
                        <div class="fieldsettings settings-{field}">
                            <xsl:value-of select="settings"
                                disable-output-escaping="yes"/>
                        </div>
                    </xsl:for-each>

                </div>
            </div>
            <br/>
        </form>

        <!-- Translation helpers -->
        <div id="translate" class="hide">
            <span id="translation-delete" data-translation-string="{php:function('\Webvaloa\Webvaloa::translate','SAVE_CHANGES')}?"/>
        </div>

        <div id="basehref" class="hide">
            <xsl:value-of select="/page/common/basehref"/>
        </div>
        
        <xsl:call-template name="loader"/>        
    </xsl:template>

</xsl:stylesheet>
