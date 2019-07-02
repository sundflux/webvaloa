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
        var $nestable = $('.dd').nestable(
            {
                listNodeName: 'ol',
                maxDepth: 50,
                placeClass: 'dd-placeholder list-group-item placeholder',
                emptyClass: 'dd-empty list-group-item',
                collapseBtnHTML: '',
                listClass: 'dd-list list-group',
                itemClass: 'dd-item list-group-item',
            }
        );

        $('button.menu-add').click(
            function (e) {
                e.preventDefault();
                if ($('.sortable-root li[data-id]').length>0) {
                    var max = Math.max.apply(
                        Math, $('.sortable-root li[data-id]').map(
                            function () {
                                return parseInt($(this).data('id'));
                            }
                        ).get()
                    );
                } else {
                    var max = 1;
                }

                var $new = $('#menu-template li').clone().prependTo('.dd > .dd-list');
                $new.attr('data-id', max + 1);
                $new.find('.menu-edit').trigger('click');
                $('select#type').val('content').trigger('change');
            }
        );

        $('#navigation-editor').on(
            'click', '.menu-delete', function (e) {
                e.preventDefault();
                $menu = $(this).closest('.dd-item');
                if (window.confirm($(this).attr('title') + ' "' + $menu.data('name') + '"?')) {
                    $menu.remove();
                }
            }
        );

        $('#menu-editor').on(
            'show.bs.modal', function (event) {
                var $button = $(event.relatedTarget) // Button that triggered the modal
                var $menu = $button.closest('.dd-item');
                var modal = $(this);
                modal.find('.modal-title .name').text($menu.data('name'));
                modal.find('.modal-body input#name').val($menu.data('name'));
                modal.find('.modal-body input#alias').val($menu.data('alias'));
                modal.find('.modal-body input#target').val($menu.data('target'));
                if ($menu.data('type') == 'url') {
                    modal.find('.modal-body input#url').val($menu.data('target'));
                }
                modal.find('.modal-body input#old').val($menu.data('old'));
                modal.find('.modal-body select#type').val($menu.data('type'));
                modal.find('.modal-body select.select-type').val($menu.data('target'))
                modal.find('.modal-body .menu-select').hide();
                modal.find('.modal-body .menu-select#menu-' + $menu.data('type')).show();
                modal.find('input#id').val($menu.data('id'));
            }
        );

        $('select#type').on(
            'change', function (e) {
                $('.modal-body .menu-select').hide();
                $('.modal-body .menu-select#menu-' + $(this).val()).show();
                $('.modal-body select.select-type').val('');
                $('input#target').val('');
            }
        );

        $('select.select-type').on(
            'change', function (e) {
                $('input#target').val($(this).val());
            }
        );

        $('#menu-editor').on(
            'hide.bs.modal', function (e) {
                var $id = parseInt($('#menu-editor input#id').val());
                if ($('#menu-editor .menu-item-save').attr('data-save') != '1' && $('#menu-editor input#old').val() != '1') {
                    $('.dd-item[data-id="' + $id + '"]').remove();
                }
                $('#menu-editor .menu-item-save').removeAttr('data-save');
            }
        );

        $('#menu-editor').on(
            'click', '.menu-item-save', function (e) {
                var $id = parseInt($('#menu-editor input#id').val());
                $(this).attr('data-save', 1);
                var $menu = $('.dd-item[data-id="' + $id + '"]');
                $menu
                .data('name', $('#menu-editor input#name').val())
                .data('type', $('#menu-editor select#type').val())
                .data('target', $('#menu-editor input#target').val())
                .data('old', 1)
                .data('alias', $('#menu-editor input#alias').val());
                if ($('#menu-editor select#type').val() == 'url') {
                    $menu.data('target', $('#menu-editor input#url').val())
                }
                $menu.find('.name:first').text($('#menu-editor input#name').val());
                $('#menu-editor').modal('hide');
            }
        );

        $nestable.on(
            'change', function () {
                $('input#json').val(window.JSON.stringify($nestable.nestable('serialize')));
            }
        );

        $('#menu-submit').submit(
            function (e) {
                $('input#json').val(window.JSON.stringify($nestable.nestable('serialize')));
                if (!confirm($(this).find('.menu-save').text() + '?')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }
        );

        $('a[data-confirm]').click(
            function () {
                if (!confirm($(this).attr('data-confirm'))) {
                    return false;
                }
            }
        );
    }
);
var NavigationHelpers = {
    sortOrdering: function () {
        var $i = 0;
        jQuery('#editable').find('li').each(
            function () {
                jQuery(this).attr('data-ordering', $i); // .data doesn't work here for some reason
                $i++;
            }
        );
    }
}