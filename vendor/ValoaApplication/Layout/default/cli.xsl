<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">
    <xsl:output
    method="text"
    version="1.0"
    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
    doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
    indent="yes" />

    <xsl:template match="/page">
        <xsl:apply-templates select="/page/module"/>
    </xsl:template>

</xsl:stylesheet>
