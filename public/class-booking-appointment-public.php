<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://stridedge.com
 * @since      1.0.0
 *
 * @package    Booking_Appointment
 * @subpackage Booking_Appointment/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Booking_Appointment
 * @subpackage Booking_Appointment/public
 * @author     StridEdge Technology Company <irfan@stridedge.com>
 */
class Booking_Appointment_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/booking-appointment-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'-custombox', plugin_dir_url( __FILE__ ) . 'css/custombox.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/booking-appointment-public.js', array( 'jquery' ), $this->version, false );
		$config = get_option('booking_appointment_configuration');
		if (is_array($config) && isset($config['timezone'])) {
			$timezone = $config['timezone'];
		} else {
			$timezone = 'UTC';
		}
		wp_localize_script( $this->plugin_name, 'booking_appointment', array( 'ajaxurl' => admin_url( 'admin-ajax.php'), 'timezone' => $timezone, 'modal_nonce' => wp_create_nonce( 'get_event_booking_form' ) ));
		wp_enqueue_script( $this->plugin_name.'-index.global', plugin_dir_url( __FILE__ ) . 'js/index.global.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name.'-custombox', plugin_dir_url( __FILE__ ) . 'js/custombox.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name.'-custombox-legacy', plugin_dir_url( __FILE__ ) . 'js/custombox.legacy.min.js', array( 'jquery' ), $this->version, false );

	}
	
	public function events(){
		global $wpdb;		
		$items = array();
		$config = get_option('booking_appointment_configuration');
		if($config['is_enabled'] == '1'){
			$settings = get_option("booking_appointment_settings_data");
			$begin = new DateTime('2024-03-13'.$settings['working_hours_start']);
			$end = new DateTime('2025-03-13'.$settings['working_hours_end']);
			$interval = DateInterval::createFromDateString('1 day');
			$period = new DatePeriod($begin, $interval, $end);
			foreach ($period as $dt) {
				$start = $dt->format("Y-m-d".$settings['working_hours_start']);
				$holiday = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}ba_holidays` WHERE date = %s",$start),ARRAY_A);
				//$holiday = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."ba_holidays` WHERE date='$start'", ARRAY_A);
				if($holiday['id']){
					$day = $dt->format("l");
					if(!in_array($day,$config['working_days'])){
						continue;
					}
					$event = new stdClass();
					$event->title = $holiday['title'];
					$event->start = $start;
					$event->end = $end;
					$event->classNames = [ 'booking', 'event', 'holiday' ];
					$event->backgroundColor = '#FF0000';
					$event->borderColor = '#FF0000';
					$items[] = $event;
				}else{
					$day = $dt->format("l");
					if(!in_array($day,$config['working_days'])){
						continue;
					}
					$event = new stdClass();
					$event->title = $settings['business_name'];
					$event->start = $start;
					$event->end = $end;
					$event->classNames = [ 'booking', 'event' ];
					$items[] = $event;
				}
			}
		}
		echo wp_json_encode($items);
		exit;
	}
	
	public function booking_form(){
		global $wpdb;
		/*if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce($_POST['nonce']) ) {
			wp_send_json_error( 'Nonce verification failed' );
		}*/
		if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])),'get_event_booking_form') ) {
			wp_send_json_error( 'Nonce verification failed' );
		}
		?>
		<div class="title">
			<h3 class="center light"><?php echo wp_kses_data($_POST['title']); ?></h3>
			<p class="center light"><?php echo wp_kses_data($_POST['start']); ?></p>
		</div>
		<div class="body">
			<?php
			$config = get_option('booking_appointment_configuration');
			$duration = $config['duration'];
			if($duration == "other"){
				$duration = $config['hours']." Hour ";
				$duration .= $config['minutes']." Minute";
				
				$slot_minutes = (int)$config['hours'] * 60;
				$slot_minutes = $slot_minutes + (int)$config['minutes'];
			}else{
				$duration = $config['duration']." Minute";
				$slot_minutes = $config['duration'];
			}
			$working_hours = $config['working_hours'];
			$break_minutes = 0;
			if($working_hours == 'other'){
				$working_hours_start = $config['working_hours_start'];
				$working_hours_end = $config['working_hours_end'];
				$checkTime = strtotime($working_hours_start);
				$loginTime = strtotime($working_hours_end);
				$diff = $checkTime - $loginTime;
				$open_minutes = abs($diff) / 60;
				
				$break_start = $config['break_start'];
				$break_end = $config['break_end'];
				$break_startTime = strtotime($break_start);
				$break_endTime = strtotime($break_end);
				$diff_break = $break_startTime - $break_endTime;
				$break_minutes = abs($diff_break) / 60;
				
				// $open_minutes = $open_minutes - $break_minutes;
			}else{
				$working_hours_start = "00:00";
				$working_hours_end = "24:00";
				$checkTime = strtotime($working_hours_start);
				$loginTime = strtotime($working_hours_end);
				$diff = $checkTime - $loginTime;
				$open_minutes = abs($diff) / 60;
			}
			if($open_minutes < $slot_minutes){
				echo wp_kses_data("<p>No Slots Available!</p>");
			}else{				
				$slots = floor($open_minutes / $slot_minutes);
				$remove_slots = floor($break_minutes / $slot_minutes);
				echo wp_kses_data('<p>Duration: '.$duration.'</p>');
				?>
				<form name="booking_form" id="booking_form" method="POST">
				<?php
				//$unique_identifier = uniqid();
				wp_nonce_field( 'booking_form_action', 'booking_form_field' );
				// echo "<input type='hidden' name='unique_identifier' value='".$unique_identifier."'>";
				echo "<select name='slot' required>";
					echo '<option value="" selected disabled>Select Slot</option>';
				$slot = 1;
				for($i=0;$i<$slots;$i++){
					$time = strtotime($working_hours_start);
					$startTime = gmdate("H:i", strtotime('+'.$i*$slot_minutes.' minutes', $time));
					$endTime = gmdate("H:i", strtotime('+'.($i+1)*$slot_minutes.' minutes', $time));
					$slot_start = strtotime($startTime);
					$slot_end = strtotime($endTime);
					if($break_endTime <= $slot_start){
						$breakdone = 1;
					}
					if(($slot_start >= $break_startTime && $slot_end <= $break_endTime) || ($slot_start < $break_endTime && $slot_start > $break_startTime)){
						continue;
					}
					// $already_booked = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM `".$wpdb->prefix."ba_entries` WHERE slot=%s AND date=%s ", $startTime.' - '.$endTime, $_POST['date'] ));
					
					$start_time = isset($startTime) ? sanitize_text_field($startTime) : '';
					$end_time = isset($endTime) ? sanitize_text_field($endTime) : '';
					$date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';

					// Combine start and end times into the format you need
					$slot = $start_time . ' - ' . $end_time;

					// Prepare and execute the query
					$already_booked = $wpdb->get_var($wpdb->prepare(
						"SELECT COUNT(*) FROM `{$wpdb->prefix}ba_entries` WHERE slot = %s AND date = %s",
						$slot,
						$date
					));
					
					?>
					<option value="<?php echo esc_html($startTime.' - '.$endTime); ?>" <?php echo ($already_booked > 0)?'disabled':''; ?>>Slot <?php echo esc_html($slot); ?> (<?php echo esc_html($startTime.' - '.$endTime); ?>) <?php echo ($already_booked > 0)?esc_html('(Booked)'):''; ?></option>
					<?php
					$slot++;
				}
				echo "</select>";
				?>
					<input type="hidden" name="date" value="<?php echo esc_html(sanitize_text_field($_POST['date'])); ?>" />
					<input placeholder="Your Name" name="name" type="name" placeholder="name" required />
					<input placeholder="Your Email" name="email" type="email" placeholder="email" required />
					<input type="submit" name="submit" value="Book Slot" class="btn-primary" />
				</form>
				<?php
			}
			?>
		</div>
		<?php
		exit;
	}
	public function save_bookings(){
		global $wpdb;
		// $unique_identifier = $_POST['unique_identifier'];
		if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'])), 'booking_form_action' ) ) {
			wp_send_json_error( 'Nonce verification failed' );
		}
		$slot = sanitize_text_field($_POST['slot']);
		$date = sanitize_text_field($_POST['date']);
		$name = sanitize_text_field($_POST['name']);
		$email = sanitize_email($_POST['email']);
		if($slot && $date && $name && $email){
			//$wpdb->query("INSERT INTO `".$wpdb->prefix."ba_entries` (name,email,slot,date) VALUES ('$name','$email','$slot','$date')");
			$wpdb->query($wpdb->prepare(
				"INSERT INTO `{$wpdb->prefix}ba_entries` (name, email, slot, date) VALUES (%s, %s, %s, %s)",
				$name,
				$email,
				$slot,
				$date
			));
		}
		$p_settings = get_option("booking_appointment_payments_data");
		$offline_message = '';
		if(is_array($p_settings)){
			if($p_settings['offline_message']){
				$offline_message = '<p>'.stripslashes($p_settings['offline_message']).'</p>';
			}
		}
		$this->send_booking_email($wpdb->insert_id);
		echo wp_kses_data("<div style='text-align:center;'>
			<h2>Booking Confirmed..!</h2>
			<p>Date - $date / Slot - $slot</p>
			".$offline_message."
		</div>");
		exit;
	}
	public function send_booking_email($id){
		global $wpdb;
		$booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}ba_entries` WHERE id = %d",$id),ARRAY_A);
		//$booking = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."ba_entries` WHERE id=".$id, ARRAY_A);
		$tags = array('{{user-name}}','{{user-email}}','{{time-slot}}','{{booking-date}}');
		
		$option = get_option("booking_appointment_email_data");
		$email_body = stripslashes(wpautop($option['email_body']));
		$email_body = str_replace('{{user-name}}',$booking['name'],$email_body);
		$email_body = str_replace('{{user-email}}',$booking['email'],$email_body);
		$email_body = str_replace('{{time-slot}}',$booking['slot'],$email_body);
		$email_body = str_replace('{{booking-date}}',$booking['date'],$email_body);
		
		$email_subject = stripslashes($option['email_subject']);
		$email_subject = str_replace('{{user-name}}',$booking['name'],$email_subject);
		$email_subject = str_replace('{{user-email}}',$booking['email'],$email_subject);
		$email_subject = str_replace('{{time-slot}}',$booking['slot'],$email_subject);
		$email_subject = str_replace('{{booking-date}}',$booking['date'],$email_subject);
		
		$headers = array ( 'Content-Type: text/html; charset=UTF-8' );
		$emailed = wp_mail( $booking['email'], $email_subject, $email_body, $headers );
	}
}
