
    <li id="menu-item-<?php echo $item_id; ?>" class="<?php echo implode(' ', $classes); ?>">
        <dl class="menu-item-bar">
            <dt class="menu-item-handle">
                <span class="item-title"><?php echo esc_html($title); ?></span>
                    <span class="item-controls">
                        <span class="item-type"><?php echo esc_html($item->type_label); ?></span>
                        <span class="item-order hide-if-js">
                            <a href="<?php
                            echo wp_nonce_url(
                                add_query_arg(
                                    array(
                                        'action' => 'move-up-menu-item',
                                        'menu-item' => $item_id,
                                    ),
                                    remove_query_arg($removed_args, admin_url('nav-menus.php'))
                                ),
                                'move-menu_item'
                            );
                            ?>" class="item-move-up"><abbr title="<?php esc_attr_e('Move up'); ?>">&#8593;</abbr></a>
                            |
                            <a href="<?php
                            echo wp_nonce_url(
                                add_query_arg(
                                    array(
                                        'action' => 'move-down-menu-item',
                                        'menu-item' => $item_id,
                                    ),
                                    remove_query_arg($removed_args, admin_url('nav-menus.php'))
                                ),
                                'move-menu_item'
                            );
                            ?>" class="item-move-down"><abbr title="<?php esc_attr_e('Move down'); ?>">
                                    &#8595;</abbr></a>
                        </span>
                        <a class="item-edit" id="edit-<?php echo $item_id; ?>"
                           title="<?php esc_attr_e('Edit Menu Item'); ?>" href="<?php
                        echo (isset($_GET['edit-menu-item']) && $item_id == $_GET['edit-menu-item']) ? admin_url('nav-menus.php') : add_query_arg('edit-menu-item', $item_id, remove_query_arg($removed_args, admin_url('nav-menus.php#menu-item-settings-' . $item_id)));
                        ?>"><?php _e('Edit Menu Item'); ?></a>
                    </span>
            </dt>
        </dl>

        <div class="menu-item-settings" id="menu-item-settings-<?php echo $item_id; ?>">
            <?php if ('custom' == $item->type) : ?>
                <p class="field-url description description-wide">
                    <label for="edit-menu-item-url-<?php echo $item_id; ?>">
                        <?php _e('URL'); ?><br/>
                        <input type="text" id="edit-menu-item-url-<?php echo $item_id; ?>"
                               class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo $item_id; ?>]"
                               value="<?php echo esc_attr($item->url); ?>"/>
                    </label>
                </p>
            <?php endif; ?>
            <p class="description description-thin">
                <label for="edit-menu-item-title-<?php echo $item_id; ?>">
                    <?php _e('Navigation Label'); ?><br/>
                    <input type="text" id="edit-menu-item-title-<?php echo $item_id; ?>"
                           class="widefat edit-menu-item-title" name="menu-item-title[<?php echo $item_id; ?>]"
                           value="<?php echo esc_attr($item->title); ?>"/>
                </label>
            </p>

            <p class="description description-thin">
                <label for="edit-menu-item-attr-title-<?php echo $item_id; ?>">
                    <?php _e('Title Attribute'); ?><br/>
                    <input type="text" id="edit-menu-item-attr-title-<?php echo $item_id; ?>"
                           class="widefat edit-menu-item-attr-title"
                           name="menu-item-attr-title[<?php echo $item_id; ?>]"
                           value="<?php echo esc_attr($item->post_excerpt); ?>"/>
                </label>
            </p>

            <p class="field-link-target description">
                <label for="edit-menu-item-target-<?php echo $item_id; ?>">
                    <input type="checkbox" id="edit-menu-item-target-<?php echo $item_id; ?>" value="_blank"
                           name="menu-item-target[<?php echo $item_id; ?>]"<?php checked($item->target, '_blank'); ?> />
                    <?php _e('Open link in a new window/tab'); ?>
                </label>
            </p>

            <p class="field-css-classes description description-thin">
                <label for="edit-menu-item-classes-<?php echo $item_id; ?>">
                    <?php _e('CSS Classes (optional)'); ?><br/>
                    <input type="text" id="edit-menu-item-classes-<?php echo $item_id; ?>"
                           class="widefat code edit-menu-item-classes" name="menu-item-classes[<?php echo $item_id; ?>]"
                           value="<?php echo esc_attr(implode(' ', $item->classes)); ?>"/>
                </label>
            </p>
            
            <p class="field-xfn description description-thin">
                <label for="edit-menu-item-xfn-<?php echo $item_id; ?>">
                    <?php _e('Link Relationship (XFN)'); ?><br/>
                    <input type="text" id="edit-menu-item-xfn-<?php echo $item_id; ?>"
                           class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?php echo $item_id; ?>]"
                           value="<?php echo esc_attr($item->xfn); ?>"/>
                </label>
            </p>

            <p class="field-description description description-wide">
                <label for="edit-menu-item-description-<?php echo $item_id; ?>">
                    <?php _e('Description'); ?><br/>
                    <textarea id="edit-menu-item-description-<?php echo $item_id; ?>"
                              class="widefat edit-menu-item-description" rows="3" cols="20"
                              name="menu-item-description[<?php echo $item_id; ?>]"><?php echo esc_html($item->description); // textarea_escaped ?></textarea>
                    <span
                        class="description"><?php _e('The description will be displayed in the menu if the current theme supports it.'); ?></span>
                </label>
            </p>

            <p class="field-image description description-wide">
                <?php if (!has_post_thumbnail($item_id)) : ?>
                    <label for="edit-menu-item-image-<?php echo $item_id; ?>">
                        <?php _e('Image', 'menu-image'); ?><br/>
                        <input type="file" name="menu-item-image_<?php echo $item_id; ?>"
                               id="edit-menu-item-image-<?php echo $item_id; ?>"/>
                    </label>
                <?php else: ?>
                    <?php print get_the_post_thumbnail($item_id); ?> <label><?php _e("Remove image", 'menu-image'); ?> <input type="checkbox"
                                                                                   name="menu_item_remove_image[<?php echo $item_id; ?>]"/></label>
                <?php endif; ?>
            </p>

            <div class="menu-item-actions description-wide submitbox">
                <?php if ('custom' != $item->type && $original_title !== FALSE) : ?>
                    <p class="link-to-original">
                        <?php printf(__('Original: %s'), '<a href="' . esc_attr($item->url) . '">' . esc_html($original_title) . '</a>'); ?>
                    </p>
                <?php endif; ?>
                <a class="item-delete submitdelete deletion" id="delete-<?php echo $item_id; ?>" href="<?php
                echo wp_nonce_url(
                    add_query_arg(
                        array(
                            'action' => 'delete-menu-item',
                            'menu-item' => $item_id,
                        ),
                        remove_query_arg($removed_args, admin_url('nav-menus.php'))
                    ),
                    'delete-menu_item_' . $item_id
                ); ?>"><?php _e('Remove'); ?></a> <span class="meta-sep"> | </span> <a class="item-cancel submitcancel"
                                                                                       id="cancel-<?php echo $item_id; ?>"
                                                                                       href="<?php    echo esc_url(add_query_arg(array('edit-menu-item' => $item_id, 'cancel' => time()), remove_query_arg($removed_args, admin_url('nav-menus.php'))));
                                                                                       ?>#menu-item-settings-<?php echo $item_id; ?>"><?php _e('Cancel'); ?></a>
            </div>

            <input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo $item_id; ?>]"
                   value="<?php echo $item_id; ?>"/>
            <input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo $item_id; ?>]"
                   value="<?php echo esc_attr($item->object_id); ?>"/>
            <input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo $item_id; ?>]"
                   value="<?php echo esc_attr($item->object); ?>"/>
            <input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo $item_id; ?>]"
                   value="<?php echo esc_attr($item->menu_item_parent); ?>"/>
            <input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo $item_id; ?>]"
                   value="<?php echo esc_attr($item->menu_order); ?>"/>
            <input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo $item_id; ?>]"
                   value="<?php echo esc_attr($item->type); ?>"/>
        </div>
        <!-- .menu-item-settings-->
        <ul class="menu-item-transport"></ul>