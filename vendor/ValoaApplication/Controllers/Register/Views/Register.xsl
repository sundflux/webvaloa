<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">

    <xsl:template match="index">
        <div class="container ">

            <form role="form" action="{/page/common/basepath}/register/register" method="post">
                <h1><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CREATE_ACCOUNT')"/></h1>
                <hr/>
                
                <div id="messages">
                    <xsl:call-template name="messages" />
                </div>                
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="firstname"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FIRSTNAME')"/></label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="firstname" 
                                name="firstname" 
                                placeholder="{php:function('\Webvaloa\Webvaloa::translate','FIRSTNAME')}" 
                                value="{firstname}"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="text"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','LASTNAME')"/></label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="lastname" 
                                name="lastname" 
                                placeholder="{php:function('\Webvaloa\Webvaloa::translate','LASTNAME')}" 
                                value="{lastname}"/>
                        </div>      
                    </div>
                </div>
                            
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','EMAIL')"/></label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                placeholder="{php:function('\Webvaloa\Webvaloa::translate','EMAIL')}" 
                                value="{email}"/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirm_email"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CONFIRM_EMAIL')"/></label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="confirm_email" 
                                name="confirm_email" 
                                placeholder="{php:function('\Webvaloa\Webvaloa::translate','EMAIL')}" 
                                value="{confirm_email}"/>
                        </div>
                    </div>
                </div>                 

                <button type="submit" class="btn btn-default"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','CREATE_ACCOUNT')"/></button>
            </form>
            
        </div>
    </xsl:template>
    
    <xsl:template match="info">
        <div class="container ">

            <h1><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','THANK_YOU_FOR_REGISTERING')"/></h1>
            <hr/>
            <div id="messages">
                <xsl:call-template name="messages" />
            </div>     
            
            <p class="lead"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','REGISTRATION_INFO')"/></p>
            <p><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','REGISTRATION_INFO_2')"/></p>

        </div>
    </xsl:template>
    
    <xsl:template match="verify">
        <div class="container ">

            <h1><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','VERIFY_ACCOUNT')"/></h1>
            <hr/>
            <div id="messages">
                <xsl:call-template name="messages" />
            </div>     
            
            <p class="lead"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','VERIFY_YOUR_ACCOUNT')"/></p>
            <p><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','VERIFY_YOUR_ACCOUNT_2')"/></p>

            <form role="form" action="{/page/common/basepath}/register/verify/{hash}" method="post">
                <div class="form-group">
                    <div class="form-group input-group-lg">
                        <label for="inputPassword"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PASSWORD')" /></label>
                        <input type="password" name="password" class="form-control" id="inputPassword" placeholder="{php:function('\Webvaloa\Webvaloa::translate','PASSWORD')}" />
                    </div>

                    <div class="form-group input-group-lg">
                        <label for="inputPassword2"><xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','PASSWORD_CONFIRM')" /></label>
                        <input type="password" name="password2" class="form-control" id="inputPassword2" placeholder="{php:function('\Webvaloa\Webvaloa::translate','PASSWORD_CONFIRM')}" />
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-primary">
                            <xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','FINISH_REGISTRATION')"/>
                        </button>
                    </div>           
                </div>
            </form>

        </div>
    </xsl:template>    

</xsl:stylesheet>
