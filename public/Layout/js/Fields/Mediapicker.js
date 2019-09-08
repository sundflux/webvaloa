/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.io>
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2014 Tarmo Alexander Sundström <ta@sundstrom.io>
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

        jQuery('.mediapicker').each(
            function () {
                MediaPicker.initMediaPicker(this);
            }
        );
    
        // Handle repeatable reset
        MediaPicker.bindRepeatables();

        // Handle repeatable group reset
        jQuery('.tab-pane').each(
            function () {
                jQuery(this).find('.repeatable-group-button').each(
                    function () {
                        jQuery(this).on(
                            'click', function () {
                                var $el = jQuery(this).parents('.tab-pane').find('.repeatable-group-holder').last();

                                $el.find('.mediapicker').each(
                                    function () {
                                        var _uniqid = FrontendHelpers.uniqid();

                                        jQuery(this).attr('name', _uniqid + '[' + jQuery(this).data('field-name') + '][]');
                                        jQuery(this).attr('id', 'mediapickerinput-' + _uniqid);

                                        MediaPicker.initMediaPicker(this);
                                    }
                                );

                                MediaPicker.bindRepeatables();
                            }
                        );
                    }
                ); 
            }
        );

    }
);

var MediaPicker = {
    
    bindRepeatables: function () {
        jQuery('.field-Mediapicker').each(
            function () {
                jQuery(this).find('.repeatable-field-button').each(
                    function () {
                        jQuery(this).on(
                            'click', function () {
                                var $el = jQuery(this).parent().find('.repeatable-holder').last();

                                $el.find('.mediapicker').each(
                                    function () {
                                        var _uniqid = FrontendHelpers.uniqid();

                                        jQuery(this).attr('name', _uniqid + '[' + jQuery(this).data('field-name') + '][]');
                                        jQuery(this).attr('id', 'mediapickerinput-' + _uniqid);
                                        jQuery(this).attr('data-uniqid', _uniqid);
                                        jQuery(this).data('uniqid', _uniqid);

                                        MediaPicker.initMediaPicker(this);
                                    }
                                );
                            }
                        );
                    }
                ); 
            }
        );  
    },

    initMediaPicker: function (el) {
        var $url = jQuery('#basehref').text();
        var _uniqid = FrontendHelpers.uniqid();

        // Reset id/uniqid for repeatables so launching mediapicker
        // instances doesn't break. This doesn't affect saving.
        jQuery(el).attr('id', 'mediapickerinput-' + _uniqid);
        jQuery(el).data('uniqid', _uniqid);

        jQuery.get(
            $url + '/content_media', function ( data ) {
                jQuery('#mediapicker-content').html(data);
            
                Media.initUpload();
                Media.initSearch();
                Media.initMultipleFileDeletion();

                if(jQuery('#initFilelist').length > 0) {
                    var dir = '/';

                    if(document.URL.indexOf('#') > -1) {
                        dir = document.URL.substr(document.URL.indexOf('#') + 1);
                    }

                    Media.getFileList(dir);
                    Media.initFolderBindings();
                }
            
                jQuery('.mediapicker-modal-selector').on(
                    'click', function () {
                        var _active = jQuery(this).parent().parent().find('input').data('uniqid');

                        jQuery('.mediapicker-modal').data('active-mediapicker-uniqid', _active);
                    }
                );
            }
        );
    }

}
