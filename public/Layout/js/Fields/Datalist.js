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
jQuery(document).ready(function() {
    jQuery('.datalist').each(function() {
        DataList.initDataList(this);
    });
    jQuery('#article-form').on('change', '.datalist-polyfill', function() {
        jQuery(this).prev('input').val(jQuery(this).val());
    });
});
var DataList = {

    data: {},

    last: 0,

    initDataList: function(el) {

        var $url = jQuery('#basehref').text();
        var $id = jQuery(el).data('field-id');
        var $val = jQuery(el).data('field-value');
        DataList.last++;
        var $uniq = 'datalist-uniq-' + DataList.last;

        // Polyfill
        if ('options' in document.createElement('datalist')) {
            $selector = 'datalist';
        } else {
            if (jQuery(el).next('datalist').length) {

                jQuery(el).next('datalist').remove();
                jQuery(el).after('<select class="datalist-polyfill form-control"/>');
                $selector = 'select';
            }
        }

        jQuery(el).attr('list', $uniq);
        jQuery(el).next($selector).attr('id', $uniq);


        if (typeof DataList.data[$id] === 'undefined') {
            // No data yet, fetching it as we are the first one
            DataList.data[$id] = {};
            DataList.data[$id].ready = false;
            DataList.data[$id].callbacks = [];

            jQuery.getJSON($url + '/content_article/fieldParams/' + $id, function(data) {
                DataList.data[$id].data = data;
                DataList.data[$id].ready = true;
                // Call self again as we are done here
                DataList.initDataList(el);
                // Looping through all the callbacks that we got while waiting for data
                DataList.data[$id].callbacks.forEach(function(current) {
                    DataList.initDataList(current);
                });
            });
        } else {
            // There is already something, next up; we find what it is
            if (DataList.data[$id].ready) {
                // We have data. We are using it here
                var items = [];
                var data = DataList.data[$id].data;
                jQuery.each(data, function(key, val) {
                    items.push('<option value="' + val.title + '">' + val.title + '</option>');
                });
                var $html = items.join('');
                jQuery($html).appendTo(jQuery(el).next($selector));
            } else {
                //We don't have data. But someone is already trying to fetch it. Be nice and ask them to call back when ready
                // TODO find out if there is any, even remote possibility that the first fetch might be done before all of these callbacks have been set
                DataList.data[$id].callbacks.push(el);
            }
        }
    }
}