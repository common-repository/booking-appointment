<?php
if (!class_exists('WP_List_Table')) {
      require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Booking_Appointment_Bookings extends WP_List_Table {
	private $table_data;

    function get_columns() {
        $columns = array(
            'cb'    => '<input type="checkbox" />',
            'name'  => __('Name', 'booking-appointment'),
            'email' => __('Email', 'booking-appointment'),
            'slot'  => __('Slot', 'booking-appointment'),
            'status'=> __('Status', 'booking-appointment'),
            'date'  => __('Date', 'booking-appointment'),
        );
        return $columns;
    }

	function prepare_items() {
		$this->process_bulk_action();
		
		$search_term = isset($_POST['s']) ? sanitize_text_field(wp_unslash($_POST['s'])) : '';
		$nonce = isset($_POST['booking_appointments_search_field']) ? sanitize_text_field(wp_unslash($_POST['booking_appointments_search_field'])) : '';

		if (wp_verify_nonce($nonce, 'booking_appointments_bookings')) {
			$this->table_data = $this->get_table_data($search_term);
		} else {
			$this->table_data = $this->get_table_data();
		}

		$columns = $this->get_columns();
		$hidden = get_user_meta(get_current_user_id(), 'managetoplevel_page_bookings_tablecolumnshidden', true);
		$hidden = is_array($hidden) ? $hidden : array();
		$sortable = $this->get_sortable_columns();
		$primary = 'name';
		$this->_column_headers = array($columns, $hidden, $sortable, $primary);

		usort($this->table_data, array($this, 'usort_reorder'));

		$per_page = $this->get_items_per_page('elements_per_page', 20);
		$current_page = $this->get_pagenum();
		$total_items = count($this->table_data);

		$this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil($total_items / $per_page)
		));

		$this->items = $this->table_data;
	}

	private function get_table_data($search = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'ba_entries';

		if (!empty($search)) {
			$table = esc_sql($table);
			$search_term = '%' . $wpdb->esc_like($search) . '%';
			
			$query = $wpdb->prepare(
				"SELECT * FROM $table WHERE name LIKE %s OR email LIKE %s OR date LIKE %s",
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

	function column_default($item, $column_name) {
        switch ($column_name) {
            case 'status':
				return ($item['status'] == '1') ? __('Booked', 'booking-appointment') : __('Pending', 'booking-appointment');
            case 'id':
            case 'name':
            case 'email':
            case 'date':
            case 'slot':
            default:
                return $item[$column_name];
        }
    }

	function column_cb($item) {
		$output = sprintf(
			'<input type="checkbox" name="element[]" value="%s" />',
			$item['id']
		);
		$output .= wp_nonce_field('booking_appointments_bookings', 'booking_appointments_search_field', true, false);
		return $output;
    }

	protected function get_sortable_columns() {
		  $sortable_columns = array(
				'name'  => array('name', false),
				'email' => array('email', false),
				'status' => array('status', false),
				'date'   => array('date', true)
		  );
		  return $sortable_columns;
	}

	function usort_reorder($a, $b) {
		$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'name';
		$order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'asc';
        $result = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'asc') ? $result : -$result;
    }

	/* function column_name($item) {
		$item_id = isset($item['id']) ? intval($item['id']) : 0;
		$item_name = isset($item['name']) ? esc_html($item['name']) : '';
		$status = isset($item['status']) ? intval($item['status']) : 0;
		$page = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';

		if ($status == 0) {
			$actions = array(
				'accept'  => sprintf('<a href="?page=%s&action=%s&element=%d&_wpnonce=%s">' . __('Accept', 'booking-appointment') . '</a>', $page, 'accept', $item_id, wp_create_nonce('booking_appointments_action_' . $item_id)),
				'delete'  => sprintf('<a href="?page=%s&action=%s&element=%d&_wpnonce=%s">' . __('Delete', 'booking-appointment') . '</a>', $page, 'delete', $item_id, wp_create_nonce('booking_appointments_action_' . $item_id)),
			);
		} else {
			$actions = array(
				'decline' => sprintf('<a href="?page=%s&action=%s&element=%d&_wpnonce=%s">' . __('Decline', 'booking-appointment') . '</a>', $page, 'decline', $item_id, wp_create_nonce('booking_appointments_action_' . $item_id)),
				'delete'  => sprintf('<a href="?page=%s&action=%s&element=%d&_wpnonce=%s">' . __('Delete', 'booking-appointment') . '</a>', $page, 'delete', $item_id, wp_create_nonce('booking_appointments_action_' . $item_id)),
			);
		}

		return sprintf('%1$s %2$s', $item_name, $this->row_actions($actions));
	} */
	
	function column_name($item) {
        $nonce = wp_create_nonce('booking_appointment_nonce');

        $actions = array(
            'accept' => sprintf(
                '<a href="%s">' . esc_html__('Accept', 'booking-appointment') . '</a>',
                esc_url(add_query_arg(array(
                    'page' => sanitize_text_field($_REQUEST['page']),
                    'action' => 'accept',
                    'element' => absint($item['id']),
                    '_wpnonce' => $nonce,
                ), admin_url('admin.php')))
            ),
            'decline' => sprintf(
                '<a href="%s">' . esc_html__('Decline', 'booking-appointment') . '</a>',
                esc_url(add_query_arg(array(
                    'page' => sanitize_text_field($_REQUEST['page']),
                    'action' => 'decline',
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
            unset($actions['accept']);
        } else {
            unset($actions['decline']);
        }

        return sprintf('%1$s %2$s', esc_html($item['name']), $this->row_actions($actions));
    }

	function get_bulk_actions() {
        $actions = array(
            'delete_all'    => __('Delete', 'booking-appointment'),
            'decline_all'   => __('Decline', 'booking-appointment'),
            'accept_all'    => __('Accept', 'booking-appointment')
        );
        return $actions;
    }

	/* public function process_bulk_action() {
		global $wpdb;
		$action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
		$table = esc_sql($wpdb->prefix . 'ba_entries');

		$process_elements = function($callback) use ($wpdb, $table) {
			if (isset($_REQUEST['element']) && is_array($_REQUEST['element'])) {
				$sanitized_elements = array_map('intval', $_REQUEST['element']);
				foreach ($sanitized_elements as $element) {
					$nonce_action = 'booking_appointments_action_' . $element;
					if (isset($_REQUEST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), $nonce_action)) {
						$callback($wpdb, $table, $element);
					}
				}
			}
		};

		switch ($action) {
			case 'decline_all':
				$process_elements(function($wpdb, $table, $element) {
					$wpdb->query($wpdb->prepare("UPDATE {$table} SET status = 0 WHERE id = %d", $element));
				});
				break;

			case 'accept_all':
				$process_elements(function($wpdb, $table, $element) {
					$wpdb->query($wpdb->prepare("UPDATE {$table} SET status = 1 WHERE id = %d", $element));
				});
				break;

			case 'delete_all':
				$process_elements(function($wpdb, $table, $element) {
					$wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE id = %d", $element));
				});
				break;

			case 'accept':
			case 'decline':
			case 'delete':
				$id = isset($_REQUEST['element']) ? intval($_REQUEST['element']) : 0;
				$nonce_action = 'booking_appointments_action_' . $id;
				if ($id && isset($_REQUEST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), $nonce_action)) {
					if ($action === 'accept') {
						$wpdb->query($wpdb->prepare("UPDATE {$table} SET status = 1 WHERE id = %d", $id));
					} elseif ($action === 'decline') {
						$wpdb->query($wpdb->prepare("UPDATE {$table} SET status = 0 WHERE id = %d", $id));
					} elseif ($action === 'delete') {
						$wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE id = %d", $id));
					}
				}
				break;

			default:
				return;
		}
	} */

	public function process_bulk_action() {
		global $wpdb;
		$table = $wpdb->prefix . 'ba_entries';
		
		if ((isset($_POST['action']) && $_POST['action'] != -1) || (isset($_POST['action2']) && $_POST['action2'] != -1)) {
			if (!isset($_POST['_wpnonce_bulk_action']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce_bulk_action'])), 'bulk_action_nonce')) {
                wp_die(esc_html__('Nonce verification failed', 'booking-appointment'));
            }
			
			$action = ($_POST['action'] !== -1) ? sanitize_text_field($_POST['action']) : sanitize_text_field($_POST['action2']);
			$elements = !empty($_POST['element']) && is_array($_POST['element']) ? array_map('intval', $_POST['element']) : array();
			
			 if (!empty($elements)) {
				 switch ($action) {
                    case 'decline_all':
                        foreach ($elements as $booking) {
                            $wpdb->update($table, array('status' => 0), array('id' => $booking), array('%d'), array('%d'));
                        }
                        break;
                    case 'accept_all':
                        foreach ($elements as $booking) {
                            $wpdb->update($table, array('status' => 1), array('id' => $booking), array('%d'), array('%d'));
                        }
                        break;
                    case 'delete_all':
                        foreach ($elements as $booking) {
                            $wpdb->delete($table, array('id' => $booking), array('%d'));
                        }
                        break;
                }
			 }
		} elseif (isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('accept', 'decline', 'delete')) && !empty($_REQUEST['element'])) {
            // Single action nonce verification
            if (isset($_REQUEST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'booking_appointment_nonce')) {
                $id = intval($_REQUEST['element']);
                $action = sanitize_text_field($_REQUEST['action']);

                if ($id > 0) {
                    if ($action === 'delete') {
                        $wpdb->delete($table, array('id' => $id), array('%d'));
                    } else {
                        $status = ($action === 'accept') ? 1 : 0;
                        $wpdb->update($table, array('status' => $status), array('id' => $id), array('%d'), array('%d'));
                    }
                }
            } else {
                wp_die(esc_html__('Nonce verification failed', 'booking-appointment'));
            }
        }
	}



}
