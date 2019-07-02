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

var Role = {
    
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

        jQuery('.load-controllers').on(
            'click', function (e) {
                Loader.show();
            
                var _url = jQuery('#basehref').text();
                var _role_id = jQuery(this).data('id');

                _request = jQuery.ajax(
                    {
                        type: "POST",
                        url: _url + '/user_role/controllers/' + _role_id
                    }
                );

                _request.done(
                    function (response) {
                        jQuery('#controllers-list').html(response);

                        Loader.hide();
                    }
                );
            }
        );

        jQuery('.add-role').on(
            'click', function (e) {
                jQuery('#role_id').val('');
                jQuery('#inputRole').val('');
                jQuery('#add-role-label').text(jQuery('#translation-add').attr('data-translation-string'));
            }
        );        

        jQuery('.edit-role').on(
            'click', function (e) {
                jQuery('#role_id').val(jQuery(this).data('id'));
                jQuery('#inputRole').val(jQuery(this).data('role'));
                jQuery('#add-role-label').text(jQuery('#translation-edit').attr('data-translation-string'));
            }
        );
    }

}

jQuery(document).ready(
    function () {

        Role.init();

    }
);
