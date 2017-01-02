<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

	<xsl:template match="index">
        <h1>
            <xsl:attribute name="class">
                <xsl:if test="isAjax">hide</xsl:if>
            </xsl:attribute>
            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','MEDIA_MANAGER')"/>&#160;<small><xsl:value-of select="path"/><span id="append-path">/</span></small>
        </h1>
        <hr> 
            <xsl:attribute name="class">
                <xsl:if test="isAjax">hide</xsl:if>
            </xsl:attribute>          
        </hr>
        
        <div class="row">
            <div class="col-lg-9">
                <xsl:if test="not(isAjax)">
                    <div class="btn-group">
                        <a href="{/page/common/basepath}/content_media/upload" class="btn btn-default"><i class="fa fa-upload"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','UPLOAD_FILES')"/></a>
                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#create-folder"><i class="fa fa-plus"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CREATE_FOLDER')"/></button>
                        <!-- TODO:
                        <button type="button" id="move-button" class="btn btn-default disabled"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','MOVE_SELECTED')"/></button>
                        -->
                        <button type="button" id="delete-button" class="btn btn-danger disabled" data-message="{php:function('\Webvaloa\Webvaloa::translate','DELETE')}?"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','DELETE_SELECTED')"/></button>
                    </div>
                </xsl:if>
            </div>
            <div class="col-lg-3">
                <div class="input-group webvaloa-search-form">
                    <input type="text" value="{search}" name="filter" class="form-control" id="filter" placeholder="{php:function('\Webvaloa\Webvaloa::translate','SEARCH')}" />
                    <span class="input-group-btn">
                        <button class="btn btn-default disabled" type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                    </span>
                </div>
            </div>
        </div>

        <hr/>

		<div class="row">
			<div class="col-md-4">
				<ul class="media-folders">
					<li class="media load-folder-listing" data-filter="/" data-type="folder" data-path="/">
						<i class="fa fa-folder-o"></i> <span>/</span>
					</li>
					<xsl:for-each select="children">
						<li class="media load-folder-listing" data-filter="{.}" data-type="folder" data-path="{.}">
							<i class="fa fa-folder-o"></i> <span><xsl:value-of select="."/></span>
						</li>
					</xsl:for-each>
				</ul>
			</div>
			<div class="col-md-8" id="file-listing">

			</div>
		</div>

        <div class="modal fade" id="create-folder" tabindex="-1" role="dialog" aria-labelledby="create-folder-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&#215;</button>
                        <h4 class="modal-title" id="create-folder-label">
                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CREATE_FOLDER')"/>
                        </h4>
                    </div>
                    <div class="">
                        <form method="post" action="{/page/common/basepath}/content_media/create?token={token}" accept-charset="{/page/common/encoding}">
                            <div class="modal-body">
                                <div class="form-group input-group-lg">
                                    <label for="inputFolder">
                                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FOLDER_NAME')" />
                                    </label>
                                    <input type="text" name="folder" class="form-control" id="inputFolderId" placeholder="{php:function('\Webvaloa\Webvaloa::translate','FOLDER_NAME')}" value="{folder}" required="required" />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CLOSE')"/>
                                </button>
                                <button type="submit" class="btn btn-success" id="add-user-button">
                                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CREATE')"/>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

		<div class="hide" id="token"><xsl:value-of select="token"/></div>
		<div class="hide" id="basehref"><xsl:value-of select="/page/common/basehref"/></div>
		<div class="hide" id="initFilelist">1</div>
        <div class="hide" id="mediapicker">
            <xsl:choose>
                <xsl:when test="isAjax">1</xsl:when>
                <xsl:otherwise>0</xsl:otherwise>
            </xsl:choose>
        </div>
	</xsl:template>

	<xsl:template match="listing">
		<xsl:if test="files != ''">
			<ul class="list-group filterable">
				<xsl:for-each select="files">
					<li class="list-group-item" data-filter="{filename}" data-type="file">
                        <xsl:if test="../mediapicker != '1'">
                            <input type="checkbox" data-filename="{filename}" class="pull-left file-selector"/>
                        </xsl:if>

						<xsl:choose>
							<xsl:when test="extension = 'jpg' or extension = 'jpeg' or extension = 'png'">
								<img class="file-list-image pull-left lazy" alt="{filename}">
									<xsl:attribute name="src"><xsl:value-of select="php:function('\Webvaloa\Helpers\Imagemagick::crop', string(fullpath), 24, 24)"/></xsl:attribute>
								</img>
							</xsl:when>
							<xsl:otherwise>
								<div class="file-list-image">
									<i class="fa fa-file"></i>
								</div>
							</xsl:otherwise>
						</xsl:choose>
						<b class="filename pull-left"><xsl:value-of select="filename"/></b>

						<div class="btn-group pull-right filelist-buttons">
                            <xsl:choose>
                                <xsl:when test="../mediapicker = '1'">
                                    <a class="btn btn-sm btn-default mediapicker-select-file" href="#" data-file="{../currentPath}{filename}"><i class="fa fa-check"></i> </a>
                                    <a class="btn btn-sm btn-default file-info-button" data-filename="{../currentPath}{filename}"><i class="fa fa-wrench" aria-hidden="true"></i></a>
                                </xsl:when>
                                <xsl:otherwise>
                                    <a class="btn btn-sm btn-default" href="{/page/common/basepath}public/media/{../currentPath}{filename}"><i class="fa fa-download"></i> </a>
                                    <a class="btn btn-sm btn-default file-info-button" data-filename="{../currentPath}{filename}"><i class="fa fa-wrench" aria-hidden="true"></i></a>
                                    <a class="btn btn-sm btn-danger confirm-delete" data-message="{php:function('\Webvaloa\Webvaloa::translate','DELETE')}?" href="{/page/common/basepath}content_media/delete?file={filename}&amp;token={../token}"><i class="fa fa-trash-o"></i></a>
                                </xsl:otherwise>
                            </xsl:choose>
						</div>

						<span class="filesize pull-right"><xsl:value-of select="filesize"/> &#160;</span>
						<br/>
					</li>

                    <li class="list-group-item file-info-dialog" style="display:none" data-filename="{../currentPath}{filename}"></li>
				</xsl:for-each>
			</ul>
		</xsl:if>

		<xsl:if test="files = ''">
			<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','NO_FILES')"/>
			<br/>
            <xsl:if test="mediapicker != '1'">
                <p>
                    <a class="btn btn-sm btn-danger confirm-delete" data-message="{php:function('\Webvaloa\Webvaloa::translate','DELETE')}?" href="{/page/common/basepath}content_media/delete?folder={currentPath}&amp;token={token}">
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','DELETE_FOLDER')"/>&#160;<i class="fa fa-trash-o"></i>
                    </a>
                </p>
            </xsl:if>
		</xsl:if>
	</xsl:template>

	<xsl:template match="delete">
	</xsl:template>

    <xsl:template match="savefileinfo">
    </xsl:template>

    <xsl:template match="fileinfo">
        <form data-filename="{filename}" data-title="{title}" data-alt="{alt}" method="post" action="{/page/common/basepath}/content_media/savefileinfo">
            <input type="hidden" class="filename-holder" name="filename" value="{filename}"/>
            <div class="form-group">
                <label for="title"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','TITLE')"/></label>
                <input type="text" class="form-control title-holder" id="title" placeholder="" value="{title}" />
            </div>
            <div class="form-group">
                <label for="alt"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','ALT')"/></label>
                <input type="text" class="form-control alt-holder" id="alt" placeholder="" value="{alt}" />
            </div>
            <button type="submit" class="btn btn-success btn-save-fileinfo"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SAVE')"/></button>
        </form>
    </xsl:template>

	<xsl:template match="upload">
		<h1>Upload files <small><xsl:value-of select="path"/></small></h1>
		<hr/>
		<a href="{/page/common/basepath}/content_media#/{uploadPath}" class="btn btn-default">&#171;&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','BACK_TO_MEDIAMANAGER')"/></a>
		<hr/>
		<br/>
		<div id="upload"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SELECT_FILES_TO_UPLOAD')"/></div>

		<div class="hide" id="token"><xsl:value-of select="token"/></div>
		<div class="hide" id="basehref"><xsl:value-of select="/page/common/basehref"/></div>
		<div id="translation-dragndrop" data-translation="{php:function('\Webvaloa\Webvaloa::translate','DRAGNDROP')}"/>
		<div id="translation-done" data-translation="{php:function('\Webvaloa\Webvaloa::translate','UPLOAD_READY')}"/>
	</xsl:template>

</xsl:stylesheet>
