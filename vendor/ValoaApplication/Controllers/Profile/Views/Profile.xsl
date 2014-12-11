<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

    <xsl:template match="index">
        <h1>
            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PROFILE')"/>
        </h1>
        <hr/>

		<div class="span3 well" id="profile">
			<center>
				<img 
					src="{/page/module//_gravatar}" 
					width="140" height="140" 
					class="img-circle" />
				<h3><xsl:value-of select="/page/module//_name"/></h3>
				<em><xsl:value-of select="/page/module//_email"/></em>
			</center>
	    </div>
    </xsl:template>

</xsl:stylesheet>
