<div class="segments_metabox clearfix" data-id="<?php echo $metabox; ?>" data-order="<?php echo $order; ?>" data-post="<?php echo $postId; ?>">

    <?php if(!empty($availableArticles)) : ?>
        <div class="articles_title">
            <?php _e('Title', $this->translationDomain); ?>: <input type="text" class="articles-title-field" value="<?php echo $title; ?>" style="width: 80%;">
        </div>
    <?php endif; ?>

    <div class="articles articles-selected">
        <input type="hidden" name="frojd_segments_metaboxes[]" value="<?php echo $metabox; ?>">
        <input type="hidden" class="articles-data-field" name="frojd_segments_metabox_<?php echo $metabox; ?>" value="<?php echo htmlentities($data); ?>">
        <?php if(!empty($availableArticles)) : ?>
            <ul class="sortable" data-parent-post-id="<?php echo $postId; ?>">
                <?php foreach ($currentArticles as $article) : ?>
                    <?php 
                        $postStatus = get_post_status($article->post_id);
                        $attr = '';
                        if(isset($article->options)) {
                            $attr .= ' data-options="' . htmlentities(json_encode($article->options)) . '"';
                        }

                        foreach($options as $option => $selectors) {
                            $selected = isset($article->options->$option) ? $article->options->$option : '';
                            if(isset($selectors['type']) && $selectors['type'] == 'checkbox') {
                                $selected = isset($article->options->$option) ? $selected : (isset($selectors['checked']) && $selectors['checked'] ? 'on' : '');
                            }
                            if(!empty($selected)) {
                                $attr .= ' data-' . $option . '="' . $selected . '"';
                            }
                        }
                    ?>
                    <?php if($postStatus) : ?>
                        <li class="segment-item" title="<?php echo get_the_title($article->post_id); ?>" data-post-id="<?php echo $article->post_id; ?>"<?php echo $attr; ?>>
                            <header>
                                <?php 
                                    do_action('frojd_segments_article_show_drag_content', $article->post_id);

                                    if(!empty($options)) {
                                        $vars = array(
                                            'metabox'   => $metabox,
                                            'article'   => $article,
                                            'options'   => $options
                                        );
                                        $this->renderTemplate('admin-metabox-options', $vars);
                                    }
                                ?>
                            </header>
                        </li>
                    <?php endif; ?>
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
                <li class="empty">
                    <header>
                        <div class="post-state-format" style="display: none"></div>
                        
                        <div class="post-title" title=""></div>

                        <a class="edit dashicons dashicons-edit" href="post.php?action=edit" title="<?php _e('Edit', $this->translationDomain); ?>" alt="<?php _e('Edit', $this->translationDomain); ?>"></a>

                        <?php
                            if(!empty($options)) {
                                $vars = array(
                                    'metabox'   => $metabox,
                                    'article'   => array(),
                                    'options'   => $options
                                );
                                $this->renderTemplate('admin-metabox-options', $vars);
                            }
                        ?>
                    </header>
                </li>
            </ul>
        </div>
    <?php endif; ?>
</div>
