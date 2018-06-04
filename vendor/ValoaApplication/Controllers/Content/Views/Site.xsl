<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

	<xsl:template match="index">
		<form action="{/page/common/basepath}/content_site/save" method="POST" id="menu-submit">
			<input type="hidden" name="json" id="json" value="" />
			<h1>
			<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SITE_STRUCTURE')"/>

			<div class="actions pull-right">
				<button class="menu-add btn btn-primary">
                <i class="fa fa-plus"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADD_MENU_ITEM')"/>
            </button>

				<div class="btn-group">
					<a href="{/page/common/basepath}/content_site" data-confirm="{php:function('\Webvaloa\Webvaloa::translate','CANCEL')}?" class="menu-undo btn btn-default"><i class="fa fa-undo"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CANCEL')"/></a>
					<button type="submit" class="menu-save btn btn-success"><i class="fa fa-save"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SAVE')"/></button>
				</div>
			</div>

        </h1>
		</form>
		<div>
			<br/>
			<div id="navigation-editor">
				<div class="sortable-root dd">
					<xsl:apply-templates select="editablemenu/navigation" mode="editablemenu" />
				</div>
			</div>
			<br/>
		</div>

		<div class="hidden" id="menu-template">
			<li data-name="{php:function('\Webvaloa\Webvaloa::translate','NEW')}" class="dd-item list-group-item" data-unsaved="1">
				<span class="dd-handle"><i style="padding-right: 5px" class="fa fa-sort"></i> <span class="name"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','NEW')"/></span></span>
				<div class="btn-group pull-right" role="group" aria-label="...">
					<button class="menu-edit btn btn-default btn-xs" title="{php:function('\Webvaloa\Webvaloa::translate','EDIT')}" data-toggle="modal" data-target="#menu-editor"><i class="fa fa-pencil"></i>&#160;
						<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EDIT')" />
					</button>
					<button class="menu-delete btn btn-danger btn-xs" title="{php:function('\Webvaloa\Webvaloa::translate','DELETE')}"><i class="fa fa-close"></i>
					</button>
				</div>

				<xsl:if test="sub">

					<xsl:call-template name="naviEditable" />
				</xsl:if>
			</li>
		</div>

		<div class="modal fade" id="menu-editor" tabindex="-1" role="dialog" aria-labelledby="menu-editor-label">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&#215;</span>
						</button>
						<h4 class="modal-title" id="menu-editor-label"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EDIT')"/>:&#160;<span class="name"></span></h4>
					</div>
					<div class="modal-body">
						<form>
							<input type="hidden" id="id" />
							<input type="hidden" id="old" />
							<input type="hidden" id="target" />
							<div class="form-group">
								<label for="message-text" class="control-label">
									<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TYPE')" />:</label>
								<select id="type" class="form-control">
									<option value="component">
										<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','COMPONENT')" />
									</option>
									<option value="alias">
										<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ALIAS')" />
									</option>
									<option value="content">
										<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CONTENT')" />
									</option>
									<option value="content_listing">
										<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','LIST')" />
									</option>
									<option value="url">
										<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ADDRESS')" />
									</option>
								</select>
							</div>


							<div class="form-group menu-select" id="menu-component">
								<label for="message-text" class="control-label">
									<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TARGET')" />:</label>
								<select class="select-type form-control">
									<option value=""></option>
									<xsl:for-each select="components">
										<option value="{id}">
											<xsl:value-of select="controller" />
										</option>
									</xsl:for-each>
								</select>
							</div>

							<div class="form-group menu-select" id="menu-alias">
								<label for="message-text" class="control-label">
									<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TARGET')" />:</label>
								<select class="select-type form-control">
									<option value=""></option>
									<xsl:for-each select="alias">
										<option value="{id}">
											<xsl:value-of select="alias" />
										</option>
									</xsl:for-each>
								</select>
							</div>

							<div class="form-group menu-select" id="menu-content">
								<label for="message-text" class="control-label">
									<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TARGET')" />:</label>
								<select class="select-type form-control">
									<option value=""></option>
									<xsl:for-each select="contents">
										<option value="{article/id}">
											<xsl:value-of select="article/title" />
										</option>
									</xsl:for-each>
								</select>
							</div>

							<div class="form-group menu-select" id="menu-content_listing">
								<label for="message-text" class="control-label">
									<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TARGET')" />:</label>
								<select class="select-type form-control">
									<option value=""></option>
									<xsl:for-each select="lists">
										<option value="{id}">
											<xsl:value-of select="category" />
										</option>
									</xsl:for-each>
								</select>
							</div>

							<div class="form-group menu-select" id="menu-url">
								<label for="message-text" class="control-label">
									<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','URL')" />:</label>
								<input type="text" class="form-control" id="url" placeholder="e.g. http://example.com" />
							</div>

							<div class="form-group">
								<label for="recipient-name" class="control-label">
									<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TITLE')" />:</label>
								<input type="text" class="form-control" id="name" />
							</div>

							<div class="form-group" id="menu-alias">
								<label for="message-text" class="control-label">
									<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ALIAS')" />:</label>
								<input type="text" class="form-control" id="alias" placeholder="{php:function('\Webvaloa\Webvaloa::translate','AUTO_GENERATED')}" />
							</div>

						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default menu-item-cancel" data-dismiss="modal">
							<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CANCEL')" />
						</button>
						<button type="button" class="btn btn-success menu-item-save">
							<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SAVE')" />
						</button>
					</div>
				</div>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="/page/module/*/*/navigation" mode="editablemenu">
		<xsl:call-template name="naviEditable" />
	</xsl:template>

	<xsl:template name="naviEditable">
		<ol class="list-group dd-list">
			<xsl:for-each select="sub">
				<li data-id="{id}" data-name="{translation}" data-alias="{alias}" data-type="{type}" class="dd-item list-group-item" data-old="1">
					<xsl:attribute name="data-target">
						<xsl:choose>
							<xsl:when test="type='url'">
								<xsl:value-of select="target" /></xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="target_id" /></xsl:otherwise>
						</xsl:choose>
					</xsl:attribute>
					<span class="dd-handle"><i style="padding-right: 5px" class="fa fa-sort"></i> <span class="name"><xsl:value-of select="translation"/></span></span>
					<div class="btn-group pull-right" role="group" aria-label="...">
						<button class="menu-edit btn btn-default btn-xs" title="{php:function('\Webvaloa\Webvaloa::translate','EDIT')}" data-toggle="modal" data-target="#menu-editor"><i class="fa fa-pencil"></i>&#160;
							<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EDIT')" />
						</button>
						<button class="menu-delete btn btn-danger btn-xs" title="{php:function('\Webvaloa\Webvaloa::translate','DELETE')}"><i class="fa fa-close"></i>
						</button>
					</div>

					<xsl:if test="sub">

						<xsl:call-template name="naviEditable" />
					</xsl:if>
				</li>
			</xsl:for-each>
		</ol>
	</xsl:template>

</xsl:stylesheet>
