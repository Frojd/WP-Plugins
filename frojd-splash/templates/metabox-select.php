<?php
$field = $fields[0];
$selected_ids = explode(',', $field['value']);

$selected = get_posts(array(
    'post_type' => $post_type,
    'post__in' => $selected_ids,
    'orderby' => 'post__in',
));

$available = get_posts(array(
    'post_type' => $post_type,
    'post__not_in' => $selected_ids,
    'posts_per_page' => -1,
));

// Should just be one hidden field
?>
<input
    type="<?php echo $field['type']; ?>"
    name="<?php echo $field['name']; ?>"
    id="<?php echo $field['name']; ?>"
    value="<?php echo $field['value']; ?>"
    <?php echo isset($field['checked']) && $field['checked'] ? 'checked' : ''; ?>
    <?php echo isset($field['selected']) && $field['selected'] ? 'selected' : ''; ?>
    <?php echo isset($field['disabled']) && $field['disabled'] ? 'disabled' : ''; ?>
    <?php echo isset($field['data-attr']) ? $field['data-attr'] : ''; ?>
    >

<?php // Selected splashes ?>
<h3>Selected splashes</h3>
<div class="selected frojd-splashes">
<?php foreach ($selected as $splash) : ?>
    <div class="splash">
        <input type="hidden" name="splashes[]" value="<?php echo $splash->ID; ?>">
        <?php
            if(has_post_thumbnail($splash->ID)) {
                echo get_the_post_thumbnail($splash->ID, 'thumb');
            }
        ?>
        <h4><?php echo get_the_title($splash->ID); ?></h4>
    </div>
<?php endforeach; ?>
</div>

<?php // Available splashes ?>
<h3>Available splashes</h3>
<div class="available frojd-splashes">
<?php foreach ($available as $splash) : ?>
    <div class="splash" style="background-color:<?php echo $color; ?>;">
        <input type="hidden" name="splashes[]" value="<?php echo $splash->ID; ?>">
        <?php
            if(has_post_thumbnail($splash->ID)) {
                echo get_the_post_thumbnail($splash->ID, 'thumb');
            }
        ?>
        <h4><?php echo get_the_title($splash->ID); ?></h4>
    </div>
<?php endforeach; ?>
</div>
