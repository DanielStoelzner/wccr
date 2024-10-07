<?php
/*
Plugin Name: WCCR
Plugin URI:
Description:
Version: 1.0.0
Author: Daniel Stoelzner
Author URI:
License:
Text Domain: wccr
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCCR {

	public $settings;
	public $plugin_path;


	public function __construct() {
		$this->includes();

		// Hook to add a custom tab in the WooCommerce product data panel
		add_filter( 'woocommerce_product_data_tabs', array( $this,'wccr_add_custom_product_tab') );

		// Hook to add custom tab content panel
		add_action( 'woocommerce_product_data_panels', array( $this,'wccr_add_custom_product_tab_content') );

		// Save the selected countries when the product is saved
		add_action( 'woocommerce_process_product_meta', array( $this,'wccr_save_custom_product_data') );
	}


	/**
	 * Include plugin file.
	 *
	 * @since 1.0.0
	 *
	 */	
	public function includes() {
		require_once $this->get_plugin_path() . '/include/admin.php';
		$this->settings = WCCR_Admin::get_instance();
	}

	public function get_plugin_path() {
		if ( isset( $this->plugin_path ) ) {
			return $this->plugin_path;
		}

		$this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );

		return $this->plugin_path;
	}





















	/**
	 * Adds a new custom tab to the WooCommerce product data panel.
	 */
	public function wccr_add_custom_product_tab( $tabs ) {
		$tabs['wccr_tab'] = array(
			'label'    => __( 'WCCR Settings', 'wccr' ),
			'target'   => 'wccr_product_data',
			'class'    => array( 'show_if_simple', 'show_if_variable' ),
			'priority' => 21, // Position the tab after the Inventory tab
		);

		return $tabs;
	}

	/**
	 * Adds content for the custom WCCR product data tab, allowing selection of multiple countries.
	 */
	public function wccr_add_custom_product_tab_content() {
		global $post;

		// Get the WooCommerce countries
		$countries = WC()->countries->get_countries();

		// Get the selected countries for the current product
		$selected_countries = get_post_meta( $post->ID, '_wccr_country_restrictions', true );
		if ( ! is_array( $selected_countries ) ) {
			$selected_countries = array(); // Ensure it's an array
		}
		?>
		<div id="wccr_product_data" class="panel woocommerce_options_panel hidden">
			<div class="options_group">
				<p><?php _e( 'Select the countries where this product cannot be shipped.', 'wccr' ); ?></p>
				<?php
				// Add a multiselect field for countries
				woocommerce_wp_select( array(
					'id'            => '_wccr_country_restrictions',
					'label'         => __( 'Country Restrictions', 'wccr' ),
					'description'   => __( 'Choose one or more countries.', 'wccr' ),
					'desc_tip'      => true,
					'options'       => $countries,
					'custom_attributes' => array( 'multiple' => 'multiple' ), // Allow multiple selection
					'value'         => $selected_countries,
				) );
				?>
			</div>
		</div>
		<?php
	}


	/**
	 * Save the selected country restrictions when the product is saved.
	 */
	public function wccr_save_custom_product_data( $post_id ) {
		if ( isset( $_POST['_wccr_country_restrictions'] ) ) {
			$countries = array_map( 'sanitize_text_field', $_POST['_wccr_country_restrictions'] );
			update_post_meta( $post_id, '_wccr_country_restrictions', $countries );
		} else {
			delete_post_meta( $post_id, '_wccr_country_restrictions' );
		}
	}












	//TODO: rewrite
	/**
	 * Check if WC is active
	 *
	 * @since  1.0.0
	 * @return bool
	*/
	private function is_wc_active() {
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$is_active = true;
		} else {
			$is_active = false;
		}

		// Do the WC active check
		if ( false === $is_active ) {
			add_action( 'admin_notices', array( $this, 'notice_activate_wc' ) );
		}		
		return $is_active;
	}

	/**
	 * Display WC active notice
	 *
	 * @since  1.0.0
	*/
	public function notice_activate_wc() {
		?>
		<div class="error">
			<p><?php printf( esc_html( 'Please install and activate %1$sWooCommerce%2$s for Country Based Restrictions for WooCommerce!', 'woo-product-country-base-restrictions' ), '<a href="' . esc_url(admin_url( 'plugin-install.php?tab=search&s=WooCommerce&plugin-search-input=Search+Plugins' ) . '">', '</a>' ) ); ?></p>
		</div>
		<?php
	}


	/**
	 * WOOCOMMERCE_VERSION admin notice
	 *
	 * @since 1.0.0
	 */
	public function admin_error_notice() {
		$message = __('Product Country Restrictions requires WooCommerce 3.0 or newer', 'woo-product-country-base-restrictions');
		echo esc_html("<div class='error'><p>$message</p></div>");
	}
	//TODO: rewrite end
}

$wccr = new WCCR();