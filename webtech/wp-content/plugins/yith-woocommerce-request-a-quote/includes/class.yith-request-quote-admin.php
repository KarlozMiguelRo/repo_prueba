<?php
if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAQ_VERSION' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Implements features of FREE version of YITH Woocommerce Request A Quote
 *
 * @class   YITH_YWRAQ_Admin
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  YITH
 */
if ( ! class_exists( 'YITH_YWRAQ_Admin' ) ) {

	/**
	 * Class YITH_YWRAQ_Admin
	 */
	class YITH_YWRAQ_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var \YWRAQ
		 */

		protected static $instance;

		/**
		 * @var $_panel Panel Object
		 */
		protected $_panel;

		/**
		 * @var $_premium string Premium tab template file name
		 */
		protected $_premium = 'premium.php';

		/**
		 * @var string Premium version landing link
		 */
		protected $_premium_landing = 'https://yithemes.com/themes/plugins/yith-woocommerce-request-a-quote/';

		/**
		 * @var string Official plugin landing page
		 */
		protected $_premium_live = 'https://plugins.yithemes.com/yith-woocommerce-request-a-quote/';

		/**
		 * @var string Panel page
		 */
		protected $_panel_page = 'yith_woocommerce_request_a_quote';

		/**
		 * @var string List of messages
		 */
		protected $messages = array();

		/**
		 * @var string Doc Url
		 */
		public $_official_documentation = 'https://docs.yithemes.com/yith-woocommerce-request-a-quote/';

		/**
		 * @var string Official plugin support page
		 */
		protected $_support = 'https://wordpress.org/support/plugin/yith-woocommerce-request-a-quote';

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_YWRAQ_Admin
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 * @author Emanuela Castorina
		 */
		public function __construct() {

			$this->create_menu_items();

			// Add action links
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_YWRAQ_DIR . '/' . basename( YITH_YWRAQ_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );
			add_action( 'init', array( $this, 'add_page' ) );

			// custom styles and javascripts
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );
		}

		/**
		 * Add the widget of "Important Links" inside the admin sidebar
		 *
		 * @return array
		 */
		public function get_panel_sidebar_link() {
			return array(
				array(
					'url'   => $this->_official_documentation,
					'title' => __( 'Plugin Documentation', 'yith-woocommerce-request-a-quote' ),
				),
				array(
					'url'   => $this->_premium_landing,
					'title' => __( 'Discovery premium version', 'yith-woocommerce-request-a-quote' ),
				),
				array(
					'url'   => $this->_premium_landing . '#tab-free_vs_premium_tab',
					'title' => __( 'Free Vs Premium', 'yith-woocommerce-request-a-quote' ),
				),
				array(
					'url'   => 'https://plugins.yithemes.com/yith-woocommerce-request-a-quote/',
					'title' => __( 'Premium live demo', 'yith-woocommerce-request-a-quote' ),
				),
				array(
					'url'   => 'https://wordpress.org/support/plugin/yith-woocommerce-request-a-quote',
					'title' => __( 'WordPress support forum', 'yith-woocommerce-request-a-quote' ),
				),
				array(
					'url'   => $this->_official_documentation . '/05-changelog.html',
					/* translators: placeholder plugin version */
					'title' => sprintf( __( 'Changelog (current version %s)', 'yith-woocommerce-request-a-quote' ), YITH_YWRAQ_VERSION ),
				),
			);
		}

		/**
		 * Enqueue styles and scripts
		 *
		 * @access public
		 * @return void
		 * @since 1.0.0
		 */
		public function enqueue_styles_scripts() {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script( 'yith_ywraq_admin', YITH_YWRAQ_ASSETS_URL . '/js/yith-ywraq-admin' . $suffix . '.js', array( 'jquery' ), '1.0.0', true );

			if ( defined( 'YITH_YWRAQ_PREMIUM' ) ) {
				wp_enqueue_style( 'yith_ywraq_backend', YITH_YWRAQ_ASSETS_URL . '/css/backend.css', array(), YITH_YWRAQ_VERSION );
			}
		}


		/**
		 * Create Menu Items
		 *
		 * Print admin menu items
		 *
		 * @since  1.0
		 * @author Emanuela Castorina
		 */
		private function create_menu_items() {

			// Add a panel under YITH Plugins tab
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
			add_action( 'yith_ywraq_premium_tab', array( $this, 'premium_tab' ) );
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use      /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->_panel ) ) {
				return;
			}

			$admin_tabs = array(
				'settings' => __( 'Settings', 'yith-woocommerce-request-a-quote' ),
			);

			if ( defined( 'YITH_YWRAQ_FREE_INIT' ) ) {
				$admin_tabs['premium'] = __( 'Premium Version', 'yith-woocommerce-request-a-quote' );
			} else {
				$admin_tabs['layout']     = __( 'Layout', 'yith-woocommerce-request-a-quote' );
				$admin_tabs['exclusions'] = __( 'Exclusion List', 'yith-woocommerce-request-a-quote' );
			}

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'plugin_slug'      => YITH_YWRAQ_SLUG,
				'page_title'       => _x( 'YITH WooCommerce Request a Quote', 'Plugin Name. Do not translate.', 'yith-woocommerce-request-a-quote' ),
				'menu_title'       => _x( 'Request a Quote', 'Plugin Name. Do not translate.', 'yith-woocommerce-request-a-quote' ),
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yith_plugin_panel',
				'links'            => $this->get_panel_sidebar_link(),
				'page'             => $this->_panel_page,
				'admin-tabs'       => $admin_tabs,
				'class'            => yith_set_wrapper_class(),
				'options-path'     => YITH_YWRAQ_DIR . '/plugin-options',
			);

			/* === Fixed: not updated theme  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once YITH_YWRAQ_DIR . '/plugin-fw/lib/yit-plugin-panel-wc.php';
			}

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );

			add_action( 'woocommerce_admin_field_ywraq_upload', array( $this->_panel, 'yit_upload' ), 10, 1 );

		}


		/**
		 * Add a page "Request a Quote".
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_page() {
			global $wpdb;

			$option_value = get_option( 'ywraq_page_id' );

			if ( $option_value > 0 && get_post( $option_value ) ) {
				return;
			}

			$page_found = $wpdb->get_var( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_name` = 'request-quote' LIMIT 1;" );
			if ( $page_found ) :
				if ( ! $option_value ) {
					update_option( 'ywraq_page_id', $page_found );
				}
				return;
			endif;

			$page_data = array(
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_author'    => 1,
				'post_name'      => esc_sql( _x( 'request-quote', 'page_slug', 'yit' ) ),
				'post_title'     => __( 'Request a Quote', 'yit' ),
				'post_content'   => '[yith_ywraq_request_quote]',
				'post_parent'    => 0,
				'comment_status' => 'closed',
			);
			$page_id   = wp_insert_post( $page_data );

			update_option( 'ywraq_page_id', $page_id );
		}

		/**
		 * Premium Tab Template
		 *
		 * Load the premium tab template on admin page
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function premium_tab() {
			$premium_tab_template = YITH_YWRAQ_TEMPLATE_PATH . 'admin/' . $this->_premium;
			if ( file_exists( $premium_tab_template ) ) {
				include_once $premium_tab_template;
			}
		}


		/**
		 * Action Links
		 *
		 * add the action links to plugin admin page
		 *
		 * @param $links | links plugin array
		 *
		 * @return   mixed Array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use      plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {
			$links[] = sprintf( '<a href="%s">%s</a>', admin_url( "admin.php?page={$this->_panel_page}" ), _x( 'Settings', 'Action links', 'yith-woocommerce-request-a-quote' ) );

			if ( ! defined( 'YITH_YWRAQ_FREE_INIT' ) && class_exists( 'YIT_Plugin_Licence' ) ) {
				$links[] = sprintf( '<a href="%s">%s</a>', YIT_Plugin_Licence::get_license_activation_url(), __( 'License', 'yith-woocommerce-request-a-quote' ) );
			}

			return $links;
		}



		/**
		 * plugin_row_meta
		 *
		 * add the action links to plugin admin page
		 *
		 * @param $plugin_meta
		 * @param $plugin_file
		 * @param $plugin_data
		 * @param $status
		 *
		 * @return   Array
		 * @since    1.6.5
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use      plugin_row_meta
		 */
		public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_YWRAQ_FREE_INIT' ) {

			$to_add = array();
			if ( defined( $init_file ) && constant( $init_file ) == $plugin_file ) {
				$to_add[] = array(
					'label' => _x( 'Live Demo', 'Plugin Row Meta', 'yith-woocommerce-request-a-quote' ),
					'url'   => $this->_premium_live,
					'icon'  => 'dashicons  dashicons-laptop',
				);

				$to_add[] = array(
					'label' => _x( 'Documentation', 'Plugin Row Meta', 'yith-woocommerce-request-a-quote' ),
					'url'   => $this->_official_documentation,
					'icon'  => 'dashicons  dashicons-search',
				);

				$to_add[] = array(
					'label' => _x( 'Support', 'Plugin Row Meta', 'yith-woocommerce-request-a-quote' ),
					'url'   => $this->_support,
					'icon'  => 'dashicons  dashicons-admin-users',
				);
			}

			if ( defined( 'YITH_YWRAQ_FREE_INIT' ) && YITH_YWRAQ_FREE_INIT == $plugin_file ) {
				$to_add[] = array(
					'label' => _x( 'Premium version', 'Plugin Row Meta', 'yith-woocommerce-request-a-quote' ),
					'url'   => $this->get_premium_landing_uri(),
					'icon'  => 'dashicons  dashicons-cart',
				);
			}

			foreach ( $to_add as $row_meta ) {
				$plugin_meta[] = sprintf( '<a href="%s" target="_blank"><span class="%s"></span>%s</a>', $row_meta['url'], $row_meta['icon'], $row_meta['label'] );
			}

			return $plugin_meta;

		}

		/**
		 * Get the premium landing uri
		 *
		 * @since   1.0.0
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return  string The premium landing link
		 */
		public function get_premium_landing_uri() {
			return $this->_premium_landing;
		}

	}
}

/**
 * Unique access to instance of YITH_YWRAQ_Admin class
 *
 * @return \YITH_YWRAQ_Admin
 */
function YITH_YWRAQ_Admin() {
	return YITH_YWRAQ_Admin::get_instance();
}

YITH_YWRAQ_Admin();
