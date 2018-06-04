<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

	<xsl:template name="Text">
		<xsl:param name="id"></xsl:param>
		<xsl:param name="uniqid"></xsl:param>
		<xsl:param name="name"></xsl:param>
		<xsl:param name="value"></xsl:param>
		<xsl:param name="translation"></xsl:param>
		<xsl:param name="params"></xsl:param>
		<xsl:param name="default_value"></xsl:param>
		<xsl:param name="validation"></xsl:param>

		<input type="text" class="form-control" name="{$uniqid}[{$name}][]" data-field-name="{$name}">
			<xsl:if test="$value != ''">
				<xsl:attribute name="value"><xsl:value-of select="$value"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="$value = '' and $default_value != ''">
				<xsl:attribute name="value"><xsl:value-of select="$default_value"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="$validation != ''">
				<xsl:attribute name="pattern"><xsl:value-of select="$validation"/></xsl:attribute>
			</xsl:if>
		</input>
		<br/>
	</xsl:template>	

</xsl:stylesheet>
