<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

    <xsl:template match="index">
    	<div class="container">
    		<h1><xsl:value-of select="article/title"/></h1>
    		<xsl:value-of select="article/fieldValues/content" disable-output-escaping="yes"/>
    	</div>
    </xsl:template>    

</xsl:stylesheet>
