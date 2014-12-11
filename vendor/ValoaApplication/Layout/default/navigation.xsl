<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template name="navi">
        <ul>
            <xsl:for-each select="sub">
                <li>
                    <a href="{uri}"><xsl:value-of select="translation"/></a>
                    <xsl:if test="sub">
                        <xsl:call-template name="navi"/>
                    </xsl:if>
                </li>
            </xsl:for-each>
        </ul>
    </xsl:template>

</xsl:stylesheet>