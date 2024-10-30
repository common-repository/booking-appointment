<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="wrap">
	<h2> <?php echo esc_html_e('Add Holiday', 'booking-appointment'); ?> <a href="?page=bookings-appointments-holidays" id="doc_popup" class="add-new-h2"><?php echo esc_html_e('All Holidays', 'booking-appointment'); ?></a></h2>
	<div>
		<form action="admin.php?page=bookings-appointments-holidays" name="add_holiday" id="add_holiday" method="POST">
			<?php
			wp_nonce_field( 'holiday_nonce', 'nonce_holiday' );
			?>
			<table class="widefat striped">
				<tr>
					<th>
						<label><?php echo esc_html_e('Holiday Name', 'booking-appointment'); ?></label>
					</th>
					<td>
						<input name="holiday_name" value="" type="text" required />
					</td>
				</tr>
				<tr>
					<th>
						<label><?php echo esc_html_e('Holiday Description', 'booking-appointment'); ?></label>
					</th>
					<td>
						<textarea name="holiday_description" required></textarea>
					</td>
				</tr>
				<tr>
					<th>
						<label><?php echo esc_html_e('Holiday Date', 'booking-appointment'); ?></label>
					</th>
					<td>
						<input type="date" name="holiday_date" required />
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<input name="submit" type="submit" value="<?php echo esc_html_e('Save Changes', 'booking-appointment'); ?>" class="button" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>