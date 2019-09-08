<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:php="http://php.net/xsl">
    
    <!--
    The Initial Developer of the Original Code is
    Tarmo Alexander Sundström <ta@sundstrom.io>

    Portions created by the Initial Developer are
    Copyright (C) 2014 Tarmo Alexander Sundström <ta@sundstrom.io>

    All Rights Reserved.

    Contributor(s):

    Permission is hereby granted, free of charge, to any person obtaining a
    copy of this software and associated documentation files (the "Software"),
    to deal in the Software without restriction, including without limitation
    the rights to use, copy, modify, merge, publish, distribute, sublicense,
    and/or sell copies of the Software, and to permit persons to whom the
    Software is furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included
    in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
    THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
    IN THE SOFTWARE.
    -->    

    <xsl:template name="ContentMediapickerPlugin" mode="plugin">
		<div 
			class="modal fade mediapicker-modal" 
			tabindex="-1" 
			role="dialog" 
			aria-labelledby="mediapickermodal" 
			aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content" id="mediapicker-modal">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">
							<span aria-hidden="true">&#215;</span>
							<span class="sr-only">Close</span>
						</button>
			        	<h4 class="modal-title" id="mediapickerlabel">
							<xsl:value-of select="php:function('\Webvaloa\Webvaloa::translate','SELECT_FILE')"/>
						</h4>
			        </div>

                    <div id="mediapicker-content"></div>
				</div>
			</div>
		</div>
    </xsl:template>

</xsl:stylesheet>
