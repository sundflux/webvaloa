<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

    <xsl:template match="index">
        <h1>
            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CHANGE_PASSWORD')"/>
        </h1>
        <hr/>

        <form class="form-horizontal" method="post" role="form" action="{/page/common/basepath}/password/save?token={token}" accept-charset="{/page/common/encoding}">
            <div class="form-group">
                <label for="inputPassword1" class="col-sm-2 control-label"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','OLD_PASSWORD')"/></label>
                <div class="col-sm-10">
                    <input type="password" name="old_password" class="form-control" id="inputPassword1" placeholder="{php:function('\Webvaloa\Webvaloa::translate','OLD_PASSWORD')}" />
                </div>
            </div>

            <div class="form-group">
                <label for="inputPassword2" class="col-sm-2 control-label"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','NEW_PASSWORD')"/></label>
                <div class="col-sm-10">
                    <input type="password" name="new_password" class="form-control" id="inputPassword2" placeholder="{php:function('\Webvaloa\Webvaloa::translate','NEW_PASSWORD')}" />
                </div>
            </div>

            <div class="form-group">
                <label for="inputPassword3" class="col-sm-2 control-label"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','REPEAT_NEW_PASSWORD')"/></label>
                <div class="col-sm-10">
                    <input type="password" name="new_password2" class="form-control" id="inputPassword3" placeholder="{php:function('\Webvaloa\Webvaloa::translate','REPEAT_NEW_PASSWORD')}" />
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">&#160;</label>
                <div class="col-sm-10">
                    <button type="submit" class="btn btn-primary"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SAVE')"/></button>
                </div>
            </div>
        </form>
              
    </xsl:template>

</xsl:stylesheet>
