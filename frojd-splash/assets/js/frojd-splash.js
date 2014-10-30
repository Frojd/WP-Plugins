jQuery(function ($) {
    'use strict';

    var metabox = $('#splash_selection_page');
    var store = metabox.find('[data-splash_selection_store]');
    var available = metabox.find('.available');
    var selected = metabox.find('.selected');
    var join = Array.prototype.join.call.bind(Array.prototype.join);

    available.add(selected).sortable({
        connectWith: '.frojd-splashes',
        update: function () {
            var splashIds = join(selected.find('.splash input').map(function () {
                return $(this).val();
            }), ',');

            store.val(splashIds);
        }
    });
});
