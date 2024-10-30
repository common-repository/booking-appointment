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

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h1><?php echo esc_html_e('Settings', 'booking-appointment'); ?></h1>
	<div class="container">
		<div>
			<?php
			$config = get_option('booking_appointment_configuration');
			// print_r($config);
			?>
		  <form method="POST" name="frm_configure" id="frm_configure">
				<?php
					wp_nonce_field( 'verify_configure_nonce', 'configure_nonce' );
				?>
			  <table class="settings_table fixed striped widefat">
				<tr>
					<th>
						<label for="is_enabled"><?php echo esc_html_e('Enable / Disable', 'booking-appointment'); ?></label>
					</th>
					<td>
						<input type="checkbox" name="is_enabled" id="is_enabled" value="1" <?php echo (isset($config['is_enabled']) && $config['is_enabled'] == '1') ? 'checked' : ''; ?> />
					</td>
				</tr>
				<tr>
					<th>
						<label for="timezone"><?php echo esc_html_e('Timezone', 'booking-appointment'); ?></label>
					</th>
					<td>
						<select name="timezone" id="timezone">
							<?php 
								if($config['timezone']){
									echo wp_timezone_choice($config['timezone']);
								}else{
									echo wp_timezone_choice('UTC');
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th>
						<label for="duration"><?php echo esc_html_e('Booking / Appointment Duration', 'booking-appointment'); ?></label>
					</th>
					<td>
						<select name="duration" id="duration" required>
							<option value="" selected disabled><?php echo esc_html_e('Duration', 'booking-appointment'); ?></option>
							<option value="15" <?php echo ($config['duration'] == '15')?'selected':''; ?>><?php echo esc_html_e('15 Minutes', 'booking-appointment'); ?></option>
							<option value="30" <?php echo ($config['duration'] == '30')?'selected':''; ?>><?php echo esc_html_e('30 Minutes', 'booking-appointment'); ?></option>
							<option value="45" <?php echo ($config['duration'] == '45')?'selected':''; ?>><?php echo esc_html_e('45 Minutes', 'booking-appointment'); ?></option>
							<option value="60" <?php echo ($config['duration'] == '60')?'selected':''; ?>><?php echo esc_html_e('1 Hour', 'booking-appointment'); ?></option>
							<option value="other" <?php echo ($config['duration'] == 'other')?'selected':''; ?>><?php echo esc_html_e('Other', 'booking-appointment'); ?></option>
						</select>
					</td>
				</tr>
				<?php 
				if (is_array($config) && $config['duration'] == 'other') {
					?>
					<tr class="custom_duration">
						<th><?php echo esc_html_e('Custom Duration', 'booking-appointment'); ?></th>
						<td>
							<input type="number" min="0" max="23" name="hours" value="<?php echo esc_html(isset($config['hours']) ? $config['hours'] : ''); ?>" required /> <?php echo esc_html_e('Hours', 'booking-appointment'); ?> 
							<input type="number" min="0" max="59" name="minutes" value="<?php echo esc_html(isset($config['minutes']) ? $config['minutes'] : ''); ?>" required /> <?php echo esc_html_e('Minutes', 'booking-appointment'); ?>
						</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<th>
						<label for="working_hours"><?php echo esc_html_e('Working Hours', 'booking-appointment'); ?></label>
					</th>
					<td>
						<select name="working_hours" id="working_hours" required>
							<option value="" selected disabled><?php echo esc_html_e('Working Hours', 'booking-appointment'); ?></option>
							<option value="24" <?php echo ($config['working_hours'] == '24')?'selected':''; ?>><?php echo esc_html_e('24 Hours', 'booking-appointment'); ?></option>
							<option value="other" <?php echo ($config['working_hours'] == 'other')?'selected':''; ?>><?php echo esc_html_e('Other', 'booking-appointment'); ?></option>
						</select>
					</td>
				</tr>
				<?php 
				if (is_array($config) && $config['working_hours'] == 'other') {
					?>
					<tr class="custom_hours">
						<th><?php echo esc_html_e('Custom Working Hours', 'booking-appointment'); ?></th>
						<td>
							<input type="time" id="working_hours_start" name="working_hours_start" min="00:00" max="24:00" value="<?php echo esc_html(isset($config['working_hours_start']) ? $config['working_hours_start'] : ''); ?>" required /> 
							<?php echo esc_html_e('to', 'booking-appointment'); ?> 
							<input type="time" id="working_hours_end" name="working_hours_end" min="00:00" max="24:00" value="<?php echo esc_html(isset($config['working_hours_end']) ? $config['working_hours_end'] : ''); ?>" required />
						</td>
					</tr>
					<tr class="break">
						<th><?php echo esc_html_e('Want to add Break?', 'booking-appointment'); ?></th>
						<td>
							<input type="checkbox" name="break" value="1" <?php echo (isset($config['break']) && $config['break'] == '1') ? 'checked' : ''; ?> />
						</td>
					</tr>
					<?php 
					if (isset($config['break']) && $config['break'] == '1') {
						?>
						<tr class="custom_breaks">
							<th><?php echo esc_html_e('Break', 'booking-appointment'); ?></th>
							<td>
								<input type="time" id="break_start" name="break_start" min="<?php echo esc_html(isset($config['working_hours_start']) ? $config['working_hours_start'] : '00:00'); ?>" max="<?php echo esc_html(isset($config['working_hours_end']) ? $config['working_hours_end'] : '24:00'); ?>" value="<?php echo esc_html(isset($config['break_start']) ? $config['break_start'] : ''); ?>" required /> 
								<?php echo esc_html_e('to', 'booking-appointment'); ?> 
								<input type="time" id="break_end" name="break_end" min="<?php echo esc_html(isset($config['working_hours_start']) ? $config['working_hours_start'] : '00:00'); ?>" max="<?php echo esc_html(isset($config['working_hours_end']) ? $config['working_hours_end'] : '24:00'); ?>" value="<?php echo esc_html(isset($config['break_end']) ? $config['break_end'] : ''); ?>" required />
							</td>
						</tr>
						<?php
					}
				}
				?>
				<tr>
					<th>
						<label for="working_days"><?php echo esc_html_e('Working Days', 'booking-appointment'); ?></label>
					</th>
					<td>
						<?php if(empty($config['working_days'])){
							$config['working_days'] = array();
						} ?>
						<select name="working_days[]" id="working_days" multiple required>
							<option value="Monday" <?php echo (in_array('Monday',$config['working_days'])) ? 'selected' : ''; ?>><?php echo esc_html_e('Monday', 'booking-appointment'); ?></option>
							<option value="Tuesday" <?php echo (in_array('Tuesday',$config['working_days'])) ? 'selected' : ''; ?>><?php echo esc_html_e('Tuesday', 'booking-appointment'); ?></option>
							<option value="Wednesday" <?php echo (in_array('Wednesday',$config['working_days'])) ? 'selected' : ''; ?>><?php echo esc_html_e('Wednesday', 'booking-appointment'); ?></option>
							<option value="Thursday" <?php echo (in_array('Thursday',$config['working_days'])) ? 'selected' : ''; ?>><?php echo esc_html_e('Thursday', 'booking-appointment'); ?></option>
							<option value="Friday" <?php echo (in_array('Friday',$config['working_days'])) ? 'selected' : ''; ?>><?php echo esc_html_e('Friday', 'booking-appointment'); ?></option>
							<option value="Saturday" <?php echo (in_array('Saturday',$config['working_days'])) ? 'selected' : ''; ?>><?php echo esc_html_e('Saturday', 'booking-appointment'); ?></option>
							<option value="Sunday" <?php echo (in_array('Sunday',$config['working_days'])) ? 'selected' : ''; ?>><?php echo esc_html_e('Sunday', 'booking-appointment'); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="submit" name="save_configuration" value="<?php echo esc_html_e('Save Changes', 'booking-appointment'); ?>" class="button button-primary" />
					</td>
				</tr>
			  </table>
		  </form>
		</div>
	</div>
</div>
