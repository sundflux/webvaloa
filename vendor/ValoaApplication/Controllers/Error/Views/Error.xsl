<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

    <xsl:template match="index">
        <div class="jumbotron" id="webvaloa-error">
            <h1><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','OOPS')"/></h1>
            <h2><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','AN_ERROR_OCCURRED')"/></h2>
            <xsl:if test="errorMessage">
                <p class="text-danger">"<xsl:value-of select="errorMessage"/>" <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','IN_CLASS')"/>&#160;<span class="label label-warning"><xsl:value-of select="errorClass"/></span></p>
            </xsl:if>
            
            <form method="get" action="{/page/common/basehref}">
                <button type="submit" class="btn btn-default btn-lg"> 
                    <span class="glyphicon glyphicon-arrow-left"></span> &#160;
                    <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','BACKTO')"/>
                </button>
            </form>
        </div>
    </xsl:template>

</xsl:stylesheet>
