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
                        $draggable.find('li').sort(sort_by_title).appendTo($draggable);
                        break;
                    case 'date':
                        $draggable.find('li').sort(sort_by_date).appendTo($draggable);
                        break;
                }
            });

            $this.find('.articles-title-field').keyup(function () {
                update_list($this);
            });
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
                list.posts.push({
                    post_id: $this.attr('data-post-id'), 
                    sorting: index
                });
            }
        });

        return JSON.stringify(list);
    }

    function update_list($container) {
        var metabox = get_list($container.parents('.segments_metabox'));
        $container.parents('.articles').find('.articles-options-field').addClass('changed').val(metabox);
    }

    function create_list($container, items) {
        var list = '';
        $.each(items, function(key, val) {
            var item = items[key];
            list += [
                '<li data-post-id="'+ item.ID +'" data-title="'+ item.post_title +'">',
                    '<header>'+ item.post_title +'<a class="edit" href="post.php?post='+ item.ID +'&action=edit" title="Edit" alt="Edit"></a></header>',
                '</li>'
            ].join('');
        });

        $container.find('.articles .draggable').html($(list));

        $container.find('.articles .draggable li').draggable({
            tolerance: 'point',
            connectToSortable: '.articles .sortable',
            snap: false,
            addClasses: false,
            helper: function() {
                /* Set the width of the helper clone to match the container */
                return $(this).clone().css('width', $(this).width() + 'px');
            }
        });
    }

    function sort_by_title(a, b) {
        return $(a).attr('data-title').toLowerCase() > $(b).attr('data-title').toLowerCase() ? 1 : -1;
    }

    function sort_by_date(a, b) {
        return $(a).attr('data-timestamp').toLowerCase() < $(b).attr('data-timestamp').toLowerCase() ? 1 : -1;
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

}(jQuery));
