<?php
/**
 *
 * @package   Fröjd - Segments
 * @author    Fröjd - Sara Öjelind
 * @license   Fröjd Interactive AB (All Rights Reserved).
 * @link      http://frojd.se
 * @copyright Fröjd Interactive AB (All Rights Reserved).
 *
 * Plugin Name: Fröjd - Segments
 * Plugin URI: http://frojd.se
 * Description: Plugin with functionality to add metaboxes to specific posts/pages edit view where editor can drag'n'drop articles to be added to view.
 * Version: 1.0
 * Author: Fröjd - Sara Öjelind
 * Author URI: http://frojd.se
 * Text Domain: frojd-segments
 * License: Fröjd Interactive AB (All Rights Reserved).
 */

namespace Frojd\Plugin\FrojdSegments;

class FrojdSegments {
    const VERSION = '1.0';

    protected $pluginSlug = 'segments';
    protected static $instance = null;

    protected $pluginBase;
    protected $pluginRelBase;

    protected $translationDomain = 'frojd-segments';

    public function __construct() {
        $this->pluginBase = rtrim(dirname(__FILE__), '/');
        $this->pluginRelBase = dirname(plugin_basename(__FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activationHook'));
        register_deactivation_hook(__FILE__, array(&$this, 'deactivationHook'));
        register_uninstall_hook(__FILE__, array(get_class(), 'uninstallHook'));

        // Set the translation domain
        $this->translationDomain = $this->pluginRelBase;

        add_action('plugins_loaded', array($this, 'pluginsLoadedHook'));

        // Enqueue scripts and style
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScriptsHook'));

        // For saving metabox values to post
        add_action('save_post', array($this, 'savePostHook'));

        // Action for creating segment metaboxes
        add_action('frojd_segments_add_metaboxes', array($this, 'frojdSegmentsAddMetaboxesHook'));

        // Filter for returning segments
        add_filter('frojd_segments_get_segment', array($this, 'frojdSegmentsGetSegmentHook'), 10, 2);
        add_filter('frojd_segments_get_segments', array($this, 'frojdSegmentsGetSegmentsHook'), 10, 1);

        // Filters for editing articles drag content
        add_action('frojd_segments_article_show_drag_content', array($this, 'frojdSegmentsArticleShowDragContent'), 10, 1);
        add_filter('frojd_segments_article_show_post_format', array($this, 'frojdSegmentsArticleShowPostFormat'), 10, 1);
        add_filter('frojd_segments_article_show_edit_button', array($this, 'frojdSegmentsArticleShowEditButton'), 10, 1);
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /*------------------------------------------------------------------------*
     * Hooks
     *------------------------------------------------------------------------*/

    public function activationHook($networkWide) {
    }

    public function deactivationHook($networkWide) {
    }

    public static function uninstallHook($networkWide) {
        if (! defined('WP_UNINSTALL_PLUGIN')) {
            die();
        }
    }

    public function pluginsLoadedHook() {
        load_plugin_textdomain($this->translationDomain, false, dirname(plugin_basename( __FILE__ )) . '/languages');
    }

    public function adminEnqueueScriptsHook($page) {
        if($page == 'post.php' || $page == 'post-new.php') {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-droppable');
            wp_enqueue_script($this->pluginRelBase . '-admin-script',
                plugins_url($this->pluginRelBase . '/js/admin.js'), array('jquery'));
            wp_enqueue_style($this->pluginRelBase . '-admin-style',
                plugins_url($this->pluginRelBase . '/css/admin.css'));
        }
    }

    public function savePostHook() {
        global $post;

        if(isset($_POST['frojd_segments_metaboxes'])) {
            $screen = get_current_screen();
            $is_edit = isset($_GET['action']) && $_GET['action'] == 'edit';
            $is_post_screen = !empty($screen) && ($screen->base == 'post' || $screen->action == 'add');

            /* Make sure that the publish action is done from the post edit view and not the quick edit view */
            if($is_edit || $is_post_screen) {

                // All metaboxes are included into an array, loop through it to get the id's and save the list
                $metaboxes = array();
                foreach($_POST['frojd_segments_metaboxes'] as $metabox) {
                    $metaboxes[] = $metabox;

                    // Get the data from the specific metabox and save
                    if(isset($_POST['frojd_segments_metabox_' . $metabox])) {
                        update_post_meta($post->ID, 'frojd_segments_metabox_' . $metabox, $_POST['frojd_segments_metabox_' . $metabox]);
                    }
                }
                update_post_meta($post->ID, 'frojd_segments_metaboxes', $metaboxes);
            }
        }
    }

    /*
     * Action hook for adding segments metabox to edit view
     */
    public function frojdSegmentsAddMetaboxesHook($metaboxes) {
        foreach($metaboxes as $id => $metabox) {
            $postTypes = array('page');
            if(isset($metabox['show_on']['post_types'])) {
                $postTypes = $metabox['show_on']['post_types'];
            }

            $is_valid_terms = (isset($metabox['show_on']['valid_terms']) && $this->checkValidTerms($metabox['show_on']['valid_terms'])) || !isset($metabox['show_on']['valid_terms']);
            $is_post_meta = (isset($metabox['show_on']['post_meta']) && $this->checkPostMeta($metabox['show_on']['post_meta'])) || !isset($metabox['show_on']['post_meta']);

            if($is_valid_terms && $is_post_meta) {
                foreach($postTypes as $postType) {
                    add_meta_box(
                        $id,
                        isset($metabox['title']) ? $metabox['title'] : 'Select articles',
                        array($this, 'metaboxArticles'),
                        $postType,
                        'normal',
                        'low',
                        $metabox
                    );
                }
            }
        }
    }

    public function frojdSegmentsGetSegmentHook($postId, $metabox) {
        $data = get_post_meta($postId, 'frojd_segments_metabox_' . $metabox, true);
        return json_decode($data);
    }

    public function frojdSegmentsGetSegmentsHook($postId) {
        $metaboxes = get_post_meta($postId, 'frojd_segments_metaboxes', true);
        $segments = array();
        if(!empty($metaboxes)) {
            foreach($metaboxes as $metabox) {
                $segment = $this->frojdSegmentsGetSegmentHook($postId, $metabox);
                $segments[$metabox] = $segment;
            }
        }
        return $segments;
    }

    public function frojdSegmentsArticleShowDragContent($postId) {
        do_action('frojd_segments_article_show_post_format', $postId);
        
        echo '<div class="post-title">' . get_the_title($postId) . '</div>';

        do_action('frojd_segments_article_show_edit_button', $postId);
    }

    public function frojdSegmentsArticleShowPostFormat($postId) {
        $postFormat = get_post_format($postId);
        if(!empty($postFormat)) : ?>
            <div class="post-state-format post-format-icon post-format-<?php echo $postFormat; ?>"></div>
        <?php endif;
    }

    public function frojdSegmentsArticleShowEditButton($postId) {
        $postStatus = get_post_status($postId);
        if($postStatus == 'trash') : ?>
            <span class="post-notice">(<?php _e('Notice: This post has been moved to the trash!', $this->translationDomain); ?>)</span>
            <a class="edit dashicons dashicons-edit" href="edit.php?post_status=trash&post_type=post" alt="<?php _e('Edit', $this->translationDomain); ?>"></a>
        <?php else : ?>
            <a class="edit dashicons dashicons-edit" href="post.php?post=<?php echo $postId; ?>&action=edit" alt="<?php _e('Edit', $this->translationDomain); ?>"></a>
        <?php endif;
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    /*
     * The callback function to be used when adding meta box from another location
     */
    public function metaboxArticles($post, $metabox) {
        $override = array();
        if(isset($metabox['args']['selection'])) {
            $override = $metabox['args']['selection'];
        }
        // Get the avialable articles to be able to choose from
        $availableArticles = $this->getArticles($post->ID, $override);
        if($availableArticles) {
            foreach($availableArticles as $i => $article) {
                $availableArticles[$i]->post_format = get_post_format($article->ID);
                $availableArticles[$i]->timestamp = get_the_time('U', $article->ID);
            }
        }

        // Get the current metabox data and merge with other data
        $data = get_post_meta($post->ID, 'frojd_segments_metabox_' . $metabox['id'], true);
        $segment = json_decode($data);

        $currentArticles = array();
        if(!empty($segment->posts)) {
            $currentArticles = $segment->posts;
        }

        $options = array();
        if(isset($metabox['args']['options'])) {
            $options = $metabox['args']['options'];
        }

        $templateVars = array(
            'postId'            => $post->ID,
            'metabox'           => $metabox['id'],
            'title'             => !empty($segment->title) ? $segment->title : '',
            'order'             => !empty($segment->order) ? $segment->order : '0',
            'postType'          => get_post_type($post->ID),
            'data'              => $data,
            'availableArticles' => $availableArticles,
            'currentArticles'   => $currentArticles,
            'options'           => $options
        );
        $this->renderTemplate('admin-metabox', $templateVars);
    }

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/

    /*
     * Returns a list of posts using default values and overriding when neccessary
     */
    private function getArticles($postId, $override = array()) {
        $postTypes = array('post');

        // The following args will be overwritten by the override variable
        $default = array(
            'post_type'     => $postTypes,
            'orderby'       => 'title',
            'order'         => 'ASC',
            'numberposts'   => -1,
            'tax_query'     => array('relation' => 'OR')
        );
        $args = array_merge($default, $override);
        
        $posts = get_posts($args);
        if($posts) {
            return $posts;
        }
        return false;
    }

    private function checkValidTerms($args) {
        $id = get_the_ID();
        foreach($args as $terms) {
            foreach($terms as $taxonomy => $term) {
                if(!has_term($term, $taxonomy, $id)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function checkPostMeta($args) {
        $id = get_the_ID();
        foreach($args as $post_meta) {
            foreach($post_meta as $key => $value) {
                if(get_post_meta($id, $key, true) != $value) {
                    return false;
                }
            }
        }
        return true;
    }

    private function renderTemplate($name, $vars = array()) {
        foreach ($vars as $key => $val) {
            $$key = $val;
        }

        $path = $this->pluginBase . '/templates/' . $name . '.php';
        if (file_exists($path)) {
            include($path);
        } else {
            echo '<p>Rendering of template failed</p>';
        }
    }
}

FrojdSegments::getInstance();
