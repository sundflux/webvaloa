/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2014 Tarmo Alexander Sundström <ta@sundstrom.im>
 *
 * All Rights Reserved.
 *
 * Contributor(s):
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

var Article = {
    
    init: function()
    {
        jQuery('.confirm').click(function(e) {
            var message = jQuery(this).attr('data-message');
            if (confirm(message)) {
                return true;
            } else {
                return false;
            }
        });
        
        jQuery('#alias-toggle').on('click', function() {
            jQuery('#article-alias').toggle();
        });

        jQuery('a[href="#webvaloa-all-tabs"]').click(function(){
          jQuery('#groups-tab li').removeClass('active');
          jQuery(this).parent().addClass('active');
          jQuery('#'+jQuery(this).data('tabs') + ' .tab-pane').each(function(i,t){
            jQuery(this).addClass('active');
          });
        }).trigger('click');

        if(document.location.hash) {
          jQuery('.nav-tabs a[href='+document.location.hash+']').tab('show');
          jQuery('a[href="#webvaloa-all-tabs"]').parent().remove();
        }
        jQuery('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
          window.location.hash = e.target.hash;
        });
        jQuery(window).on('hashchange', function() {
          jQuery('.nav-tabs a[href='+document.location.hash+']').tab('show');
        });
    }
}

jQuery(document).ready(function() {

    Article.init();

});
