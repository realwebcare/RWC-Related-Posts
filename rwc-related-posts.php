<?php
/**
 * Plugin Name:         RWC Related Posts
 * Plugin URI:          https://realwebcare.com
 * Description:         Display related posts dynamically to improve user engagement and keep visitors exploring your content.
 * Version:             1.0.0
 * Requires at least:   5.2
 * Requires PHP:        7.4
 * Author:              Realwebcare
 * Author URI:          https://www.realwebcare.com/
 * Text Domain:         rwc-related-posts
 * Domain Path:         /languages
 * License:             GPL v3 or later
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined('ABSPATH') ) {
    exit;
}

/**
 * Class RWC_Related_Posts
 * 
 * This class implements a related posts feature for WordPress. It uses the Singleton pattern 
 * to ensure only one instance of the class is created during runtime. The class hooks into 
 * WordPress to:
 * 
 * - Append related posts to the content of single post views.
 * - Enqueue custom styles for the related posts display.
 * - Define custom image sizes for related post thumbnails.
 * - Load necessary dependencies and classes required for functionality.
 * 
 * The related posts are determined based on the categories of the current post and are displayed 
 * as a list of up to 5 posts with thumbnails, titles, excerpts, and author information.
 */
if ( ! class_exists( 'RWC_Related_Posts' ) ) {
    class RWC_Related_Posts {

        // Hold the single instance of the class.
        private static $instance;

        // Constructor is private to enforce the Singleton pattern.
        private function __construct() {
            // Hook to filter post content and append related posts.
            add_filter( 'the_content', array( $this, 'the_content_callback' ) );

            // Hook to enqueue styles for the plugin.
            add_action( 'wp_enqueue_scripts', array( $this, 'warp_load_media' ) );

            // Hook to add theme-related settings (like image sizes).
            add_action( 'after_setup_theme', array( $this, 'warp_theme_setup' ) );

            // Define plugin-specific constants.
            $this->define_constants();

            // Load necessary classes or dependencies.
            $this->load_classes();

            // Add inline styles for the plugin.
            add_action( 'wp_enqueue_scripts', array( $this, 'warp_add_inline_styles' ) );
        }

        // Public static method to retrieve the singleton instance.
        public static function get_instances() {
            if ( self::$instance ) {
                return self::$instance;
            }

            self::$instance = new self();

            return self::$instance;
        }

        // Define constants used throughout the plugin.
        private function define_constants() {
            define( 'WARP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
        }

        // Load additional classes or files for the plugin.
        private function load_classes() {
            require_once WARP_PLUGIN_PATH . 'includes/warp-class.settings-api.php';
            require_once WARP_PLUGIN_PATH . 'includes/warp_options.php';
        }

        // Add theme-related setup, such as registering custom image sizes.
        public function warp_theme_setup() {
            add_image_size( 'warp-thumb-md', 340, 230, true );
        }

        // Enqueue styles for the plugin.
        public function warp_load_media() {
            wp_enqueue_style( 'warp-style', plugins_url( 'assets/css/warp-style.css', __FILE__ ), '', '1.0.0' );
        }

        // Method to add inline styles.
        public function warp_add_inline_styles( $data ) {
            $custom_css = $this->warp_init_custom_style(); // Retrieve custom CSS.
            wp_add_inline_style( 'warp-style', $custom_css ); // Add inline styles.
        }

        // Method to generate custom CSS as a string.
        private function warp_init_custom_style() {
            $warp_columns = warp_get_option( 'warp_columns', 'warp_general', 'five' );
            $custom_css = "
                .warp-related {
                    grid-template-columns: repeat(" . $warp_columns . ", 1fr);
                }
            ";
            return $custom_css;
        }

        // Callback function to display related posts after the content.
        public function the_content_callback( $content ) {
            global $post;

            $warp_enable = warp_get_option( 'warp_enable', 'warp_general', 'on' );
            $warp_heading = warp_get_option( 'warp_heading', 'warp_general', 'Related Posts' );
            $warp_postno = warp_get_option( 'warp_postno', 'warp_general', 5 );
            $warp_order = warp_get_option( 'warp_order', 'warp_general', 'DESC' );
            $warp_orderby = warp_get_option( 'warp_orderby', 'warp_general', 'rand' );
            $show_thumb = warp_get_option( 'show_thumb', 'warp_general', 'on' );
            $show_date = warp_get_option( 'show_date', 'warp_general', 'on' );
            $show_title = warp_get_option( 'show_title', 'warp_general', 'on' );
            $short_desc = warp_get_option( 'short_desc', 'warp_general', 'on' );
            $desc_length = warp_get_option( 'desc_length', 'warp_general', 10 );
            $show_author = warp_get_option( 'show_author', 'warp_general', 'on' );

            // Ensure it's a single post view.
            if ( ! is_singular( 'post' ) || $warp_enable === 'off' ) {
                return $content;
            }

            // Retrieve the category of the current post.
            $categories = get_the_terms( $post->ID, 'category' );
            $cats = array();

            // Check if categories exist and are valid.
            if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
                foreach( $categories as $category ) {
                    $cats[] = $category->term_id;
                }
            }

            // Query arguments to fetch related posts.
            $args = array(
                'post_type'         => 'post',
                'posts_per_page'    => $warp_postno,    // Display a maximum of 5 related posts.
                'category__in'      => $cats,
                'post_status'       => 'publish',
                'order'             => $warp_order,
                'orderby'           => $warp_orderby,          // Shuffle the list of related posts.
            );

            // Query to fetch related posts.
            $related_query = new WP_Query( $args );

            // Start capturing the output.
            ob_start();

            // Check if there are any related posts.
            if ( $related_query->have_posts() ) { ?>

                <h3 class="warp-heading"><?php esc_html_e( $warp_heading, 'rwc-related-posts' ); ?></h3>

                <ul class="warp-related"><?php

                    while ( $related_query->have_posts() ) {
                        $related_query->the_post(); ?>

                        <li class="warp-related-lists">
                            <?php if ( $show_thumb === 'on' ) { ?>
                                <a class="warp-thumbnail" href="<?php echo esc_url( get_the_permalink() ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>"><?php
                                    if ( has_post_thumbnail() ) {
                                        echo wp_kses_post( get_the_post_thumbnail( get_the_ID(), 'warp-thumb-md' ) );
                                    } ?>
                                </a>
                            <?php } ?>

                            <?php if ( $show_date == 'on' || $show_title == 'on' || $short_desc == 'on' ) : ?>
                            <div class="warp-content-wrapper">
                                <?php if ( $show_date === 'on' ) { ?>
                                    <span class="warp-date"><?php echo esc_html( wp_date( 'd F, Y', strtotime( $post->post_date ) ) ); ?></span>
                                <?php } ?>

                                <?php if ( $show_title === 'on' ) { ?>
                                    <div class="warp-title">
                                        <a href="<?php echo esc_url( get_the_permalink() ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>">
                                            <?php echo esc_html( get_the_title() ); ?>
                                        </a>
                                    </div>
                                <?php } ?>

                                <?php if ( $short_desc === 'on' ) { ?>
                                    <div class="warp-content">
                                        <?php echo esc_html( wp_trim_words( get_the_content(), $desc_length, '...' ) ); ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php endif; ?>

                            <?php if ( $show_author === 'on' ) { ?>
                                <div class="warp-author-wrapper">
                                    <span class="warp-author-image"><?php echo wp_kses_post( get_avatar( get_the_author_meta( 'ID' ), 24 ) ); ?></span>
                                    <span class="warp-author-name"><?php echo esc_html( sprintf( __( 'by %s', 'rwc-related-posts' ), ucfirst( get_the_author() ) ) ); ?></span>
                                </div>
                            <?php } ?>
                        </li><?php
                    } ?>

                </ul><?php

            } else {
                // Fallback message if no related posts are found.
                esc_html_e( 'Sorry, no posts matched your criteria.', 'rwc-related-posts' );
            }

            // Restore original post data to avoid conflicts.
            wp_reset_postdata();

            // Append the captured output to the original content.
            $content .= ob_get_clean();

            return $content;
        }
    }

    // Instantiate the plugin class.
    RWC_Related_Posts::get_instances();
}