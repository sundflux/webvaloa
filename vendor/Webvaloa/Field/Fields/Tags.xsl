<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

	<xsl:template name="Tags">
		<xsl:param name="id"></xsl:param>
		<xsl:param name="uniqid"></xsl:param>
		<xsl:param name="name"></xsl:param>
		<xsl:param name="value"></xsl:param>
		<xsl:param name="translation"></xsl:param>
		<xsl:param name="default_value"></xsl:param>
		<xsl:param name="validation"></xsl:param>
		<xsl:param name="params"></xsl:param>

		<input type="hidden" name="{$uniqid}[{$name}][]" id="ifempty-tags"/>

		<select multiple="multiple"
			class="form-control tags" 
			name="{$uniqid}[{$name}][]" 
			data-field-name="{$name}"
			data-field-id="{$id}"/>

		<br/>
	</xsl:template>

</xsl:stylesheet>
