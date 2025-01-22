<?php
/*
 *  RWC Related Posts v1.0.0 - 22 December, 2024
 *  By @realwebcare - https://www.realwebcare.com/
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('WARP_Settings_Config' ) ):
	class WARP_Settings_Config {

		private $settings_api;

		function __construct() {
			$this->settings_api = new WARP_WeDevs_Settings_API;
			add_action( 'admin_init', array($this, 'admin_init') );
			add_action( 'admin_menu', array($this, 'admin_menu') );
		}

		function admin_init() {
			//set the settings
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );
			//initialize settings
			$this->settings_api->admin_init();
		}

		function admin_menu() {
            add_menu_page(
                'WARP Related Post',
                'Related Posts',
                'administrator',
                'warp-related-post',
                array( $this, 'warp_related_post' ),
                'dashicons-list-view',
                26
            );
		}

		// setings tabs
		function get_settings_sections() {
			$sections = array(
				array(
				'id' => 'warp_general',
				'title' => __( 'General Settings', 'rwc-related-posts' )
				)
			);
			return $sections;
		}

		/**
		* Returns all the settings fields
		*
		* @return array settings fields
		*/
		function get_settings_fields() {
			$settings_fields = array( 
				'warp_general' => array(
					array(
						'name'				=> 'warp_enable',
						'label'				=> __( 'Enable Related Posts', 'rwc-related-posts' ),
						'desc'				=> __( 'Mark if you want to show related posts below each post.', 'rwc-related-posts' ),
						'type'				=> 'checkbox',
						'default'			=> 'on'
					),
					array(
						'name'              => 'warp_heading',
						'label'             => __( 'Enter Related Posts Heading', 'rwc-related-posts' ),
						'desc'              => __( 'Enter a heading for the Related Posts.', 'rwc-related-posts' ),
						'placeholder'       => __( 'Related Posts', 'rwc-related-posts' ),
						'type'              => 'text',
						'default'           => 'Related Posts',
						'sanitize_callback' => 'sanitize_text_field'
					),
					array(
						'name'				=> 'warp_columns',
						'label'				=> __( 'Number of Posts per Row', 'rwc-related-posts' ),
						'desc'				=> __( 'Divide the posts by row by selecting the number of columns from the dropdown list.', 'rwc-related-posts' ),
						'type'				=> 'select',
						'default'			=> 'five',
						'options'			=> array(
							'5'             => 'Five Columns',
							'4'             => 'Four Columns',
							'3'             => 'Three Columns',
							'2'             => 'Two Columns',
						)
					),
					array(
						'name'      		=> 'warp_postno',
						'label'     		=> __( 'Number of Posts', 'rwc-related-posts' ),
						'desc'      		=> __( 'Number of post to show. -1, means show all.', 'rwc-related-posts' ),
						'placeholder'       => __( '5', 'rwc-related-posts' ),
						'min'               => -1,
						'max'               => 5,
						'type'     		 	=> 'number',
						'default'  			=> 5
					),
					array(
						'name'      		=> 'warp_order',
						'label'     		=> __( 'Select Post Order', 'rwc-related-posts' ),
						'desc'      		=> __( 'Define the order in which posts are displayed.', 'rwc-related-posts' ),
						'type'      		=> 'select',
						'default'   		=> 'DESC',
						'options'   		=> array(
							'ASC'     	    => __( 'Ascending', 'rwc-related-posts' ),
							'DESC'    	    => __( 'Descending', 'rwc-related-posts' )
						),
					),
					array(
						'name'      		=> 'warp_orderby',
						'label'     		=> __( 'Select Post Order By', 'rwc-related-posts' ),
						'desc'      		=> __( 'Choose the parameter by which posts should be ordered.', 'rwc-related-posts' ),
						'type'      		=> 'select',
						'default'   		=> 'rand',
						'options'   		=> array(
							'ID'     	    => __( 'Post ID', 'rwc-related-posts' ),
							'name'    	    => __( 'Post Name (post slug)', 'rwc-related-posts' ),
							'date'    	    => __( 'Post Date', 'rwc-related-posts' ),
							'rand'    	    => __( 'Random', 'rwc-related-posts' ),
						),
					),
                    array(
						'name'				=> 'show_thumb',
						'label'				=> __( 'Show Thumbnail', 'rwc-related-posts' ),
						'desc'				=> __( 'Select if you want to show the featured image thumbnail in related post.', 'rwc-related-posts' ),
						'type'				=> 'checkbox',
						'default'			=> 'on'
					),
                    array(
						'name'				=> 'show_date',
						'label'				=> __( 'Show Date', 'rwc-related-posts' ),
						'desc'				=> __( 'Select if you want to show the date in related post.', 'rwc-related-posts' ),
						'type'				=> 'checkbox',
						'default'			=> 'on'
					),
					array(
						'name'				=> 'show_title',
						'label'				=> __( 'Show Title', 'rwc-related-posts' ),
						'desc'				=> __( 'Select if you want to show the title in related post.', 'rwc-related-posts' ),
						'type'				=> 'checkbox',
						'default'			=> 'on'
					),
					array(
						'name'				=> 'short_desc',
						'label'				=> __( 'Short Description', 'rwc-related-posts' ),
						'desc'				=> __( 'Select if you want to show the post short description in related post.', 'rwc-related-posts' ),
						'type'				=> 'checkbox',
						'default'			=> 'on'
					),
					array(
						'name'              => 'desc_length',
						'label'             => __( 'Description Length', 'rwc-related-posts' ),
						'desc'              => __( 'Set how many words to show for the description.', 'rwc-related-posts' ),
						'placeholder'       => __( '10', 'rwc-related-posts' ),
						'min'               => 3,
						'max'               => 50,
						'type'              => 'number',
						'default'           => '10'
					),
					array(
						'name'				=> 'show_author',
						'label'				=> __( 'Show Author Name', 'rwc-related-posts' ),
						'desc'				=> __( 'Select if you want to show the author name in related post.', 'rwc-related-posts' ),
						'type'				=> 'checkbox',
						'default'			=> 'on'
					),
				),
			);
			return $settings_fields;
		}

		// wraping the settings
		function warp_related_post() { ?>
			<div class="warp_settings_area">
				<div class="wrap warp_settings"><?php
					$this->settings_api->show_navigation();
					$this->settings_api->show_forms(); ?>
				</div>
			</div>
			<?php
		}

		/**
		* Get all the pages
		*
		* @return array page names with key value pairs
		*/
		function get_pages() {
			$pages = get_pages();
			$pages_options = array();
			if ( $pages ) {
				foreach ( $pages as $page ) {
					$pages_options[$page->ID] = $page->post_title;
				}
			}
			return $pages_options;
		}
	}
endif;

$warp_settings_config = new WARP_Settings_Config();

//--------- trigger setting api class---------------- //
if ( !function_exists( 'warp_get_option' ) ) {
	function warp_get_option( $option, $section, $default = '' ) {
		$options = get_option( $section );
		if ( isset( $options[$option] ) ) {
			return $options[$option];
		}
		return $default;
	}
}

//--------- get categories for news ticker---------------- //
if ( !function_exists( 'get_warp_categories' ) ) {
	function get_warp_categories() {
		$ticker_categories = get_categories();
		$categories = array("Select a category");

		foreach ( $ticker_categories as $category ) {
			$categories[$category->cat_ID] = $category->name;
		}

		return $categories;
	}
}