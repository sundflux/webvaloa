<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

	<xsl:template match="index">
        <form method="post"
            action="{/page/common/basepath}/login_logout?token={token}"
            accept-charset="{/page/common/encoding}"
            class="form-signin">

            <button class="btn btn-block btn-primary btn-lg" type="submit" name="logout"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','Logout')"/></button>
        </form>
	</xsl:template>

</xsl:stylesheet>
