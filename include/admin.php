<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCCR_Admin {

    public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    private static $instance;

	public function __construct() {
		$this->init();
	}

    public function init() {
        // Hook to add the WCCR admin page to WooCommerce
		add_action( 'admin_menu', array( $this,'wccr_add_admin_page_to_woocommerce') );
		
		// Hook to register settings
		add_action( 'admin_init', array( $this,'wccr_register_settings') );
    }

    /**
	 * Add a WCCR admin page under the WooCommerce menu.
	 */
	public function wccr_add_admin_page_to_woocommerce() {
		// Add submenu page under WooCommerce
		add_submenu_page(
			'woocommerce',                   // Parent slug (WooCommerce menu)
			__( 'WCCR Settings', 'wccr' ),   // Page title
			__( 'WCCR Settings', 'wccr' ),   // Menu title
			'manage_options',                // Capability required
			'wccr-settings',                 // Menu slug
			array( $this, 'wccr_settings_page_callback' )    // Callback function to render the page
		);
	}

	/**
	 * Callback function for displaying the WCCR admin settings page.
	 */
	public function wccr_settings_page_callback() {
		?>
		<div class="wrap">
			<h1><?php _e( 'WCCR Settings', 'wccr' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				// Output security fields for the registered setting
				settings_fields( 'wccr_settings_group' );
				// Output setting sections and their fields
				do_settings_sections( 'wccr-settings' );
				// Output save settings button
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Register the settings for the WCCR page.
	 */
	public function wccr_register_settings() {
		// Register a new setting for "wccr-settings" page
		register_setting( 'wccr_settings_group', 'wccr_settings_option' );

		// Add a new section in the WCCR settings page
		add_settings_section(
			'wccr_settings_section',          // ID
			__( 'WCCR Settings Section', 'wccr' ), // Title
			array( $this, 'wccr_settings_section_callback' ), // Callback
			'wccr-settings'                   // Page
		);

		// Add a setting field
		add_settings_field(
			'wccr_text_field',                        // ID
			__( 'Text Field', 'wccr' ),               // Title
			array( $this, 'wccr_text_field_callback' ),               // Callback
			'wccr-settings',                          // Page
			'wccr_settings_section',                  // Section
			array( 'label_for' => 'wccr_text_field' ) // Args
		);
	}

	/**
	 * Callback function for the WCCR settings section.
	 */
	public function wccr_settings_section_callback() {
		echo '<p>' . __( 'Adjust the WCCR plugin settings below.', 'wccr' ) . '</p>';
	}

	/**
	 * Callback function for the text field.
	 */
	public function wccr_text_field_callback( $args ) {
		// Get the value of the setting we've registered with register_setting()
		$option = get_option( 'wccr_settings_option' );
		?>
		<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="wccr_settings_option" value="<?php echo isset( $option ) ? esc_attr( $option ) : ''; ?>" />
		<?php
	}
}