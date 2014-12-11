<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

	<xsl:template name="Articlepicker">
		<xsl:param name="id"></xsl:param>
		<xsl:param name="uniqid"></xsl:param>
		<xsl:param name="name"></xsl:param>
		<xsl:param name="value"></xsl:param>
		<xsl:param name="translation"></xsl:param>
		<xsl:param name="params"></xsl:param>

		<select 
			class="form-control articlepicker" 
			name="{$uniqid}[{$name}][]" 
			data-field-name="{$name}" 
			data-field-id="{$id}" 
			data-field-value="{$value}"
			id="articlepicker-{uniqid}">
			<option value=""></option>
		</select>
		
		<br/>
	</xsl:template>	

</xsl:stylesheet>
