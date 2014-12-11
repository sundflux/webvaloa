<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

    <xsl:template match="index">
        <h1>
            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SETTINGS')"/>
        </h1>
        <hr/>

        <form class="form-horizontal" method="post" role="form" action="{/page/common/basepath}/settings/save?token={token}" accept-charset="{/page/common/encoding}">
            <input type="hidden" name="component" value="{component}" />
            <xsl:for-each select="settings">
                
                <div class="form-group">
                    <label for="Settings{id}" class="col-sm-2 control-label">
                        <xsl:value-of select="key_translated"/>
                    </label>
                    <div class="col-sm-10">
                        <xsl:choose>
                            <!-- text inputs -->
                            <xsl:when test="type='text'">
                                <input type="text" name="{key}" class="form-control" id="Settings{id}" value="{value}" />
                            </xsl:when>
                            
                            <!-- selects -->
                            <xsl:when test="type='select'">
                                <select name="{key}" id="Settings{id}" class="form-control">
                                    <xsl:for-each select="values">
                                        <option value="{value}">
                                            <xsl:if test="../value = value">
                                                <xsl:attribute name="selected">
                                                    selected
                                                </xsl:attribute>
                                            </xsl:if>
                                            <xsl:value-of select="translation"/> 
                                        </option>
                                    </xsl:for-each>
                                </select>
                            </xsl:when>

                            <!-- checkboxes -->
                            <xsl:when test="type='checkbox'" id="Settings{id}">
                                <input type="checkbox" name="{key}" id="Settings{id}">
                                    <xsl:choose>
                                        <xsl:when test="value='1'">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:when>
                                        <xsl:otherwise>

                                        </xsl:otherwise>
                                    </xsl:choose>
                                </input>
                            </xsl:when>

                        </xsl:choose>
                    </div>
                </div>
            
            </xsl:for-each>
            <div class="form-group">
                <label class="col-sm-2 control-label">&#160;</label>
                <div class="col-sm-10">
                    <button type="submit" class="btn btn-primary">
                        <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SAVE')"/>
                    </button>
                </div>
            </div>
        </form>
            
    </xsl:template>

</xsl:stylesheet>
