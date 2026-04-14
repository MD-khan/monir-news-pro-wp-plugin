<?php
/**
 * MonirNews Pro — Database class.
 *
 * Creates and manages the {prefix}mnp_ads custom table using dbDelta.
 * All queries use $wpdb->prepare() to prevent SQL injection.
 *
 * @package MonirNews_Pro
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MNP_Database
 *
 * @since 2.0.0
 */
class MNP_Database {

	/**
	 * Table name without prefix.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	const TABLE_NAME = 'mnp_ads';

	/**
	 * Database schema version stored in options.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	const DB_VERSION = '2.0.0';

	/**
	 * Create or upgrade the mnp_ads table via dbDelta.
	 *
	 * Called on plugin activation via register_activation_hook.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function create_tables() {
		global $wpdb;

		$table           = $wpdb->prefix . self::TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id          int(11)      NOT NULL AUTO_INCREMENT,
			name        varchar(255) NOT NULL DEFAULT '',
			type        varchar(50)  NOT NULL DEFAULT 'image',
			media_id    int(11)      NOT NULL DEFAULT 0,
			media_url   text         NOT NULL,
			click_url   text         NOT NULL,
			zone        varchar(100) NOT NULL DEFAULT 'sidebar-top',
			start_date  datetime     NOT NULL DEFAULT '2000-01-01 00:00:00',
			end_date    datetime              DEFAULT NULL,
			status      varchar(20)  NOT NULL DEFAULT 'active',
			priority    int(11)      NOT NULL DEFAULT 10,
			impressions int(11)      NOT NULL DEFAULT 0,
			width       int(11)      NOT NULL DEFAULT 300,
			height      int(11)      NOT NULL DEFAULT 250,
			html_code   longtext     NOT NULL,
			created_at  datetime     NOT NULL DEFAULT '2000-01-01 00:00:00',
			updated_at  datetime     NOT NULL DEFAULT '2000-01-01 00:00:00',
			PRIMARY KEY  (id),
			KEY zone     (zone),
			KEY status   (status),
			KEY priority (priority)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'mnp_db_version', self::DB_VERSION );
	}

	/**
	 * Retrieve ads from the database.
	 *
	 * @since 2.0.0
	 * @param array $args {
	 *   Optional query arguments.
	 *   @type string $zone    Filter by zone slug. Default '' (all zones).
	 *   @type string $status  Filter by status. Default 'active'. Empty = all.
	 *   @type int    $limit   Max rows to return. -1 = no limit. Default -1.
	 *   @type int    $offset  Row offset for pagination. Default 0.
	 *   @type string $orderby Column to sort by. Default 'priority'.
	 *   @type string $order   Sort direction: ASC or DESC. Default 'DESC'.
	 * }
	 * @return array Array of ad row objects.
	 */
	public static function get_ads( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'zone'    => '',
			'status'  => 'active',
			'limit'   => -1,
			'offset'  => 0,
			'orderby' => 'priority',
			'order'   => 'DESC',
		);

		$args  = wp_parse_args( $args, $defaults );
		$table = $wpdb->prefix . self::TABLE_NAME;

		// Whitelist orderby column to prevent SQL injection.
		$allowed_orderby = array( 'id', 'name', 'priority', 'impressions', 'created_at', 'start_date', 'end_date', 'status', 'zone' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'priority';
		$order           = ( 'ASC' === strtoupper( $args['order'] ) ) ? 'ASC' : 'DESC';

		$conditions = array();
		$values     = array();

		if ( ! empty( $args['zone'] ) ) {
			$conditions[] = 'zone = %s';
			$values[]     = sanitize_key( $args['zone'] );
		}

		if ( ! empty( $args['status'] ) ) {
			$conditions[] = 'status = %s';
			$values[]     = sanitize_key( $args['status'] );
		}

		$where = $conditions ? 'WHERE ' . implode( ' AND ', $conditions ) : '';

		if ( (int) $args['limit'] > 0 ) {
			$values[] = (int) $args['limit'];
			$values[] = (int) $args['offset'];
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = $wpdb->prepare( "SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", $values );
		} elseif ( $values ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = $wpdb->prepare( "SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order}", $values );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = "SELECT * FROM {$table} ORDER BY {$orderby} {$order}";
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $sql );
	}

	/**
	 * Count ads matching the given filters.
	 *
	 * @since 2.0.0
	 * @param array $args Same zone/status filters as get_ads().
	 * @return int Total matching ad count.
	 */
	public static function count_ads( $args = array() ) {
		global $wpdb;

		$args  = wp_parse_args( $args, array( 'zone' => '', 'status' => '' ) );
		$table = $wpdb->prefix . self::TABLE_NAME;

		$conditions = array();
		$values     = array();

		if ( ! empty( $args['zone'] ) ) {
			$conditions[] = 'zone = %s';
			$values[]     = sanitize_key( $args['zone'] );
		}

		if ( ! empty( $args['status'] ) ) {
			$conditions[] = 'status = %s';
			$values[]     = sanitize_key( $args['status'] );
		}

		$where = $conditions ? 'WHERE ' . implode( ' AND ', $conditions ) : '';

		if ( $values ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$table} {$where}", $values );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = "SELECT COUNT(*) FROM {$table}";
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Return ad counts grouped by status.
	 *
	 * @since 2.0.0
	 * @return array {
	 *   @type int $total     All ads combined.
	 *   @type int $active    Active ads.
	 *   @type int $paused    Paused ads.
	 *   @type int $scheduled Scheduled ads.
	 *   @type int $expired   Expired ads.
	 * }
	 */
	public static function count_by_status() {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( "SELECT status, COUNT(*) AS cnt FROM {$table} GROUP BY status", ARRAY_A );

		$counts = array(
			'total'     => 0,
			'active'    => 0,
			'paused'    => 0,
			'scheduled' => 0,
			'expired'   => 0,
		);

		foreach ( $rows as $row ) {
			if ( array_key_exists( $row['status'], $counts ) ) {
				$counts[ $row['status'] ] = (int) $row['cnt'];
			}
			$counts['total'] += (int) $row['cnt'];
		}

		return $counts;
	}

	/**
	 * Retrieve a single ad by its ID.
	 *
	 * @since 2.0.0
	 * @param int $id Ad primary key.
	 * @return object|null Ad row object or null if not found.
	 */
	public static function get_ad( $id ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_NAME;

		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", absint( $id ) ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}

	/**
	 * Insert a new ad record.
	 *
	 * @since 2.0.0
	 * @param array $data Raw ad field values (sanitized internally).
	 * @return int|false New row ID on success, false on failure.
	 */
	public static function insert_ad( $data ) {
		global $wpdb;

		$table     = $wpdb->prefix . self::TABLE_NAME;
		$sanitized = self::sanitize_ad_data( $data );

		$sanitized['created_at'] = current_time( 'mysql' );
		$sanitized['updated_at'] = current_time( 'mysql' );

		$result = $wpdb->insert( $table, $sanitized, self::get_format( $sanitized ) );

		return false === $result ? false : $wpdb->insert_id;
	}

	/**
	 * Update an existing ad record.
	 *
	 * @since 2.0.0
	 * @param int   $id   Ad ID to update.
	 * @param array $data New field values (sanitized internally).
	 * @return bool True on success, false on failure.
	 */
	public static function update_ad( $id, $data ) {
		global $wpdb;

		$table     = $wpdb->prefix . self::TABLE_NAME;
		$sanitized = self::sanitize_ad_data( $data );

		$sanitized['updated_at'] = current_time( 'mysql' );

		$result = $wpdb->update(
			$table,
			$sanitized,
			array( 'id' => absint( $id ) ),
			self::get_format( $sanitized ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Delete an ad record permanently.
	 *
	 * @since 2.0.0
	 * @param int $id Ad ID.
	 * @return bool True on success, false on failure.
	 */
	public static function delete_ad( $id ) {
		global $wpdb;

		$table  = $wpdb->prefix . self::TABLE_NAME;
		$result = $wpdb->delete( $table, array( 'id' => absint( $id ) ), array( '%d' ) );

		return false !== $result;
	}

	/**
	 * Increment the impression counter for an ad by one.
	 *
	 * Uses a direct UPDATE query to avoid race conditions.
	 *
	 * @since 2.0.0
	 * @param int $id Ad ID.
	 * @return void
	 */
	public static function increment_impressions( $id ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_NAME;

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET impressions = impressions + 1 WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $id )
			)
		);
	}

	/**
	 * Mark ads as expired when their end_date has passed.
	 *
	 * Hooked to init so status is always up to date on every request.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function update_expired_ads() {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_NAME;

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET status = 'expired', updated_at = %s WHERE end_date IS NOT NULL AND end_date < NOW() AND status = 'active'", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				current_time( 'mysql' )
			)
		);
	}

	/**
	 * Sanitize all ad field values before writing to the database.
	 *
	 * @since 2.0.0
	 * @param array $data Raw input data.
	 * @return array Sanitized data ready for wpdb insert/update.
	 */
	private static function sanitize_ad_data( $data ) {
		$allowed_types    = array( 'image', 'gif', 'video', 'html' );
		$allowed_zones    = array( 'header', 'sidebar-top', 'sidebar-middle', 'in-content', 'footer', 'mobile-top' );
		$allowed_statuses = array( 'active', 'paused', 'scheduled', 'expired' );

		$out = array();

		if ( isset( $data['name'] ) ) {
			$out['name'] = sanitize_text_field( $data['name'] );
		}
		if ( isset( $data['type'] ) ) {
			$out['type'] = in_array( $data['type'], $allowed_types, true ) ? $data['type'] : 'image';
		}
		if ( isset( $data['media_id'] ) ) {
			$out['media_id'] = absint( $data['media_id'] );
		}
		if ( isset( $data['media_url'] ) ) {
			$out['media_url'] = esc_url_raw( $data['media_url'] );
		}
		if ( isset( $data['click_url'] ) ) {
			$out['click_url'] = esc_url_raw( $data['click_url'] );
		}
		if ( isset( $data['zone'] ) ) {
			$out['zone'] = in_array( $data['zone'], $allowed_zones, true ) ? $data['zone'] : 'sidebar-top';
		}
		if ( isset( $data['start_date'] ) ) {
			$dt              = str_replace( 'T', ' ', sanitize_text_field( $data['start_date'] ) );
			$out['start_date'] = ( 16 === strlen( $dt ) ) ? $dt . ':00' : $dt;
		}
		if ( array_key_exists( 'end_date', $data ) ) {
			if ( empty( $data['end_date'] ) ) {
				$out['end_date'] = null;
			} else {
				$dt            = str_replace( 'T', ' ', sanitize_text_field( $data['end_date'] ) );
				$out['end_date'] = ( 16 === strlen( $dt ) ) ? $dt . ':00' : $dt;
			}
		}
		if ( isset( $data['status'] ) ) {
			$out['status'] = in_array( $data['status'], $allowed_statuses, true ) ? $data['status'] : 'active';
		}
		if ( isset( $data['priority'] ) ) {
			$out['priority'] = absint( $data['priority'] );
		}
		if ( isset( $data['width'] ) ) {
			$out['width'] = absint( $data['width'] );
		}
		if ( isset( $data['height'] ) ) {
			$out['height'] = absint( $data['height'] );
		}
		if ( isset( $data['html_code'] ) ) {
			$out['html_code'] = wp_kses_post( $data['html_code'] );
		}

		return $out;
	}

	/**
	 * Build a printf-style format array matching the given data keys.
	 *
	 * @since 2.0.0
	 * @param array $data Data being inserted/updated.
	 * @return array Array of %d/%s format strings.
	 */
	private static function get_format( $data ) {
		$int_fields = array( 'media_id', 'priority', 'impressions', 'width', 'height' );
		$format     = array();

		foreach ( array_keys( $data ) as $key ) {
			$format[] = in_array( $key, $int_fields, true ) ? '%d' : '%s';
		}

		return $format;
	}
}
