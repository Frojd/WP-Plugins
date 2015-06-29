<div class="options">
    <span class="dashicons dashicons-admin-generic"></span>
    <div class="options-lightbox">
        <div class="title">
            <?php _e('Select options', $this->translationDomain); ?>
            <button type="button" id="wp-link-close" class="close"><span class="screen-reader-text"><?php _e('Close', $this->translationDomain); ?></span></button>
        </div>
        <table>
            <tbody>
                <?php foreach($options as $option => $selector) : ?>
                    <?php
                        $selected = isset($article->options->$option) ? $article->options->$option : '';
                        $type = isset($selector['type']) ? $selector['type'] : 'text';
                        $id = 'frojd_segments_metabox_' . $metabox . '_options_' . $option;

                        if($type == 'checkbox') {
                            $selected = isset($article->options->$option) ? $selected : (isset($selector['checked']) && $selector['checked'] ? 'on' : '');
                        }
                    ?>
                    <tr class="option" data-option="<?php echo $option; ?>">
                        <td><label for="<?php echo $id; ?>"><?php echo isset($selector['label']) ? $selector['label'] : $option; ?></label></td>
                        <td>
                            <?php switch($type) :
                                case 'textarea': ?>
                                    <textarea name="<?php echo $id; ?>"><?php echo $selected; ?></textarea>
                                    <?php break;

                                case 'select': ?>
                                    <?php
                                        $keys = array_keys($selector['options']);
                                        $selected = !empty($selected) ? $selected : $keys[0];
                                    ?>
                                    <select name="<?php echo $id; ?>">
                                        <?php foreach($selector['options'] as $value => $label) : ?>
                                            <option value="<?php echo $value; ?>"<?php echo $value == $selected ? ' selected' : ''; ?>><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php break;

                                case 'checkbox': ?>
                                    <input type="<?php echo $type; ?>" name="<?php echo $id; ?>" <?php echo $selected == 'on' ? ' checked' : ''; ?>>
                                    <?php break;

                                default: ?>
                                    <input type="<?php echo $type; ?>" name="<?php echo $id; ?>" value="<?php echo $selected; ?>">
                                    <?php break; ?>
                            <?php endswitch; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="submitbox"><button class="save-options button button-primary"><?php _e('Save', $this->translationDomain); ?></button></div>
    </div>
</div>