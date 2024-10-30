<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://stridedge.com
 * @since      1.0.0
 *
 * @package    Booking_Appointment
 * @subpackage Booking_Appointment/admin/partials
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="notice notice-info">
    <p><?php echo esc_html(__('To display the booking calendar, use the shortcode [booking_appointment] in your posts or pages.', 'booking-appointment')); ?></p>
</div>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h1><?php echo esc_html(__('Settings', 'booking-appointment')); ?></h1>
	<div class="container">
		<div class="tab">
		  <button class="tablinks active" onclick="openCity(event, 'Settings')"><?php echo esc_html(__('Settings', 'booking-appointment')); ?></button>
		  <button class="tablinks" onclick="openCity(event, 'Payments')"><?php echo esc_html(__('Payments', 'booking-appointment')); ?></button>
		  <button class="tablinks" onclick="openCity(event, 'Email')"><?php echo esc_html(__('Email', 'booking-appointment')); ?></button>
		</div>

		<div id="Settings" class="tabcontent" style="display: block;">
		  <h3><?php echo esc_html(__('Settings', 'booking-appointment')); ?></h3>
		  <?php
		  $settings = get_option("booking_appointment_settings_data");
		  ?>
		  <form method="POST" name="frm_settings" id="frm_settings">
				<?php
					wp_nonce_field( 'verify_business_nonce', 'business_nonce' );
				?>
			  <table class="settings_table fixed striped widefat">
				<tr>
					<th>
						<label for="business_name"><?php echo esc_html(__('Business Name', 'booking-appointment')); ?></label>
					</th>
					<td>
						<input type="text" name="business_name" id="business_name" value="<?php echo esc_html(isset($settings['business_name']) ? $settings['business_name'] : ''); ?>" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="business_address"><?php echo esc_html(__('Business Address', 'booking-appointment')); ?></label>
					</th>
					<td>
						<input type="text" name="business_address" id="business_address" value="<?php echo esc_html(isset($settings['business_address']) ? $settings['business_address'] : ''); ?>" />
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="submit" name="save_business_details" value="<?php echo esc_html(__('Save Changes', 'booking-appointment')); ?>" class="button button-primary" />
					</td>
				</tr>
			  </table>
		  </form>
		</div>

		<div id="Payments" class="tabcontent">
		  <h3><?php echo esc_html(__('Payments', 'booking-appointment')); ?></h3>
			<div>
				<h4><?php echo esc_html(__('Offline', 'booking-appointment')); ?></h4>
				<div>
					<?php
					$p_settings = get_option("booking_appointment_payments_data");
					?>
					<form method="POST" name="frm_payments" id="frm_payments">
						<?php
							wp_nonce_field( 'verify_payment_nonce', 'payment_nonce' );
						?>
						<table class="settings_table fixed striped widefat">
							<tr>
								<th>
									<label for="offline_message"><?php echo esc_html(__('Message to Users after Booking Done', 'booking-appointment')); ?></label>
								</th>
								<td>
									<?php 
									$offline_message = '';
									if(is_array($p_settings)){
										if($p_settings['offline_message']){
											$offline_message = esc_html(stripslashes($p_settings['offline_message']));
										}else{
											$offline_message = '';
										}
									}
									?>
									<textarea name="offline_message" id="offline_message"><?php echo $offline_message;?></textarea>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<input type="submit" name="save_payment_details" value="<?php echo esc_html(__('Save Changes', 'booking-appointment')); ?>" class="button button-primary" />
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>		  
		</div>

		<div id="Email" class="tabcontent">
		  <h3><?php echo esc_html(__('Email', 'booking-appointment')); ?></h3>
			<div class="ba_infobox">
				<p><strong><?php echo esc_html(__('Tags to use in Email Body', 'booking-appointment')); ?></strong></p>
				<ul>
					<li><?php esc_html_e( '<strong>{{user-name}} - </strong> To display Customer Name in Email Subject/Body', 'booking-appointment' ); ?></li>
					<li><?php esc_html_e( '<strong>{{user-email}} - </strong> To display Customer Email Address in Email Subject/Body', 'booking-appointment' ); ?></li>
					<li><?php esc_html_e( '<strong>{{time-slot}} - </strong> To display time slots of the bookings in Email Subject/Body', 'booking-appointment' ); ?></li>
					<li><?php esc_html_e( '<strong>{{booking-date}} - </strong> To display booking date of the bookings in Email Subject/Body', 'booking-appointment' ); ?></li>
				</ul>
			</div>
			<?php
			$option = get_option("booking_appointment_email_data");
			?>
			<div class="">
				<form method="POST" name="frm_email_settings" id="frm_email_settings">
					<?php
					wp_nonce_field( 'verify_email_nonce', 'email_nonce' );
					?>
					<table class="email_table fixed striped widefat" >
						<tr>
							<th id="thead">
								<label for="from_email"><?php echo esc_html(__('From Email', 'booking-appointment')); ?><span class="asterick">*</span></label>
							</th>
							<td class="br">
								<input type="email" class="content" name="from_email" id="from_email" 
									value="<?php echo isset($option['from_email']) && $option['from_email'] !== false ? esc_html($option['from_email']) : ''; ?>" 
									required>
							</td>
						</tr>
						<tr>
							<th id="thead">
								<label for="from_name"><?php echo esc_html(__('From Name', 'booking-appointment')); ?><span class="asterick">*</span></label>
							</th>
							<td class="br">
								<input type="text" class="content" name="from_name" id="from_name" 
									value="<?php echo isset($option['from_name']) && $option['from_name'] !== false ? esc_html($option['from_name']) : ''; ?>" 
									required>
							</td>
						</tr>
						<tr>
							<th id="thead">
								<label for="replyto_email"><?php echo esc_html(__('Reply-to Email', 'booking-appointment')); ?><span class="asterick">*</span></label>
							</th>
							<td class="br">
								<input type="email" class="content" name="replyto_email" id="replyto_email" 
									value="<?php echo isset($option['replyto_email']) && $option['replyto_email'] !== false ? esc_html($option['replyto_email']) : ''; ?>" 
									required>
							</td>
						</tr>
						<tr>
							<th id="thead">
								<label for="replyto_name"><?php echo esc_html(__('Reply-to Name', 'booking-appointment')); ?><span class="asterick">*</span></label>
							</th>
							<td class="br">
								<input type="text" class="content" name="replyto_name" id="replyto_name" 
									value="<?php echo isset($option['replyto_name']) && $option['replyto_name'] !== false ? esc_html($option['replyto_name']) : ''; ?>" 
									required>
							</td>
						</tr>
						<tr>
							<th id="thead">
								<label for="email_subject"><?php echo esc_html(__('Email Subject', 'booking-appointment')); ?><span class="asterick">*</span></th></label>							
							<td class="br">
								<input type="text" class="content" name="email_subject" id="email_subject" 
									value="<?php echo isset($option['email_subject']) && $option['email_subject'] !== false ? esc_html($option['email_subject']) : ''; ?>" 
									required>
							</td>
						</tr>
						<tr>
							<th id="thead"><?php echo esc_html(__('Email Body', 'booking-appointment')); ?></th>
							<td class="br">
								<?php 
									if (isset($option['email_body']) && $option['email_body'] !== false) {
										wp_editor(stripslashes(wpautop($option['email_body'])), 'email_body');
									} else {
										wp_editor('', 'email_body');
									}
								?> 
							</td>
						</tr>
						<tr>
							<td colspan="2" class="br">
								<input type="submit" name="submit_email_settings" value="<?php echo esc_html(__('Save Changes', 'booking-appointment')); ?>" />
								<img id="save_emailsettings_loader" src="<?php echo esc_html(site_url()); ?>/wp-admin/images/spinner.gif" style="margin:5px;display:none;" />
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>
	</div>
</div>
