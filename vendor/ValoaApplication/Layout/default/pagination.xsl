<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

	<!-- pagination main template. calls 'pages' template for numbers. -->	
	<xsl:template name="pagination">
		<xsl:param name="url"/>
		<xsl:param name="urlAppend"/>
		<xsl:param name="onlyButtons"/>
		<xsl:param name="pageCurrent"/>
		<xsl:param name="pageNext"/>
		<xsl:param name="pagePrev"/>
		<xsl:param name="pageCount"/>

		<ul class="pagination">
			<xsl:choose>
				<xsl:when test="$pagePrev='' or not($pagePrev)">
					<li class="disabled"><a href="#">&#171;</a></li>
					<li class="disabled"><a href="#">&#8249;</a></li>
				</xsl:when>
				<xsl:otherwise>
					<li><a href="{$url}1{$urlAppend}">&#171;</a></li>
					<li><a href="{$url}{$pagePrev}{$urlAppend}">&#8249;</a></li>					
				</xsl:otherwise>	
			</xsl:choose>

			<xsl:if test="not($onlyButtons)">
				<xsl:call-template name="pages">
					<xsl:with-param name="max" select="$pageCount"/>
					<xsl:with-param name="url" select="$url"/>				
					<xsl:with-param name="urlAppend" select="$urlAppend"/>		
					<xsl:with-param name="current" select="$pageCurrent"/>				
				</xsl:call-template>
			</xsl:if>

			<xsl:choose>
				<xsl:when test="$pageNext='' or not($pageNext)">
					<li class="disabled"><a href="#">&#8250;</a></li>
					<li class="disabled"><a href="#">&#187;</a></li>			
				</xsl:when>
				<xsl:otherwise>
					<li ><a href="{$url}{$pageNext}{$urlAppend}">&#8250;</a></li>
					<li><a href="{$url}{$pageCount}{$urlAppend}">&#187;</a></li>
				</xsl:otherwise>
			</xsl:choose>
		</ul>

	</xsl:template>
	
	<!-- page number links for pagination -->
	<xsl:template name="pages">
		<xsl:param name="url"/>
		<xsl:param name="urlAppend"/>
		<xsl:param name="max" select="1"/>
		<xsl:param name="count" select="0"/>
		<xsl:param name="current" select="1"/>

		<xsl:if test="$count &lt; $max">
			<xsl:if test="$count &gt; $current - 7 and $count &lt; $current + 5">
				<li>
					<!-- mark current page -->
					<xsl:if test="$count+1 = $current">
						<xsl:attribute name="class">active</xsl:attribute>
					</xsl:if>
					<a href="{$url}{$count+1}{$urlAppend}">
						<xsl:value-of select="$count+1"/>
					</a>
				</li>
			</xsl:if>

			<xsl:call-template name="pages">
				<xsl:with-param name="count" select="$count + 1"/>
				<xsl:with-param name="max" select="$max"/>
				<xsl:with-param name="url" select="$url"/>
				<xsl:with-param name="urlAppend" select="$urlAppend"/>
				<xsl:with-param name="current" select="$current"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>	

</xsl:stylesheet>
