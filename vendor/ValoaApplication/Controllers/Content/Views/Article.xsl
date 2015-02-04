<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

    <xsl:template match="index">
        <h1 class="a-btn article-listing">
            <a class="btn btn-default pull-right article-listing" href="{/page/common/basepath}/content_article/add">
                <i class="fa fa-plus"></i>&#160;
                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD_ARTICLE')"/>
            </a>

            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ARTICLES')"/>
        </h1>
        <hr/>	

        <form method="get" action="{/page/common/basepath}/content_article">
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
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TITLE')"/>
                    </th>
                    <th data-hide="phone,tablet">
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PUBLISHED')"/>
                    </th>
                    <th> </th>
                </tr>
            </thead>
            <tbody>
                <xsl:if test="articles != ''">
                    <xsl:for-each select="articles">
                        <tr>
                            <td>
                                <xsl:value-of select="id"/>
                            </td>
                            <td>
                                <xsl:if test="published = '0'">
                                    <xsl:attribute name="class">text-muted</xsl:attribute>
                                </xsl:if>
	                            
                                <xsl:value-of select="title"/>
                            </td>
                            <td data-hide="phone,tablet"> 
                                <xsl:choose>
                                    <xsl:when test="published = '-1'">
                                        <span class="label label-danger">
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TRASHED')"/>
                                        </span>
                                    </xsl:when>	                        		
                                    <xsl:when test="published = '0'">
                                        <span class="label label-warning">
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','UNPUBLISHED')"/>
                                        </span>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <span class="label label-success">
                                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PUBLISHED')"/>
                                        </span>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </td>
                            <td class="footable-last-column">
                                <div class="btn-group">
                                    <a href="{/page/common/basepath}/content_article/edit/{id}" class="btn btn-default">
                                        <i class="fa fa-pencil"></i>&#160;
                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EDIT')"/>
                                    </a>

                                    <a class="btn btn-danger confirm" data-message="{php:function('\Webvaloa\Webvaloa::translate','ARE_YOU_SURE')}">
                                        <xsl:choose>
                                            <xsl:when test="system_role = '1'">  
                                                <xsl:attribute name="href">javascript:;</xsl:attribute>
                                                <xsl:attribute name="disabled">disabled</xsl:attribute>
                                            </xsl:when>
                                            <xsl:otherwise>
                                                <xsl:attribute name="href">
                                                    <xsl:value-of select="/page/common/basepath"/>/content_article/trash/<xsl:value-of select="id"/>?token=<xsl:value-of select="../token"/>
                                                </xsl:attribute>
                                            </xsl:otherwise>
                                        </xsl:choose>
                                        <i class="fa fa-minus"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </xsl:for-each>
                </xsl:if>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">
                        <xsl:call-template name="pagination">
                            <xsl:with-param name="url">
                                <xsl:value-of select="/page/common/basepath"/>
                                <xsl:value-of select="pages/url"/>
                            </xsl:with-param>
                            <xsl:with-param name="urlAppend">/<xsl:value-of select="category_id"/></xsl:with-param>
                            <xsl:with-param name="pageCurrent">
                                <xsl:value-of select="pages/page"/>
                            </xsl:with-param>
                            <xsl:with-param name="pageNext">
                                <xsl:value-of select="pages/pageNext"/>
                            </xsl:with-param>
                            <xsl:with-param name="pagePrev">
                                <xsl:value-of select="pages/pagePrev"/>
                            </xsl:with-param>
                            <xsl:with-param name="pageCount">
                                <xsl:value-of select="pages/pages"/>
                            </xsl:with-param>
                        </xsl:call-template>
                    </td>
                </tr>
            </tfoot>
        </table>

    </xsl:template>

    <xsl:template match="save">
    </xsl:template>

    <xsl:template match="article">
        <h1 class="article-header">
            <xsl:value-of select="title"/>
            <xsl:if test="article_id != '' and article_id &gt; 0">
                <small>&#160;#<xsl:value-of select="article_id"/></small>
            </xsl:if>

            <xsl:if test="category_id &gt; 0 or category_id = ''">
                <div class="btn-group pull-right">
                    <xsl:if test="not(category) or category=''">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            <xsl:if test="not(category) or category=''">
                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SELECT')"/>&#160;
                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CATEGORY')"/>
                            </xsl:if>

                            <xsl:value-of select="category"/>&#160;
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <xsl:for-each select="categories">
                                <xsl:choose>
                                    <xsl:when test="../article_id != ''">
                                        <li>
                                            <a id="move-to-category" href="{/page/common/basepath}/content_article/move/{../article_id}/{id}">
                                                <xsl:value-of select="category"/>
                                            </a>
                                        </li>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <li>
                                            <a href="{/page/common/basepath}/content_article/add/{id}">
                                                <xsl:value-of select="category"/>
                                            </a>
                                        </li>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </xsl:for-each>
                        </ul>
                    </xsl:if>
                </div>	
            </xsl:if>

            <button type="button" class="btn btn-default pull-right" data-toggle="modal" data-target="#myModal">
                <xsl:if test="mode &lt; 2">
                    <xsl:attribute name="disabled">disabled</xsl:attribute>
                </xsl:if>

                <i class="fa fa-clock-o"></i>&#160;
                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','VERSION_HISTORY')"/>
            </button>

            <xsl:if test="category_id &gt; 0">
                <a id="trash-article" href="{/page/common/basepath}/content_article/trash/{articleID}" class="btn btn-danger pull-right">
                    <xsl:if test="mode &lt; 1 or article_id = ''">
                        <xsl:attribute name="disabled">disabled</xsl:attribute>
                    </xsl:if>

                    <i class="fa fa-trash-o"></i>&#160;
                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TRASH_ARTICLE')"/>
                </a>
            </xsl:if>

            <button type="button" class="btn btn-success pull-right" id="save-article">
                <xsl:if test="mode &lt; 1">
                    <xsl:attribute name="disabled">disabled</xsl:attribute>
                </xsl:if>

                <xsl:choose>
                    <xsl:when test="article_id = '0'">
                        <i class="fa fa-save"></i>&#160;
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SAVE')"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <i class="fa fa-save"></i>&#160;
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SAVE_ARTICLE')"/>
                    </xsl:otherwise>
                </xsl:choose>
            </button>

        </h1>

        <hr/>

        <xsl:if test="not(category_id) or category_id = ''">
            <p class="text-muted pull-right category-notice" id="category-notice">
                <span class="glyphicon glyphicon-chevron-up"></span>
                <br/>
                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SELECT_CATEGORY_FIRST')"/>
            </p>
        </xsl:if>

        <xsl:if test="category != '' or categoryID = '0'">

            <form class="form-horizontal" 
                              role="form" 
                              id="article-form" 
                              method="post" 
                              action="{/page/common/basepath}/content_article/save">

                <input type="hidden" name="category_id" value="{categoryID}" id="category_id"/>
                <input type="hidden" name="article_id" value="{articleID}" id="article_id"/>

                <xsl:if test="category_id &gt; 0">
                    <div class="input-group article-title-holder">
                        <input class="form-control input-lg article-title" type="text" name="title" value="{article/title}">
                            <xsl:attribute name="placeholder">
                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ARTICLE_TITLE_HERE')"/>
                            </xsl:attribute>
                        </input>
						<span class="input-group-btn">
							<button type="button" id="alias-toggle">
								<xsl:attribute name="class">btn btn-default input-lg</xsl:attribute>
								<i class="fa fa-external-link"></i>
							</button>

							<button type="button" class="btn input-lg btn-default" id="publish-time-toggle">
								<i class="fa fa-clock-o"></i>
							</button>

                            <button type="button" id="publish-toggle">
                                <xsl:choose>
                                    <xsl:when test="article/published = '1'">
                                        <xsl:attribute name="class">btn btn-success input-lg</xsl:attribute>
                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PUBLISHED')"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:attribute name="class">btn btn-warning input-lg</xsl:attribute>
                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','UNPUBLISHED')"/>
                                    </xsl:otherwise> 
                                </xsl:choose>
                            </button>
                        </span>
                    </div>
                                        
                    <div class="well" id="article-alias" style="display: none">
                        <input class="form-control input-lg article-title" type="text" name="alias" value="{article/alias}">
                            <xsl:attribute name="placeholder">
                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ARTICLE_ALIAS')"/>
                            </xsl:attribute>
                        </input>
                    </div>

                    <div class="well" id="article-publish" style="display: none">
						<div class="form-group">
							<label for="inputPublishup"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','START_PUBLISHING')"/></label>
							<input type="text" class="form-control article-title" id="inputPublishup" name="publish_up">
								<xsl:attribute name="value"><xsl:value-of select="article/publish_up"/></xsl:attribute>
							</input>
						</div>
						<div class="form-group">
							<label for="inputPublishdown"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','STOP_PUBLISHING')"/></label>
							<input type="text" class="form-control article-title" id="inputPublishdown" name="publish_down">
								<xsl:attribute name="value"><xsl:value-of select="article/publish_down"/></xsl:attribute>
							</input>								
						</div>
                    </div>

                </xsl:if>

                <input type="hidden" name="published" value="{article/published}" id="published"/>

                <br/>

                <ul class="nav nav-tabs" id="groups-tab" data-tabs="tabs">
                    <xsl:for-each select="fields">
                        <li>
                            <a href="#{name}" data-toggle="tab">
                                <xsl:value-of select="translation"/>
                            </a>
                        </li>
                    </xsl:for-each>
                </ul>

                <div class="tab-content">
                    <!-- Groups are at this level -->
                    <xsl:for-each select="fields">

                        <!-- Group tab panel -->
                        <div class="tab-pane" id="{name}">

                            <!-- Repeated group data -->
                            <xsl:for-each select="repeatable_group">
                                <xsl:for-each select="repeatable">

                                    <div class="repeatable-group-holder">

                                        <!-- Fields inside the group -->
                                        <xsl:for-each select="fields">

                                            <div class="field-holder field-{type}">

                                                <div class="form-group">
                                                    <label class="control-label col-sm-3">
                                                        <span 
                                                            data-container="body" 
                                                            data-toggle="popover" 
                                                            data-placement="bottom" 
                                                            data-content="{help_text}">
                                                            <xsl:if test="help_text != ''">
                                                                <xsl:attribute name="class">help-text</xsl:attribute>
                                                            </xsl:if>
                                                            <xsl:value-of select="translation"/>
                                                        </span>
                                                    </label>

                                                    <div class="col-sm-9">
                                                        <!-- Field types -->

                                                        <!-- Inject field templates here: -->
                                                        <span id="injectholder"></span>

                                                        <xsl:call-template name="Repeatable">
                                                            <xsl:with-param name="repeatable">
                                                                <xsl:value-of select="repeatable"/>
                                                            </xsl:with-param>
                                                        </xsl:call-template>
                                                    </div>
                                                </div>

                                            </div>
                                        </xsl:for-each>

                                        <input type="hidden" class="group-separator" name="{uniqid}[group_separator]" value="1" />

                                        <xsl:if test="position() &gt; 1 and ../../repeatable = '1'">
                                            <hr/>
                                            <xsl:call-template name="delete-group-button"/>
                                        </xsl:if>
                                    </div>
									
                                </xsl:for-each>
                            </xsl:for-each>

                            <!-- repeat-group -->
                            <xsl:if test="repeatable = '1'">
                                <hr/>
                                <div class="form-group">
                                    <label class="control-label col-sm-3">
										&#160;
                                    </label>
                                    <div class="col-sm-9">
                                        <xsl:call-template name="Repeatable">
                                            <xsl:with-param name="repeatable">
                                                <xsl:value-of select="repeatable"/>
                                            </xsl:with-param>
                                            <xsl:with-param name="repeatable-class">repeatable-group-button</xsl:with-param>
                                            <xsl:with-param name="button-text">
                                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD')"/>&#160;
                                                <xsl:value-of select="translation"/>
                                            </xsl:with-param>
                                        </xsl:call-template>
                                    </div>
                                </div>
                            </xsl:if>

                        </div>

                    </xsl:for-each>
                </div>

            </form>

            <!-- Delete button element for repeatables -->
            <div id="repeatable-delete" class="hide">
                <xsl:call-template name="delete-button"/>
            </div>
            <div id="repeatable-group-delete" class="hide">
                <hr/>
                <xsl:call-template name="delete-group-button"/>
            </div>

            <!-- Translation helpers -->
            <div id="translate" class="hide">
                <span id="translation-delete" data-translation-string="{php:function('\Webvaloa\Webvaloa::translate','DELETE')}?"/>
                <span id="translation-save" data-translation-string="{php:function('\Webvaloa\Webvaloa::translate','SAVE_CHANGES')}?"/>
                <span id="translation-move" data-translation-string="{php:function('\Webvaloa\Webvaloa::translate','MOVE_TO_CATEGORY')}"/>
                <span id="translation-move-notice" data-translation-string="{php:function('\Webvaloa\Webvaloa::translate','MOVE_TO_CATEGORY_NOTICE')}"/>
                <span id="translation-leave-notice" data-translation-string="{php:function('\Webvaloa\Webvaloa::translate','LEAVE_NOTICE')}"/>
                <span id="translation-trash" data-translation-string="{php:function('\Webvaloa\Webvaloa::translate','ARE_YOU_SURE')}"/>
                <span id="translation-published" data-translation-string="{php:function('\Webvaloa\Webvaloa::translate','PUBLISHED')}"/>
                <span id="translation-unpublished" data-translation-string="{php:function('\Webvaloa\Webvaloa::translate','UNPUBLISHED')}"/>
            </div>

        </xsl:if>

        <!-- Version history modal -->
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&#215;</span>
                            <span class="sr-only">
                                <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CLOSE')"/>
                            </span>
                        </button>
                        <h4 class="modal-title" id="myModalLabel">
                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','VERSION_HISTORY')"/>
                        </h4>
                    </div>
                    <div class="modal-body">
                        <xsl:for-each select="history">
                            <a href="{/page/common/basepath}/content_article/edit/{../articleID}?version={id}">
                                <xsl:value-of select="created"/>
                            </a>
                            <br/>
                        </xsl:for-each>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CLOSE')"/>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="basehref" class="hide">
            <xsl:value-of select="/page/common/basehref"/>
        </div>
    </xsl:template>

    <xsl:template name="delete-button">
        <xsl:param name="button-text">
            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','DELETE')"/>
        </xsl:param>

        <button type="button" class="btn btn-danger repeatable-field-button-delete">
            <span class="fa fa-times"></span>&#160;
            <xsl:value-of select="$button-text"/>
        </button>

        <div class="move-repeatable pull-right">
            <button type="button" class="btn btn-info move-repeatable-up">
                <span class="fa fa-angle-up"></span>
            </button>
            <button type="button" class="btn btn-info move-repeatable-down">
                <span class="fa fa-angle-down"></span>
            </button>
        </div>

        <br/>
        <br/>
    </xsl:template>	

    <xsl:template name="delete-group-button">
        <xsl:param name="button-text">
            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','DELETE')"/>
        </xsl:param>

        <div class="form-group">
            <label class="control-label col-sm-3">
				&#160;
            </label>
            <div class="col-sm-9">
                <button type="button" class="btn btn-danger repeatable-group-button-delete">
                    <span class="fa fa-times"></span>&#160;
                    <xsl:value-of select="$button-text"/>
                </button>
            </div>
        </div>	
    </xsl:template>

    <xsl:template name="Repeatable">
        <xsl:param name="repeatable">0</xsl:param>
        <xsl:param name="repeatable-class">repeatable-field-button</xsl:param>
        <xsl:param name="button-text">
            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD')"/>
        </xsl:param>

        <xsl:if test="$repeatable = '1'">
            <button type="button" class="btn btn-default {$repeatable-class}">
                <span class="fa fa-plus"></span>&#160;
                <xsl:value-of select="$button-text"/>
            </button>
        </xsl:if>

    </xsl:template>	

</xsl:stylesheet>
