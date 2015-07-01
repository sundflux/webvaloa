<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">
    <xsl:output method="html"
        version="5.0"
        encoding="utf-8"
        indent="yes"
        omit-xml-declaration="yes" />

    <xsl:template match="/page">
        <xsl:apply-templates select="/page/module"/>
    </xsl:template>

</xsl:stylesheet>
