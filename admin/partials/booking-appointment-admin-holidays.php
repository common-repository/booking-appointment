<?php
if (!class_exists('WP_List_Table')) {
      require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
class Booking_Appointment_Holidays extends WP_List_Table{
	private $table_data;
    function get_columns(){
        $columns = array(
                'cb'            => '<input type="checkbox" />',
                'title'          => __('Title', 'booking-appointment'),
                'description'         => __('Description', 'booking-appointment'),
                'status'   => __('Status', 'booking-appointment'),
                'date'   => __('Date', 'booking-appointment'),
        );
        return $columns;
    }
	function prepare_items(){
        $this->process_bulk_action();
		
		$search_term = isset($_POST['s']) ? sanitize_text_field(wp_unslash($_POST['s'])) : '';
		$nonce = isset($_POST['booking_holidays_search_field']) ? sanitize_text_field(wp_unslash($_POST['booking_holidays_search_field'])) : '';
		
		if (wp_verify_nonce($nonce, 'booking_holidays_search')) {
            $this->table_data = $this->get_table_data($search_term);
        } else {
            $this->table_data = $this->get_table_data();
        }

        $columns = $this->get_columns();
        $hidden = ( is_array(get_user_meta( get_current_user_id(), 'managetoplevel_page_holidays_tablecolumnshidden', true)) ) ? get_user_meta( get_current_user_id(), 'managetoplevel_page_holidays_tablecolumnshidden', true) : array();
        $sortable = $this->get_sortable_columns();
        $primary  = 'title';
        $this->_column_headers = array($columns, $hidden, $sortable, $primary);

        usort($this->table_data, array(&$this, 'usort_reorder'));
		
		/* pagination */
        $per_page = $this->get_items_per_page('elements_per_page', 20);
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);

        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
                'total_items' => $total_items, // total number of items
                'per_page'    => $per_page, // items to show on a page
                'total_pages' => ceil( $total_items / $per_page ) // use ceil to round up
        ));

        $this->items = $this->table_data;
    }
	private function get_table_data($search = '') {
        global $wpdb;

        $table = $wpdb->prefix . 'ba_holidays';
		
		if ( !empty($search) ) {			
			$table = esc_sql($table);
			$search_term = '%' . $wpdb->esc_like($search) . '%';
			
			$query = $wpdb->prepare(
				"SELECT * FROM $table WHERE title LIKE %s OR description LIKE %s OR date LIKE %s",
				$search_term,
				$search_term,
				$search_term
			);

			$results = $wpdb->get_results($query, ARRAY_A);
			return $results;
		} else {
			$table = esc_sql($table);

			$query = $wpdb->prepare(
				"SELECT * FROM `$table` WHERE 1 = %d",
				1 
			);

			$results = $wpdb->get_results($query, ARRAY_A);
			return $results;
        }
    }
	function column_default($item, $column_name){
          switch ($column_name) {
                case 'status':
					return ($item['status'] == '1')?__('Enabled', 'booking-appointment'):__('Disabled', 'booking-appointment');
					break;
                case 'id':
                case 'title':
                case 'description':
                case 'date':
                default:
                    return $item[$column_name];
          }
    }
	function column_cb($item) {
        $output = sprintf(
            '<input type="checkbox" name="element[]" value="%s" />',
            esc_attr($item['id']),
        );
		$output .= wp_nonce_field('booking_holidays_search', 'booking_holidays_search_field', true, false);
		return $output;
    }
	protected function get_sortable_columns() {
        return array(
            'title' => array('title', false),
            'status' => array('status', false),
            'date' => array('date', true),
        );
    }
	function usort_reorder($a, $b) {
		if(isset($_GET['orderby']) || isset($_GET['order'])){
			$orderby = !empty($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'title';
			$order = !empty($_GET['order']) ? sanitize_text_field($_GET['order']) : 'asc';
			$result = strcmp($a[$orderby], $b[$orderby]);

			return ($order === 'asc') ? $result : -$result;
		}
    }
	
	function column_title($item) {
        $nonce = wp_create_nonce('booking_appointment_nonce');

        $actions = array(
            'enable' => sprintf(
                '<a href="%s">' . esc_html__('Enable', 'booking-appointment') . '</a>',
                esc_url(add_query_arg(array(
                    'page' => sanitize_text_field($_REQUEST['page']),
                    'action' => 'enable',
                    'element' => absint($item['id']),
                    '_wpnonce' => $nonce,
                ), admin_url('admin.php')))
            ),
            'disable' => sprintf(
                '<a href="%s">' . esc_html__('Disable', 'booking-appointment') . '</a>',
                esc_url(add_query_arg(array(
                    'page' => sanitize_text_field($_REQUEST['page']),
                    'action' => 'disable',
                    'element' => absint($item['id']),
                    '_wpnonce' => $nonce,
                ), admin_url('admin.php')))
            ),
            'delete' => sprintf(
                '<a href="%s">' . esc_html__('Delete', 'booking-appointment') . '</a>',
                esc_url(add_query_arg(array(
                    'page' => sanitize_text_field($_REQUEST['page']),
                    'action' => 'delete',
                    'element' => absint($item['id']),
                    '_wpnonce' => $nonce,
                ), admin_url('admin.php')))
            ),
        );

        if ($item['status'] == '1') {
            unset($actions['enable']);
        } else {
            unset($actions['disable']);
        }

        return sprintf('%1$s %2$s', esc_html($item['title']), $this->row_actions($actions));
    }
	
	function get_bulk_actions(){
            $actions = array(
				'delete_all'    => esc_html__('Delete', 'booking-appointment'),
				'disable_all' => esc_html__('Disable', 'booking-appointment'),
				'enable_all' => esc_html__('Enable', 'booking-appointment')
            );
            return $actions;
    }
	
	/* public function process_bulk_action() {
        global $wpdb;
        $table = $wpdb->prefix . 'ba_holidays';
		$elements = array_map('intval', $_REQUEST['element']);
        if (!empty($_REQUEST['element']) && is_array($elements)) {
			// echo $_REQUEST['_wpnonce'];
			if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash ($_REQUEST['_wpnonce'], 'booking_appointment_nonce')))) {
				wp_die(esc_html__('Nonce verification failed', 'booking-appointment'));
			}
            $action = sanitize_text_field($_REQUEST['action']);
            switch ($action) {
                case 'disable_all':
                    foreach ($elements as $holiday) {
						if ($holiday > 0) {
							$wpdb->update($table, array('status' => 0), array('id' => intval($holiday)), array('%d'), array('%d'));
						}
                    }
                    break;
                case 'enable_all':
                    foreach ($elements as $holiday) {
						if ($holiday > 0) {
							$wpdb->update($table, array('status' => 1), array('id' => intval($holiday)), array('%d'), array('%d'));
						}
                    }
                    break;
                case 'delete_all':
                    foreach ($elements as $holiday) {
						if ($holiday > 0) {
							$wpdb->delete($table, array('id' => intval($holiday)), array('%d'));
						}
                    }
                    break;
            }
        }

        if (isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('enable', 'disable', 'delete')) && !empty($_REQUEST['element'])) {
            $id = intval($_REQUEST['element']);
            $status = (sanitize_text_field($_REQUEST['action']) === 'enable') ? 1 : 0;
            
            if (sanitize_text_field($_REQUEST['action']) === 'delete') {
                $wpdb->delete($table, array('id' => $id), array('%d'));
            } else {
                $wpdb->update($table, array('status' => $status), array('id' => $id), array('%d'), array('%d'));
            }
        }
    } */
	
	public function process_bulk_action() {
        global $wpdb;
        $table = $wpdb->prefix . 'ba_holidays';

        if ((isset($_POST['action']) && $_POST['action'] != -1) || (isset($_POST['action2']) && $_POST['action2'] != -1)) {
            // Verify nonce for bulk actions
            if (!isset($_POST['_wpnonce_bulk_action']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce_bulk_action'])), 'bulk_action_nonce')) {
                wp_die(esc_html__('Nonce verification failed', 'booking-appointment'));
            }

            $action = ($_POST['action'] !== -1) ? sanitize_text_field($_POST['action']) : sanitize_text_field($_POST['action2']);
            $elements = !empty($_POST['element']) && is_array($_POST['element']) ? array_map('intval', $_POST['element']) : array();

            if (!empty($elements)) {
                switch ($action) {
                    case 'disable_all':
                        foreach ($elements as $holiday) {
                            $wpdb->update($table, array('status' => 0), array('id' => $holiday), array('%d'), array('%d'));
                        }
                        break;
                    case 'enable_all':
                        foreach ($elements as $holiday) {
                            $wpdb->update($table, array('status' => 1), array('id' => $holiday), array('%d'), array('%d'));
                        }
                        break;
                    case 'delete_all':
                        foreach ($elements as $holiday) {
                            $wpdb->delete($table, array('id' => $holiday), array('%d'));
                        }
                        break;
                }
            }
        } elseif (isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('enable', 'disable', 'delete')) && !empty($_REQUEST['element'])) {
            // Single action nonce verification
            if (isset($_REQUEST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'booking_appointment_nonce')) {
                $id = intval($_REQUEST['element']);
                $action = sanitize_text_field($_REQUEST['action']);

                if ($id > 0) {
                    if ($action === 'delete') {
                        $wpdb->delete($table, array('id' => $id), array('%d'));
                    } else {
                        $status = ($action === 'enable') ? 1 : 0;
                        $wpdb->update($table, array('status' => $status), array('id' => $id), array('%d'), array('%d'));
                    }
                }
            } else {
                wp_die(esc_html__('Nonce verification failed', 'booking-appointment'));
            }
        }
    }
}