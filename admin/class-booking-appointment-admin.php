<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://stridedge.com
 * @since      1.0.0
 *
 * @package    Booking_Appointment
 * @subpackage Booking_Appointment/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Booking_Appointment
 * @subpackage Booking_Appointment/admin
 * @author     StridEdge Technology Company <irfan@stridedge.com>
 */
class Booking_Appointment_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Booking_Appointment_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Booking_Appointment_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/booking-appointment-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Booking_Appointment_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Booking_Appointment_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/booking-appointment-admin.js', array( 'jquery' ), $this->version, false );

	}
	
	public function menu() {
		global $booking_appointment_holidays_menu;
		global $booking_appointment_bookings_menu;
		$menu_slug = 'bookings-appointments';
		add_menu_page( 'Bookings/Appointments', 'Bookings/Appointments', 'manage_options', $menu_slug, false );
		add_submenu_page( $menu_slug, 'Settings', 'Settings', 'manage_options', $menu_slug, array($this,'settings') );
		add_submenu_page( $menu_slug, 'Configure', 'Configure', 'manage_options', $menu_slug.'-configure', array($this,'configure') );
		$booking_appointment_holidays_menu = add_submenu_page( $menu_slug, 'Holidays', 'Holidays', 'manage_options', $menu_slug.'-holidays', array($this,'holidays') );
		add_action("load-$booking_appointment_holidays_menu", array($this,"holidays_menu_screen_options") );
		
		$booking_appointment_bookings_menu = add_submenu_page( $menu_slug, 'Bookings', 'Bookings', 'manage_options', $menu_slug.'-bookings', array($this,'bookings') );
		add_action("load-$booking_appointment_bookings_menu", array($this,"booking_menu_screen_options") );
	}
	public function holidays_menu_screen_options() {
		global $booking_appointment_holidays_menu;
		global $booking_appointment_table;
	 
		$screen = get_current_screen();
	 
		// get out of here if we are not on our settings page
		if(!is_object($screen) || $screen->id != $booking_appointment_holidays_menu)
			return;
	 
		$args = array(
			'label' => __('Records per page', 'booking-appointment'),
			'default' => 20,
			'option' => 'elements_per_page'
		);
		add_screen_option( 'per_page', $args );
		require_once plugin_dir_path( __FILE__ ) . 'partials/booking-appointment-admin-holidays.php';
		$booking_appointment_table = new Booking_Appointment_Holidays();
	}
	public function booking_menu_screen_options() {
		global $booking_appointment_bookings_menu;
		global $booking_appointment_table;
	 
		$screen = get_current_screen();
	 
		// get out of here if we are not on our settings page
		if(!is_object($screen) || $screen->id != $booking_appointment_bookings_menu)
			return;
	 
		$args = array(
			'label' => __('Records per page', 'booking-appointment'),
			'default' => 20,
			'option' => 'elements_per_page'
		);
		add_screen_option( 'per_page', $args );
		require_once plugin_dir_path( __FILE__ ) . 'partials/booking-appointment-admin-bookings.php';
		$booking_appointment_table = new Booking_Appointment_Bookings();
	}
	public function settings(){
		require_once plugin_dir_path( __FILE__ ) . 'partials/booking-appointment-admin-display.php';
	}
	public function configure(){
		require_once plugin_dir_path( __FILE__ ) . 'partials/booking-appointment-admin-configure.php';
	}
	public function screen_option($status, $option, $value){
		return $value;
	}
	public function holidays(){
		
		if(isset($_POST['submit']) && $_POST['submit'] == 'Save Changes'){
			global $wpdb;
			
			if ( !isset( $_POST['nonce_holiday'] ) || !wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce_holiday'])), 'holiday_nonce' ) ) {
				wp_send_json_error( 'Nonce verification failed' );
			}
			$holiday_name = isset($_POST['holiday_name']) ? sanitize_text_field($_POST['holiday_name']) : '';
			$holiday_description = isset($_POST['holiday_description']) ? sanitize_textarea_field($_POST['holiday_description']) : '';
			$holiday_date = isset($_POST['holiday_date']) ? sanitize_text_field($_POST['holiday_date']) : '';
			
			$wpdb->query($wpdb->prepare(
				"INSERT INTO `{$wpdb->prefix}ba_holidays` (title, description, date) VALUES (%s, %s, %s)",
				$holiday_name,
				$holiday_description,
				$holiday_date
			));
			
			//$wpdb->query("INSERT INTO `".$wpdb->prefix."ba_holidays` (title,description,date) VALUES ('".$_POST['holiday_name']."', '".$_POST['holiday_description']."', '".$_POST['holiday_date']."')");
		}
		
		if(isset($_GET['add']) && $_GET['add'] == '1'){
			require_once plugin_dir_path( __FILE__ ) . 'partials/booking-appointment-admin-holidays-add.php';
		}else{
			require_once plugin_dir_path( __FILE__ ) . 'partials/booking-appointment-admin-holidays.php';
			$table = new Booking_Appointment_Holidays();
			echo '<div class="wrap">';
			echo '<h2>Holidays <a href="?page=bookings-appointments-holidays&add=1" id="doc_popup" class="add-new-h2">Add New Holiday</a></h2>';
			echo '<form method="post">';
				$table->prepare_items();
				$table->search_box('search', 'search_id');
				$table->display();
				wp_nonce_field('bulk_action_nonce', '_wpnonce_bulk_action');
			echo '</form></div>';
		}
	}
	public function bookings(){
		
		/*if(isset($_POST['submit']) && $_POST['submit'] == 'Save Changes'){
			global $wpdb;
			$wpdb->query("INSERT INTO `".$wpdb->prefix."ba_holidays` (title,description,date) VALUES ('".$_POST['holiday_name']."', '".$_POST['holiday_description']."', '".$_POST['holiday_date']."')");
		}*/
		
		/*if(isset($_GET['add']) && $_GET['add'] == '1'){
			require_once plugin_dir_path( __FILE__ ) . 'partials/booking-appointment-admin-holidays-add.php';
		}else{*/
			require_once plugin_dir_path( __FILE__ ) . 'partials/booking-appointment-admin-bookings.php';
			$table = new Booking_Appointment_Bookings();
			echo '<div class="wrap">';
			echo '<h2>Bookings</h2>';
			echo '<form method="post">';
				$table->prepare_items();
				$table->search_box('search', 'search_id');
				$table->display();
				wp_nonce_field('bulk_action_nonce', '_wpnonce_bulk_action');
			echo '</form></div>';
		// }
	}
	public function save_email_settings() {
		// Check nonce for security
		if (!isset($_POST['email_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['email_nonce'])), 'verify_email_nonce')) {
			wp_send_json_error('Nonce verification failed');
		}

		// Sanitize input fields
		$option = array(
			'from_email'     => sanitize_email(isset($_POST['from_email']) ? $_POST['from_email'] : ''),
			'from_name'      => sanitize_text_field(isset($_POST['from_name']) ? $_POST['from_name'] : ''),
			'replyto_email'  => sanitize_email(isset($_POST['replyto_email']) ? $_POST['replyto_email'] : ''),
			'replyto_name'   => sanitize_text_field(isset($_POST['replyto_name']) ? $_POST['replyto_name'] : ''),
			'email_subject'  => sanitize_text_field(isset($_POST['email_subject']) ? $_POST['email_subject'] : ''),
			'email_body'     => isset($_POST['email_body']) ? wp_kses_post($_POST['email_body']) : ''
		);

		// Update options and check for success
		$updated = update_option('booking_appointment_email_data', $option);
		
		if ($updated) {
			wp_send_json_success('Settings saved successfully');
		} else {
			wp_send_json_error('Failed to save settings');
		}
	}

	public function save_settings(){
		if ( !isset( $_POST['business_nonce'] ) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['business_nonce'])), 'verify_business_nonce' ) ) {
			wp_send_json_error( 'Nonce verification failed' );
		}
		$option['business_name'] = sanitize_text_field($_POST['business_name']);
		$option['business_address'] = sanitize_text_field($_POST['business_address']);
		echo esc_html(update_option( 'booking_appointment_settings_data', $option));
		exit;
	}
	public function save_configuration() {
		if ( ! isset( $_POST['configure_nonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['configure_nonce'])), 'verify_configure_nonce' ) ) {
			wp_send_json_error( 'Nonce verification failed' );
		}

		unset($_POST['action']);
		
		$ba_configuration = array(
			'is_enabled'              => isset($_POST['is_enabled']) ? sanitize_text_field(wp_unslash($_POST['is_enabled'])) : '',
			'timezone'                => isset($_POST['timezone']) ? sanitize_text_field(wp_unslash($_POST['timezone'])) : '',
			'duration'                => isset($_POST['duration']) ? intval($_POST['duration']) : 0,
			'hours'                   => isset($_POST['hours']) ? intval($_POST['hours']) : 0,
			'minutes'                 => isset($_POST['minutes']) ? intval($_POST['minutes']) : 0,
			'working_hours'           => isset($_POST['working_hours']) ? sanitize_text_field(wp_unslash($_POST['working_hours'])) : '',
			'working_hours_start'     => isset($_POST['working_hours_start']) ? sanitize_text_field(wp_unslash($_POST['working_hours_start'])) : '',
			'working_hours_end'       => isset($_POST['working_hours_end']) ? sanitize_text_field(wp_unslash($_POST['working_hours_end'])) : '',
			'break'                   => isset($_POST['break']) ? sanitize_text_field(wp_unslash($_POST['break'])) : '',
			'break_start'             => isset($_POST['break_start']) ? sanitize_text_field(wp_unslash($_POST['break_start'])) : '',
			'break_end'               => isset($_POST['break_end']) ? sanitize_text_field(wp_unslash($_POST['break_end'])) : '',
			'working_days'            => isset($_POST['working_days']) ? array_map('sanitize_text_field', $_POST['working_days']) : array(),
		);

		// Optionally, ensure `working_days` is an array if it's not already
		if ( ! is_array( $ba_configuration['working_days'] ) ) {
			$ba_configuration['working_days'] = array();
		}

		$sanitized_options = $this->sanitize_array( $ba_configuration );
		update_option( 'booking_appointment_configuration', $sanitized_options );
		echo esc_html__( 'Configuration updated.', 'booking-appointment' );
		exit;
	}

	public function save_payment_settings() {
		if ( ! isset( $_POST['payment_nonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['payment_nonce'])), 'verify_payment_nonce' ) ) {
			wp_send_json_error( 'Nonce verification failed' );
		}

		unset( $_POST['action'] );

		$payments_data = array(
			'offline_message' => isset($_POST['offline_message']) ? sanitize_text_field(wp_unslash($_POST['offline_message'])) : '',
		);

		$sanitized_options = $this->sanitize_array( $payments_data );
		update_option( 'booking_appointment_payments_data', $sanitized_options );
		echo esc_html__( 'Payments data updated.', 'booking-appointment' );
		exit;
	}
	public function display(){
		return "<div id='calendar'></div><div id='demo-modal' class='custombox-modal' style='display: none;'>";
		// return "here";
		// return $this->generate_calendar(date('Y', $time), date('n', $time), $days, 1, null, 0);
	}
	public function sanitize_array($array) {
		foreach ($array as $key => &$value) {
			if (is_array($value)) {
				// Recursively sanitize arrays
				$value = $this->sanitize_array($value);
			} else {
				// Handle different types of data
				if (is_string($value)) {
					// Sanitize text fields
					$value = sanitize_text_field($value);
				} elseif (is_email($value)) {
					// Sanitize email addresses
					$value = sanitize_email($value);
				} elseif (is_numeric($value)) {
					// Sanitize numeric values
					$value = intval($value);
				} elseif (filter_var($value, FILTER_VALIDATE_URL)) {
					// Sanitize URLs
					$value = esc_url_raw($value);
				} else {
					// Default sanitization for any other types
					$value = sanitize_text_field($value);
				}
			}
		}

		return $array;
	}
}
