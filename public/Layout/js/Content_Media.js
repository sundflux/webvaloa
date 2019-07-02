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

var Media = {
    
    initUpload: function () {
        var _url = jQuery('#basehref').text();
        var _token = jQuery('#token').text();
        var _dropStr = jQuery('#translation-dragndrop').data('translation');
        var _doneStr = jQuery('#translation-done').data('translation');

        jQuery('#upload').uploadFile(
            {
                url: _url + '/content_media/store/?token=' + _token,
                multiple: true,
                fileName: 'files',
                uploadButtonClass: 'btn btn-info btn-lg',
                dragDropStr: '<span id="upload-drag">' + _dropStr + '</span>',
                dragdropWidth: 'auto',
                statusBarWidth: 'auto',
                doneStr: _doneStr,
                onSubmit:function (files) {
                    Loader.show();
                },
                afterUploadAll:function () {
                    Loader.hide();  
                }
            }
        );
    },

    initSearch: function () { 
        jQuery('#filter').keyup(
            function () {
                var filter  = jQuery(this).val();

                jQuery('.filterable li').each(
                    function () {
                        if (jQuery(this).data('filter').search(new RegExp(filter, 'i')) < 0) {
                            jQuery(this).fadeOut('fast');
                        } else {
                            jQuery(this).show();
                        }
                    }
                );
            }
        );
    },

    initFolderBindings: function () {
        jQuery('.load-folder-listing').on(
            'click', function (e) {
                e.preventDefault();

                var _dir = jQuery(this).data('path');
                Media. getFileList(_dir);
            }
        )
    },

    initConfirmBindings: function () {
        jQuery('.confirm-delete').click(
            function (e) {
                e.preventDefault();

                var message = jQuery(this).attr('data-message');

                if (confirm(message)) {
                    Loader.show();
                
                    var _url = jQuery(this).attr('href');
                    var _path = jQuery('#append-path').text();

                    _request = jQuery.ajax(
                        {
                            type: "GET",
                            url: _url
                        }
                    );

                    _request.done(
                        function (response) {
                            Media.getFileList(_path);
                            Loader.hide();
                        }
                    );

                    return true;
                } else {
                    return false;
                }
            }
        );
    },

    initFileSelectorBindings: function () {
        jQuery('.file-selector').on(
            'click', function () {
                var $selectedN = Media.countSelectedFileSelectors();

                if($selectedN > 0) {
                    jQuery('#move-button').removeClass('disabled');
                    jQuery('#delete-button').removeClass('disabled');
                } else {
                    jQuery('#move-button').addClass('disabled');
                    jQuery('#delete-button').addClass('disabled');
                }
            }
        );
        
        jQuery('.mediapicker-select-file').on(
            'click', function (e) {
                e.preventDefault();
            
                var _file = jQuery(this).data('file');
                var _active = jQuery('.mediapicker-modal').data('active-mediapicker-uniqid');

                jQuery('#mediapickerinput-' + _active).val(_file);
                jQuery('.mediapicker-modal').modal('hide');
            }
        );
    },

    initMultipleFileDeletion: function () {
        jQuery('#delete-button').on(
            'click', function () {            
                var $selectedN = Media.countSelectedFileSelectors();

                if(jQuery(this).hasClass('disabled')) {
                    return false;
                }

                if($selectedN == 0) {
                    return false;
                }

                var message = jQuery(this).attr('data-message');

                if (!confirm(message)) {
                    return false;
                }

                Loader.show();

                var _path = jQuery('#append-path').text();

                jQuery('.file-selector:checked').each(
                    function () {
                        var $p = jQuery(this).parent();
                        var $del = $p.find('.confirm-delete');
                        var $deleteUrl = jQuery($del).attr('href');

                        _request = jQuery.ajax(
                            {
                                type: "GET",
                                url: $deleteUrl
                            }
                        );                
                    }
                );

                Media.getFileList(_path);
                Loader.hide();
            }
        );
    },

    countSelectedFileSelectors: function () {
        return jQuery('.file-selector:checked').length;
    },

    getFileList: function (srcPath) {
        Loader.show();
        
        var _request;
        var _url = jQuery('#basehref').text();
        var _mediapicker = jQuery('#mediapicker').text();
        var _token = jQuery('#token').text();

        _request = jQuery.ajax(
            {
                type: "POST",
                url: _url + '/content_media/listing?token=' + _token,
                data: {
                    path: srcPath,
                    mediapicker: _mediapicker
                }
            }
        );

        _request.done(
            function (response) {
                jQuery('#file-listing').html(response);
                jQuery('img.lazy').show().lazyload();
                jQuery('#append-path').text(srcPath);

                window.location.hash = srcPath;

                Media.initFileSelectorBindings();
                Media.initConfirmBindings();
                Media.initFileInfo();

                Loader.hide();
            }
        );
    },

    getFileInfo: function (_filename) {
        Loader.show();

        jQuery('.file-info-dialog').html('').hide();

        var _request;
        var _url = jQuery('#basehref').text();
        var _token = jQuery('#token').text();

        _request = jQuery.ajax(
            {
                type: "POST",
                url: _url + '/content_media/fileinfo?token=' + _token,
                data: {
                    filename: _filename
                }
            }
        );

        _request.done(
            function (response) {
                jQuery('.file-info-dialog[data-filename="'+_filename+'"]').html(response).toggle();

                jQuery('.btn-save-fileinfo').on(
                    'click', function (e) {
                        e.preventDefault();

                        var _filename = jQuery(this).parent().find('.filename-holder').val();
                        var _title = jQuery(this).parent().find('.title-holder').val();
                        var _alt = jQuery(this).parent().find('.alt-holder').val();

                        Media.saveFileInfo(_filename, _title, _alt);
                    }
                );

                Loader.hide();
            }
        );
    },

    initFileInfo: function () {
        jQuery('.file-info-button').on(
            'click', function (e) {
                e.preventDefault();

                var _filename = jQuery(this).data('filename');
                Media.getFileInfo(_filename);
            }
        );
    },

    saveFileInfo: function (_filename, _title, _alt) {
        Loader.show();

        var _request;
        var _url = jQuery('#basehref').text();
        var _token = jQuery('#token').text();

        _request = jQuery.ajax(
            {
                type: "POST",
                url: _url + '/content_media/savefileinfo?token=' + _token,
                data: {
                    filename: _filename,
                    title: _title,
                    alt: _alt
                }
            }
        );

        _request.done(
            function (response) {
                jQuery('.file-info-dialog').html('').hide();

                Loader.hide();
            }
        );
    }

}

jQuery(document).ready(
    function () {

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

    }
);