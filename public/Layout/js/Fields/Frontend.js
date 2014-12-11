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

jQuery( document ).ready(function() {

    Loader.show();

    // Bind tabs
    Frontend.bindTabs();

    // Bind repeatable buttons
    Frontend.bindRepeatable('.repeatable-field-button');

    // Bind repeatable group buttons
    Frontend.bindRepeatableGroup();    

    // Bind delete buttons to repeated elements
    Frontend.bindRepeatableDelete();

    // Bind delete buttons to repeated elements
    Frontend.bindRepeatableGroupDelete();

    // Bind moveup to repeatable elements
    Frontend.bindMoveRepeatableUp();

    // Bind movedown to repeatable elements
    Frontend.bindMoveRepeatableDown();

    // Bind save article button
    Frontend.bindSaveArticle();

    // Bind trash article button
    Frontend.bindTrashArticle();

    // Bind category selector
    Frontend.bindMoveArticle();

    // Bind published- toggler
    Frontend.bindPublished();

    // Bind notice about unsaved changes
    Frontend.bindLeaveNotice();

    // Generate uniqids for group separators
    Frontend.generateUniqIDs();

    // Bind help texts
    Frontend.bindHelpTexts();

    Loader.hide();

});

var Frontend = {

    bindTabs: function() {
        // Make the first tab active
        jQuery('.tab-pane').first().removeClass('hidden');        
        jQuery('#groups-tab').find('li').first().addClass('active');

        // Mark first panel active
        jQuery('.tab-content').each(function() {
            jQuery(this).find('.tab-pane').first().addClass('active');
        });

        // Enable group tabs    
        jQuery('#groups-tab').tab();

        jQuery('#groups-tab a').on('click', function(e) {
            e.preventDefault();

            jQuery(this).tab('show');
        });

        // Show first tab by default
        jQuery('#groups-tab a:first').tab('show');        
    },

    bindRepeatable: function(el) {
        // Bind Repeatable button

        jQuery(el).on('click', function(e) {
            e.preventDefault();

            // Get the repeatable element
            var $el = jQuery(this).parent().find('.repeatable-holder').first();

            // Get the last repeated element
            var $last = jQuery(this).parent().find('.repeatable-holder').last();

            // Clone the element
            var $clone = $el.clone();

            // Reset values on all input fields
            $clone.find('input, select, textarea, .resetable').not(':input[type=button], :input[type=submit], :input[type=reset]').each(function() {
                jQuery(this).val('');
                jQuery(this).attr('name', FrontendHelpers.uniqid() + '[' + jQuery(this).data('field-name') + '][]');
            });

            // Find delete button
            var $deleteButton = jQuery('#repeatable-delete').html();

            // Append the delete button to element
            $clone.append($deleteButton);

            // Find the delete button and bind it
            $clone.find('.repeatable-field-button-delete').each(function() {
                jQuery(this).on('click', function(e) {
                    e.preventDefault();

                    if(confirm(jQuery('#translation-delete').attr('data-translation-string'))) {
                        jQuery(this).parent().remove();
                    }
                });
            });

            // Disable moveup/down until save
            $clone.find('.move-repeatable').each(function() {
                jQuery(this).hide();
            });

            // Insert the cloned element after last repeated element
            $last.after($clone);

            // Bind help texts
            Frontend.bindHelpTexts();
        });
    },

    bindRepeatableGroup: function() {
        // Bind Repeatable button
        jQuery('.repeatable-group-button').on('click', function(e) {
            e.preventDefault();

            // Get the repeatable element
            var $el = jQuery(this).parent().parent().parent().find('.repeatable-group-holder').first();

            // Get the last repeated element
            var $last = jQuery(this).parent().parent().parent().find('.repeatable-group-holder').last();

            // Clone the element
            var $clone = $el.clone();

            // Reset values on all input fields
            $clone.find('input, select, textarea, .resetable').each(function() {
                jQuery(this).val('');
                jQuery(this).attr('name', FrontendHelpers.uniqid() + '[' + jQuery(this).data('field-name') + '][]');
            });

            // Find delete button
            var $deleteButton = jQuery('#repeatable-group-delete').html();

            // Append the delete button to element
            $clone.append($deleteButton);

            // Find the delete button and bind it
            $clone.find('.repeatable-group-button-delete').each(function() {
                jQuery(this).on('click', function(e) {
                    e.preventDefault();

                    if(confirm(jQuery('#translation-delete').data('translation-string'))) {
                        jQuery(this).parent().parent().parent().remove();
                    }
                });
            });

            // Remove repeated elements
            $clone.find('.repeatable-field-button-delete').each(function() {
                jQuery(this).parent().remove();
            });            

            // Bind repeatables in group
            $clone.find('.repeatable-field-button').each(function() {
                Frontend.bindRepeatable(this);
            });

            // Disable moveup/down until save
            $clone.find('.move-repeatable').each(function() {
                jQuery(this).hide();
            });

            // Find the group separator
            $clone.find('.group-separator').each(function() {
                jQuery(this).attr('name', FrontendHelpers.uniqid() + '[group_separator]');
            });

            // Insert the cloned element after last repeated element
            $last.after($clone);

            // Bind help texts
            Frontend.bindHelpTexts();
        });
    },    

    bindRepeatableDelete: function() {
        // Bind delete events for repeatables
        jQuery('.field-holder').find('.repeatable-field-button-delete').each(function() {
            jQuery(this).on('click', function(e) {
                e.preventDefault();

                if(confirm(jQuery('#translation-delete').data('translation-string'))) {
                    jQuery(this).parent().remove();
                }
            });
        });
    },

    bindRepeatableGroupDelete: function() {
        // Bind delete events for repeatables
        jQuery('.repeatable-group-holder').find('.repeatable-group-button-delete').each(function() {
            jQuery(this).on('click', function(e) {
                e.preventDefault();

                if(confirm(jQuery('#translation-delete').data('translation-string'))) {
                    jQuery(this).parent().parent().parent().remove();
                }
            });
        });
    },    

    bindMoveRepeatableUp: function() {
        jQuery('.move-repeatable-up').on('click', function() {
            var $holder = jQuery(this).parent().parent();
            var $prev = jQuery($holder).prev();
            var $c = 0;

            jQuery($prev).find('.repeatable-field-button-delete');
            $prev.find('.repeatable-field-button-delete').each(function() {
                $c++;
            });

            // Only move if element above has repeatables delete button
            if($c == 1) {
                jQuery($holder).insertBefore($prev);
            }
        })
    },

    bindMoveRepeatableDown: function() {
        jQuery('.move-repeatable-down').on('click', function() {
            var $holder = jQuery(this).parent().parent();
            var $next = jQuery($holder).next();
            var $c = 0;

            jQuery($next).find('.repeatable-field-button-delete');
            $next.find('.repeatable-field-button-delete').each(function() {
                $c++;
            });

            // Only move if element above has repeatables delete button
            if($c == 1) {
                jQuery($holder).insertAfter($next);
            }
        })
    },

    bindSaveArticle: function() {
        // Bind save button
        jQuery('#save-article').on('click', function(e) {
            e.preventDefault();

            if(confirm(jQuery('#translation-save').data('translation-string'))) {
                Frontend.resetLeaveNotice();

                jQuery('#article-form').submit();
            }
        }); 
    },

    bindTrashArticle: function() {
        // Bind trash button
        jQuery('#trash-article').on('click', function() {
            var $cat = jQuery(this).text();

            return confirm(
                jQuery('#translation-trash').data('translation-string')
            );
        }); 
    },    

    bindMoveArticle: function() {
        // Bind move button
        jQuery('#move-to-category').on('click', function() {
            var $cat = jQuery(this).text();

            return confirm(
                jQuery('#translation-move').data('translation-string') + ' ' + $cat + '?' + '\n\n' +
                jQuery('#translation-move-notice').data('translation-string')
            );
        }); 
    },

    bindPublished: function() {
        // Bind published status toggler
        jQuery('#publish-toggle').on('click', function(e) {
            e.preventDefault();

            if(jQuery(this).hasClass('btn-success')) {
                jQuery(this).removeClass('btn-success');
                jQuery(this).addClass('btn-warning');
                jQuery(this).text(jQuery('#translation-unpublished').data('translation-string'));
                jQuery('#published').val(0);
            } else {
                jQuery(this).removeClass('btn-warning');
                jQuery(this).addClass('btn-success');
                jQuery(this).text(jQuery('#translation-published').data('translation-string'));
                jQuery('#published').val(1);
            }
        }); 
    },

    bindHelpTexts: function() {
        jQuery('.help-text').mouseenter(function() {
            jQuery(this).popover('show');
        }).mouseleave(function() {
            jQuery(this).popover('hide');
        });
    },

    generateUniqIDs: function() {
        jQuery('.group-separator').each(function() {
            jQuery(this).attr('name', FrontendHelpers.uniqid() + '[group_separator]');
        });
    },

    bindLeaveNotice: function() {
        jQuery('body').one("keypress", function() {
            window.onbeforeunload = function() {
                return jQuery('#translation-leave-notice').data('translation-string');
            };
        });
    },

    resetLeaveNotice: function() {
        window.onbeforeunload = null;
    }

}

var FrontendHelpers = {

    uniqid: function(prefix, more_entropy) {
        //  discuss at: http://phpjs.org/functions/uniqid/
        // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        //  revised by: Kankrelune (http://www.webfaktory.info/)
        //        note: Uses an internal counter (in php_js global) to avoid collision
        //        test: skip
        //   example 1: uniqid();
        //   returns 1: 'a30285b160c14'
        //   example 2: uniqid('foo');
        //   returns 2: 'fooa30285b1cd361'
        //   example 3: uniqid('bar', true);
        //   returns 3: 'bara20285b23dfd1.31879087'

        if (typeof prefix === 'undefined') {
         prefix = '';
        }

        var retId;
        var formatSeed = function(seed, reqWidth) {
            seed = parseInt(seed, 10).toString(16); // to hex str

            if (reqWidth < seed.length) { // so long we split
                return seed.slice(seed.length - reqWidth);
            }

            if (reqWidth > seed.length) { // so short we pad
                return Array(1 + (reqWidth - seed.length)).join('0') + seed;
            }
            return seed;
        };

        // BEGIN REDUNDANT
        if (!this.php_js) {
            this.php_js = {};
        }

        // END REDUNDANT
        if (!this.php_js.uniqidSeed) { // init seed with big random int
            this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
        }

        this.php_js.uniqidSeed++;

        retId = prefix; // start with prefix, add current milliseconds hex string
        retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
        retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string

        if (more_entropy) {
            // for more entropy we add a float lower to 10
            retId += (Math.random() * 10).toFixed(8).toString();
        }

        return retId;
    }

}
