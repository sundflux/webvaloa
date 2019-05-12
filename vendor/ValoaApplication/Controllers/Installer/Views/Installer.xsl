<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

<xsl:template match="index" xml:space="preserve">
<xsl:value-of select="title"/>
---
<xsl:if test="help">
<xsl:call-template name="help" />
</xsl:if>
</xsl:template>

<xsl:template name="help" xsl:space="preserve">
SYNTAX:
	-c [controller] -m [method, optional] -p [parameters]

INSTALLER COMMANDS:

	-c installer -p setup/cms
		Install Webvaloa with CMS profile.

	-c installer -p setup/developer
		Install Webvaloa with Developer profile.

	-c installer -p setup/[profile name]
		Install Webvaloa with custom profile.

	-c installer -m extension -p install/[extension name]
		Install given extension.

	-c installer -m extension -p uninstall/[extension name]
		Uninstall given extension.

	-c installer -m plugin -p install/[plugin name]
		Install given plugin.

	-c installer -m plugin -p uninstall/[plugin name]
		Uninstall given extension.
</xsl:template>

</xsl:stylesheet>
