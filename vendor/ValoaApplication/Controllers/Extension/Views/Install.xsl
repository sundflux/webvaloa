<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

	<xsl:template match="index">
		<form method="get" action="{/page/common/basepath}/extension_install">
			<input type="hidden" name="discover" value="1"/>
	        <h1>
	        	<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','INSTALL_EXTENSIONS')"/>
	        </h1>
	        <hr/>

    	</form>

        <xsl:if test="components != ''">
        	<h2><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','COMPONENTS')"/></h2>
	        <table class="table table-striped">
	            <tbody>
	                <xsl:for-each select="components">
	                    <tr>
	                        <td>
	                            <xsl:value-of select="."/>                                
	                        </td>
	                        <td class="footable-last-column">
	                            <div class="btn-group">
	                                <a href="{/page/common/basepath}/extension_install/install/{.}?token={../token}" class="btn btn-primary">
	                                    <i class="fa fa-download"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','INSTALL')"/>
	                                </a>
	                            </div>
	                        </td>
	                    </tr>
	                </xsl:for-each>
	            </tbody>        
	        </table>
    	</xsl:if>

        <xsl:if test="plugins != ''">
        	<h2><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PLUGINS')"/></h2>
	        <table class="table table-striped">
	            <tbody>
	                <xsl:for-each select="plugins">
	                    <tr>
	                        <td>
	                            <xsl:value-of select="."/>                                
	                        </td>
	                        <td class="footable-last-column">
	                            <div class="btn-group">
	                                <a href="{/page/common/basepath}/extension_install/install/{.}?token={../token}&amp;plugin" class="btn btn-primary">
	                                    <i class="fa fa-download"></i>&#160;<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','INSTALL')"/>
	                                </a>
	                            </div>
	                        </td>
	                    </tr>
	                </xsl:for-each>
	            </tbody>        
	        </table>
    	</xsl:if>

	</xsl:template>

</xsl:stylesheet>
