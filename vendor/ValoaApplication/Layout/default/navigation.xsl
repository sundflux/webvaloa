<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template name="navi">
        <xsl:param name="article_id" />
        <ul class="menu">
            <xsl:for-each select="sub">
                <li>
                    <a href="{target}">
                        <xsl:attribute name="class">
                            <xsl:if test="target_id = $article_id">active</xsl:if>
                            <xsl:if test=".//sub[target_id=$article_id]">active-trail</xsl:if>
                        </xsl:attribute>
                        <xsl:value-of select="translation" />
                    </a>
                    <xsl:if test="sub and (target_id = $article_id or .//sub[target_id=$article_id])">
                        <xsl:call-template name="navi">
                            <xsl:with-param name="article_id" select="$article_id" />
                        </xsl:call-template>
                    </xsl:if>
                </li>
            </xsl:for-each>
        </ul>
    </xsl:template>
</xsl:stylesheet>