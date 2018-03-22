<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

    <xsl:template match="index">
        <div class="container">
            <div class="row">
                <div class="col-sm-6 col-md-4 col-md-offset-4">
                    <h1 class="text-center login-title"><xsl:value-of select="config/site_name"/></h1>
                    <div class="account-wall">
                        <div class="text-center" id="branding-image">
                            <xsl:choose>
                                <xsl:when test="config/webvaloa_branding/value">
                                    <img src="{/page/common/basehref}/public/media/{config/webvaloa_branding/value}" />
                                </xsl:when>
                                <xsl:otherwise>
                                    <img src="{/page/common/basehref}/public/media/webvaloa-logo.png" />
                                </xsl:otherwise>
                            </xsl:choose>
                        </div>
                        <br/>
                        <form method="post" action="{/page/common/basepath}/login_passwordreset?token={token}" accept-charset="{/page/common/encoding}" class="form-signin" id="form-passwordreset">
                            <input name="username" type="email" class="form-control" placeholder="{php:function('\Webvaloa\Webvaloa::translate','Email')}" required="required" autofocus="autofocus" />
                        	<p class="text-muted text-center">
                        		<small><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','Enter your username to send password reset request.')"/></small>
                        	</p>
                            <br/>
                            <button class="btn btn-lg btn-primary btn-block" type="submit">
                               <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','Reset password')"/>
                            </button>
                            <span class="clearfix"></span>
                        </form>
                    </div>

                </div>
            </div>
        </div>
	</xsl:template>

    <xsl:template match="verify">
        <div class="container">
            <div class="row">
                <div class="col-sm-6 col-md-4 col-md-offset-4">
                    <h1 class="text-center login-title"><xsl:value-of select="config/site_name"/></h1>
                    <div class="account-wall">
                        <div class="text-center" id="branding-image">
                            <xsl:choose>
                                <xsl:when test="config/webvaloa_branding/value">
                                    <img src="{/page/common/basehref}/public/media/{config/webvaloa_branding/value}" />
                                </xsl:when>
                                <xsl:otherwise>
                                    <img src="{/page/common/basehref}/public/media/webvaloa-logo.png" />
                                </xsl:otherwise>
                            </xsl:choose>
                        </div>
                        <div id="messages">
                            <xsl:call-template name="messages" />
                        </div>
                        <br/>
                        <form method="post" action="{/page/common/basepath}/login_passwordreset/verify/{hash}" accept-charset="{/page/common/encoding}" class="form-signin" id="form-passwordreset">
                            <input name="password" type="password" class="form-control" placeholder="{php:function('\Webvaloa\Webvaloa::translate','New password')}" required="required" autofocus="autofocus" />
                            <input name="password2" type="password" class="form-control" placeholder="{php:function('\Webvaloa\Webvaloa::translate','Verify new password')}" required="required" />

                            <p class="text-muted text-center">
                                <small><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','Enter new password')"/></small>
                            </p>
                            <br/>
                            <button class="btn btn-lg btn-primary btn-block" type="submit">
                               <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','Change password')"/>
                            </button>
                            <span class="clearfix"></span>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </xsl:template>

</xsl:stylesheet>
