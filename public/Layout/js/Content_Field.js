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

jQuery(document).ready(
    function () {

        jQuery('.sortable').sortable(
            {
                handle: 'i.fa-sort'
            }
        );

        jQuery('.sortable').sortable().bind(
            'sortupdate', function () {
                Loader.show();

                var i = 0;
                var _url = jQuery('#basehref').text();
                var _group_id = jQuery(this).attr('data-id');
                var _order = new Array();
        
                var field_id = '';
                jQuery(this).children('li').each(
                    function (e) {
                        field_id = jQuery(this).attr('data-id');
                        if(typeof field_id !== 'undefined' && field_id != '') {
                            _order[i++] = field_id;
                        }
                    }
                );

                _request = jQuery.ajax(
                    {
                        type: "POST",
                        url: _url + '/content_field/ordering',
                        data: {
                            group_id: _group_id,
                            ordering: _order
                        }
                    }
                );

                _request.done(
                    function (response) {
                        Loader.hide();
                    }
                );

            }
        );

        jQuery('.field-tooltip').tooltip();
        jQuery(".alert").alert()
    
        jQuery('.add-field').click(
            function () {
                jQuery('#group_id').val(jQuery(this).attr('data-target-id'));
            }
        );

        jQuery('.confirm').on(
            'click', function (e) {
                if(!confirm(jQuery('#translation-delete').attr('data-translation-string'))) {
                    return false;
                }
            }
        );
    
        jQuery('button[data-action]').click(
            function (e) {
                e.preventDefault();
        
                Loader.show();
        
                var _request;
                var _group_id = jQuery(this).attr('data-target-id');
                var _action = jQuery(this).attr('data-action');
                var _url = jQuery('#basehref').text();
                var _active = jQuery(this).hasClass('active');
        
                if(_active) {
                    jQuery(this).removeClass('active');
                } else {
                    jQuery(this).addClass('active');
                }

                _request = jQuery.ajax(
                    {
                        type: "POST",
                        url: _url + '/content_field/toggle' + _action,
                        data: {
                            group_id: _group_id
                        }
                    }
                );

                _request.done(
                    function (response) {
                        Loader.hide();
                    }
                );
        
            }
        );

        // Fieldtype settings
        jQuery('#field_type').on(
            'change', function () {
                var $v = jQuery(this).val();

                if($v == '') {
                    jQuery('.fieldsettings').hide();
                } else {
                    jQuery('.fieldsettings').hide();
                    jQuery('.settings-' + $v).show();
                }
            }
        );

        var $v = jQuery('#field_type').val();
        jQuery('.fieldsettings').hide();
        jQuery('.settings-' + $v).show();

        // Validate group names
        jQuery('#group_name').on(
            'keyup', function (e) {
                e.preventDefault();
                ValidationHelper.validateName('#group_name', 'group', 0);
            }
        );

        jQuery('#group_name').on(
            'blur focusout', function (e) {
                e.preventDefault();
                ValidationHelper.validateName('#group_name', 'group', 1);
            }
        );

        // Validate field names
        jQuery('#field_name').on(
            'keyup', function (e) {
                e.preventDefault();
                ValidationHelper.validateName('#field_name', 'field', 0);
            }
        );

        jQuery('#field_name').on(
            'blur focusout', function (e) {
                e.preventDefault();
                ValidationHelper.validateName('#field_name', 'field', 1);
            }
        );

    }
);

var ValidationHelper = {

    validateName: function (id, type, apply) {
        Loader.show();

        var el = jQuery(id);
        var _url = jQuery('#basehref').text();
        var _value = jQuery(el).val();

        _request = jQuery.ajax(
            {
                type: "GET",
                url: _url + '/content_field/validate' + type + '/' + _value + '/'
            }
        );

        _request.done(
            function (response) {
                Loader.hide();

                if(response.exists > 0 || jQuery(id).val() == "") {
                    jQuery(id).parent().removeClass('has-success');
                    jQuery(id).parent().addClass('has-error');
                    jQuery('.fields-savebutton').prop('disabled', true);
                } else {
                    jQuery(id).parent().removeClass('has-error');
                    jQuery(id).parent().addClass('has-success');
                    jQuery('.fields-savebutton').prop('disabled', false);
                }

                if(apply == 1) {
                    jQuery(id).val(response.formattedname);
                }
            }
        );
    }

}