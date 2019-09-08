
/**
 * The Initial Developer of the Original Code is
 * 2014 Tarmo Alexander Sundström <ta@sundstrom.io>
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2014 Tarmo Alexander Sundström <ta@sundstrom.io>
 *
 * All Rights Reserved.
 *
 * Contributor(s):
 * 2014 Tarmo Alexander Sundström <ta@sundstrom.io>
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

        jQuery('input[type=color]').each(
            function (e) {
                jQuery(this).attr("type","text").minicolors(
                    {
                        theme: 'bootstrap'
                    }
                );
            }
        ); 

        // Handle repeatable reset
        jQuery('.field-Colorpicker').each(
            function () {
                jQuery(this).find('.repeatable-field-button').each(
                    function () {
                        jQuery(this).on(
                            'click', function () {
                                ColorPickerHelper.bindColorPicker(this);
                            }
                        );
                    }
                );
            }
        );

        // Handle repeatable group reset
        jQuery('.tab-pane').each(
            function () {
                jQuery(this).find('.repeatable-group-button').each(
                    function () {
                        jQuery(this).on(
                            'click', function () {

                                var $last = jQuery('.tab-pane').find('.repeatable-group-holder').last();
                                var $colorpickers = jQuery($last).find('.colorpicker');

                                $colorpickers.each(
                                    function () {
                                        jQuery(this).minicolors(
                                            'destroy'
                                        );

                                        jQuery(this).parent().find('.minicolors-swatch').last().remove();
                                        jQuery(this).attr("type","text");
                                        jQuery(this).minicolors(
                                            {
                                                theme: 'bootstrap'
                                            }
                                        );
                                    }
                                );

                                $last.find('.repeatable-field-button').each(
                                    function () {
                                        jQuery(this).on(
                                            'click', function () {
                                                ColorPickerHelper.bindColorPicker(this);
                                            }
                                        );
                                    }
                                );
                            }
                        );
                    }
                ); 
            }
        );

    }
);

var ColorPickerHelper = {

    bindColorPicker: function (el) {
        var $parents = jQuery(el).parents('.field-Colorpicker');
        var $last = jQuery(el).find('.repeatable-holder').last();

        $last.find('.colorpicker').each(
            function () {
                jQuery(this).minicolors(
                    'destroy'
                );
            }
        );

        $parents.find('.colorpicker').last().each(
            function () {
                // initialize only empty elements
                if(jQuery(this).val() == '') {
                    jQuery(this).parent().find('.minicolors-swatch').last().remove();
                    jQuery(this).attr("type","text");
                    jQuery(this).minicolors(
                        {
                            theme: 'bootstrap'
                        }
                    );
                }
            }
        );
    }

}