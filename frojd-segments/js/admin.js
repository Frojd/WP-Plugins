(function ($) {
    $(document).ready(function () {

        $.fn.pulsate = function() {
            var element = $(this);
            window.setInterval(function () {
                element.fadeTo('slow', 0.5).fadeTo('slow', 1.0);
            }, 1000);
        };

        var $metabox = $('.segments_metabox');
        var $articles = $metabox.find('.articles');

        $articles.find('.post-notice').pulsate();

        /*This variable is used to track if an elements is supposed to be removed or not*/
        var remove_intent = false;

        $articles.find('.sortable').sortable({
            over: function () {
                remove_intent = false;
            },
            out: function () {
                remove_intent = true;
            },
            beforeStop: function (event, ui) {
                if(remove_intent === true) {
                    ui.item.remove();
                    update_list($(this));
                }
            },
            update: function (event, ui) {
                if(remove_intent === false) {
                    update_list($(this));
                }
            }
        });

        $metabox.find('.meta-box-sortables').on('sortupdate', function() {
            update_list($(this));
        });

        $articles.find('.ui-draggable-dragging').css('width', $articles.find('.segments').width() + 'px');

        $metabox.each(function() {
            var $this = $(this);
            var id = $this.attr('data-id');

            var list = get_list($this);

            if (window['frojd_segments_' + id] !== undefined) {
                var posts = window['frojd_segments_' + id];
                create_list($this, posts);

                $this.find('#post_search').keyup(function() {
                    filter_and_recreate_list($this, posts);
                });
            }

            $this.find('.articles .sort-by').click(function() {
                var sort_by = $(this).attr('data-sort-by');
                var $draggable = $this.find('.articles .draggable');

                switch (sort_by){
                    case 'title':
                        $draggable.find('li:not(.empty)').sort(sort_by_title).appendTo($draggable);
                        break;
                    case 'date':
                        $draggable.find('li:not(.empty)').sort(sort_by_date).appendTo($draggable);
                        break;
                }
            });

            $metabox.find('.articles-title-field').keyup(function () {
                update_list($this.find('.articles .sortable'));
            });

            $metabox.find('.articles.articles-selected .sortable .options:not(.active) .dashicons').click(function(e) {
                $(this).parents('li').siblings('li.segment-item').find('.options').removeClass('active');
                $(this).parents('.options').addClass('active');
            });

            $metabox.find('.articles.articles-selected .sortable .options .close').click(function() {
                $(this).parents('.options').removeClass('active');
            });

            $metabox.find('.articles.articles-selected .sortable .options .save-options').click(save_options);
        });

    });

    function get_list($metabox) {
        var parent_post_id = $metabox.attr('data-post');
        var list = {
            parent_post_id: parent_post_id,
            meta_box: $metabox.attr('data-id'),
            posts: [],
            title: $metabox.find('.articles-title-field').val(),
            order: $metabox.attr('data-order')
        };

        $metabox.find('.articles.articles-selected li').each(function( index ) {
            var $this = $(this);
            if ($this.attr('data-post-id') !== undefined) {
                var item = {
                    post_id: $this.attr('data-post-id'), 
                    sorting: index
                };
                if($this.attr('data-options')) {
                    item.options = typeof($this.attr('data-options')) !== 'undefined' && $this.attr('data-options') !== '' ? JSON.parse($this.attr('data-options')) : ''
                }
                list.posts.push(item);
            }
        });

        return JSON.stringify(list);
    }

    function update_list($container) {
        var metabox = get_list($container.parents('.segments_metabox'));
        $container.parents('.articles').find('.articles-data-field').addClass('changed').val(metabox);
    }

    function create_list($container, items) {
        var list = '';
        var $empty = $container.find('.empty');

        $container.find('.articles .draggable li:not(.empty)').remove();
        $.each(items, function(key, val) {
            var item = items[key];
            var $item = $empty.clone().removeClass('empty');

            $item.attr('data-post-id', item.ID).attr('data-title', item.post_title).attr('data-timestamp', item.timestamp);

            if(item.post_format) {
                $item.find('.post-state-format').addClass('post-format-icon').addClass('post-format-' + item.post_format).show();
            }

            $item.find('.post-title').text(item.post_title).attr('title', item.post_title);
            $item.find('.edit').attr('href', 'post.php?action=edit&post=' + item.ID);
            $container.find('.articles .draggable').append($item);
        });

        $container.find('.articles .draggable li').draggable({
            tolerance: 'point',
            connectToSortable: '.articles .sortable',
            snap: false,
            addClasses: false,
            helper: function() {
                var $clone = $(this).clone();
                /* Set the width of the helper clone to match the container */
                $clone.css('width', $('.articles .draggable').width() + 'px');

                $clone.addClass('segment-item');

                $clone.find('.options').click(function() {
                    $(this).find('.options-lightbox').addClass('active');
                });

                $clone.find('.options .save-options').click(save_options);
                return $clone;
            },
            stop: function () {
                $('.articles .sortable .segment-item').css({
                    width: '',
                    height: ''
                });
            }
        });
    }

    function sort_by_title(a, b) {
        return $(a).attr('data-title').toLowerCase() > $(b).attr('data-title').toLowerCase() ? 1 : -1;
    }

    function sort_by_date(a, b) {
        return $(a).attr('data-timestamp') < $(b).attr('data-timestamp') ? 1 : -1;
    }

    function filter_and_recreate_list($container, items) {
        var filtered = items.slice();
        var search = $container.find('#post_search').val();

        /*Filter by search*/
        filtered = jQuery.grep(filtered, function(element) {
            if (element.post_title.toLowerCase().indexOf(search.toLowerCase()) > -1) {
                return element;
            }
        });

        create_list($container, filtered);
    }

    function save_options(e) {
        e.preventDefault();

        var $lightbox = $(this).parents('.options-lightbox');
        var options = {};

        var $item = $lightbox.parents('li.segment-item');

        $lightbox.find('.option').each(function() {
            var $this = $(this);
            var option = $this.attr('data-option');
            var value = $this.find('input, select').val();
            if($this.find('input[type=checkbox]').length) {
                if(!$this.find('input:checked').length) {
                    value = '';
                }
            }
            $item.attr('data-' + option, value);
            options[option] = value;
        });

        $item.attr('data-options', JSON.stringify(options));

        $lightbox.parents('.options').removeClass('active');

        update_list($item.parents('.sortable'));
    }

}(jQuery));
