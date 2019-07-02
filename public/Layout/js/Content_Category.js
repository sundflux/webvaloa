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

var Category = {
    
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

        jQuery('.favorite-toggle').on(
            'click', function (e) {
                e.preventDefault();

                Loader.show();

                _request = jQuery.ajax(
                    {
                        type: "GET",
                        url: jQuery(this).attr('href')
                    }
                );

                if(jQuery(this).children().first('i').hasClass('fa-star')) {
                    jQuery(this).children('i').removeClass('fa-star');
                    jQuery(this).children('i').addClass('fa-star-o');
                } else {
                    jQuery(this).children('i').addClass('fa-star');
                    jQuery(this).children('i').removeClass('fa-star-o');
                }

                _request.done(
                    function (response, el) {
                        Loader.hide();
                    }
                );

                return false;
            }
        );

        jQuery('.edit-category').on(
            'click', function (e) {
                var _url = jQuery('#basehref').text();
                var _token = jQuery('#token').text();

                Loader.show();

                jQuery('#inputCategoryEdit').val(jQuery(this).data('category-name'));
                jQuery('#category_id').val(jQuery(this).data('category-id'));

                _request = jQuery.ajax(
                    {
                        type: "GET",
                        url: _url + '/content_category/layouts/' + jQuery(this).data('category-id')
                    }
                );

                _request.done(
                    function (response) {
                        jQuery('#layout-overrides').html(response);
                        Loader.hide();
                    }
                );


                jQuery('#edit-category-info-tab a').click(
                    function (e) {
                        e.preventDefault();

                        jQuery(this).tab('show');
                    }
                );

                jQuery('#edit-category-info-tab').tab();

                var _url = jQuery('#basehref').text();

                _requestRoles = jQuery.ajax(
                    {
                        type: "POST",
                        url: _url + '/content_category/roles/' + jQuery(this).data('category-id')
                    }
                );

                _requestRoles.done(
                    function (response) {
                        jQuery('#add-category-roles-holder').html(response);

                        Loader.hide();
                    }
                );
            }
        );

    }

}

jQuery(document).ready(
    function () {

        Category.init();

    }
);