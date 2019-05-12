<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

<xsl:template match="index" xml:space="preserve">
<xsl:value-of select="command"/>
<xsl:value-of select="value" />
</xsl:template>

</xsl:stylesheet>
