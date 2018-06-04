<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

	<xsl:template name="Wysiwyg">
		<xsl:param name="id"></xsl:param>
		<xsl:param name="uniqid"></xsl:param>
		<xsl:param name="name"></xsl:param>
		<xsl:param name="value"></xsl:param>
		<xsl:param name="translation"></xsl:param>
		<xsl:param name="default_value"></xsl:param>
		<xsl:param name="validation"></xsl:param>
		<xsl:param name="params"></xsl:param>

		<textarea class="form-control wysiwyg" rows="10" name="{$uniqid}[{$name}][]" data-field-name="{$name}"><xsl:value-of select="$value"/></textarea>
		<br/>
	</xsl:template>	

</xsl:stylesheet>
