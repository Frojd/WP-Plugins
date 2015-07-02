<div class="wrap">
    <?php 
        if(isset($_POST['opcache-reset']) && $_POST['opcache-reset']) {
            $success = opcache_reset();
            if($success) : ?>
                <div id="message" class="updated notice is-dismissible"><p><?php _e('OpCache has been reset', self::TRANSLATION_SLUG); ?></p><button type="button" class="notice-dismiss"></button></div>
            <?php else : ?>
                <div id="message" class="error notice is-dismissible"><p><?php _e('OpCache couldn\'t be reset, try again!', self::TRANSLATION_SLUG); ?></p><button type="button" class="notice-dismiss"></button></div>
            <?php endif;
        }
    ?>
    <h2><?php _e('Fröjd OpCache', self::TRANSLATION_SLUG); ?></h2>
    
    <form id="frojd-opcache-form" method="post" action="options-general.php?page=<?php echo self::SETTING_NAME; ?>">
        <input type="hidden" name="opcache-reset" value="true">
        <?php 
            submit_button(__('Reset OpCache', self::TRANSLATION_SLUG), 'primary');
        ?>
    </form>
</div>