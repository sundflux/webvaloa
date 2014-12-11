<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

	<xsl:template name="Mediapicker">
		<xsl:param name="id"></xsl:param>
		<xsl:param name="uniqid"></xsl:param>
		<xsl:param name="name"></xsl:param>
		<xsl:param name="value"></xsl:param>
		<xsl:param name="translation"></xsl:param>
		<xsl:param name="params"></xsl:param>

		<div class="row">
			<div class="col-lg-6">
				<div class="input-group">
					<input 
						type="text" 
						class="form-control mediapicker" 
						name="{$uniqid}[{$name}][]" 
						data-field-name="{$name}" 
                        id="mediapickerinput-{$uniqid}"
                        data-uniqid="{$uniqid}"
						value="{$value}" />
				    
					<span class="input-group-btn">
						<button class="btn btn-default mediapicker-modal-selector" type="button" data-toggle="modal" data-target=".mediapicker-modal">
							<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SELECT_FILE')"/>
						</button>
					</span>
				</div>
			</div>	
		</div>

		<br/>
	</xsl:template>	

</xsl:stylesheet>
