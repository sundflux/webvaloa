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

var User = {
    
    init: function () {
        jQuery('.confirm').click(
            function (e) {
                var message = jQuery(this).attr('data-message');
                if (confirm(message)) {
                    return true;
                } else {
                    return false;
                }
            }
        );

        jQuery('#user-info a').click(
            function (e) {
                e.preventDefault();
            
                jQuery(this).tab('show');
            }
        );
        
        jQuery('#user-roles a').click(
            function (e) {
                e.preventDefault();
            
                jQuery(this).tab('show');
            }
        );        

        jQuery('#user-info-tab').tab();
        
        jQuery('#edit-user').on(
            'show.bs.modal', function (e) {
                Loader.show();

                var editId = jQuery(e.relatedTarget).data("id");
                var editData = jQuery("tr[data-id="+editId+"]");
            
                jQuery('#edit-user form [name]').each(
                    function () {
                        jQuery(this).val(editData.data(jQuery(this).attr("name")));
                    }
                );

                if(editData.data("username") != editData.data("email")) {
                    jQuery("#editCheckboxUsername").attr("checked",true);
                    jQuery("#editInputUsername").attr("disabled",false);
                } else {
                    jQuery("#editCheckboxUsername").attr("checked",false);
                    jQuery("#editInputUsername").attr("disabled",true).val("");
                }

                var _url = jQuery('#basehref').text();

                _requestRoles = jQuery.ajax(
                    {
                        type: "POST",
                        url: _url + '/user/roles/' + editId
                    }
                );

                _requestRoles.done(
                    function (response) {
                        jQuery('#edit-user-roles-holder').html(response);

                        Loader.hide();
                    }
                );
            
                _requestMeta = jQuery.ajax(
                    {
                        type: "POST",
                        url: _url + '/user/meta/' + editId
                    }
                );

                _requestMeta.done(
                    function (response) {
                        jQuery('#edit-user-meta-holder').html(response);

                        Loader.hide();
                    }
                );
            
            }
        );

        jQuery('.load-roles').on(
            'click', function (e) {
                Loader.show();

                var _url = jQuery('#basehref').text();

                _requestRoles = jQuery.ajax(
                    {
                        type: "POST",
                        url: _url + '/user/roles'
                    }
                );

                _requestRoles.done(
                    function (response) {
                        jQuery('#add-user-roles-holder').html(response);

                        Loader.hide();
                    }
                );
            
                _requestMeta = jQuery.ajax(
                    {
                        type: "POST",
                        url: _url + '/user/meta'
                    }
                );

                _requestMeta.done(
                    function (response) {
                        jQuery('#add-user-meta-holder').html(response);

                        Loader.hide();
                    }
                );
            
            }
        );
    },
    
    toggleDisabled: function (el) {
        jQuery('#' + el).prop("disabled", !jQuery('#' + el).prop("disabled"));
        jQuery('#' + el).val('');
    }

}

jQuery(document).ready(
    function () {

        User.init();

    }
);
