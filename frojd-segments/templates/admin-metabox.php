<div class="segments_metabox clearfix" data-id="<?php echo $metabox; ?>" data-order="<?php echo $order; ?>" data-post="<?php echo $postId; ?>">

    <?php if(!empty($availableArticles)) : ?>
        <div class="articles_title">
            <?php _e('Title', $this->translationDomain); ?>: <input type="text" class="articles-title-field" value="<?php echo $title; ?>" style="width: 80%;">
        </div>
    <?php endif; ?>

    <div class="articles articles-selected">
        <input type="hidden" name="frojd_segments_metaboxes[]" value="<?php echo $metabox; ?>">
        <input type="hidden" class="articles-options-field" name="frojd_segments_metabox_<?php echo $metabox; ?>" value="<?php echo htmlentities($options); ?>">
        <?php if(!empty($availableArticles)) : ?>
            <ul class="sortable" data-parent-post-id="<?php echo $postId; ?>">
                <?php foreach ($currentArticles as $article) : ?>
                    <li data-post-id="<?php echo $article->post_id; ?>">
                        <header>
                            <?php echo get_the_title($article->post_id); ?>
                            <?php if (get_post_status($article->post_id) == 'trash') : ?>
                                <span class="post-notice">(<?php _e('Notice: This post has been moved to the trash!'); ?>)</span>
                                <a class="edit" href="edit.php?post_status=trash&post_type=post" title="<?php _e('Edit', $this->translationDomain); ?>" alt="<?php _e('Edit', $this->translationDomain); ?>"></a>
                            <?php else : ?>
                                <a class="edit" href="post.php?post=<?php echo $article->post_id; ?>&action=edit" title="<?php _e('Edit', $this->translationDomain); ?>" alt="<?php _e('Edit', $this->translationDomain); ?>"></a>
                            <?php endif; ?>
                        </header>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p><i><?php _e('No relevant articles available', $this->translationDomain); ?></i></p>
        <?php endif; ?>
    </div>

    <?php if(!empty($availableArticles)) : ?>
        <div class="articles articles-available">
            <header class="article-filter">
                <div class="fields clearfix">
                    <label for="post_search"><?php _e('Search', $this->translationDomain); ?></label>
                    <input type="text" id="post_search" name="search">
                </div>
                <div class="sorting">
                    <label><?php _e('Order by', $this->translationDomain); ?>:</label>
                    <span class="sort-by" data-sort-by="title"><?php _e('Name', $this->translationDomain); ?></span>,
                    <span class="sort-by" data-sort-by="date"><?php _e('Publish date', $this->translationDomain); ?></span>
                </div>
            </header>

            <script type="text/javascript">
                /*This json is used in admin.js in this plugin*/
                var frojd_segments_<?php echo $metabox; ?> = <?php echo json_encode($availableArticles); ?>;
            </script>

            <ul class="draggable">
                <!-- This list is filled by the json above through create_list_from_json in admin.js in this plugin -->
            </ul>
        </div>
    <?php endif; ?>
</div>
