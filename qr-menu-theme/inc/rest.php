<?php
/**
 * REST API routes for menu and checkout.
 *
 * @package ReklamaQRMenu
 */

add_action(
	'rest_api_init',
	function() {
		register_rest_route(
			'reklama-qr/v1',
			'/menu',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => 'reklama_qr_rest_get_menu',
			)
		);

		register_rest_route(
			'reklama-qr/v1',
			'/orders',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'reklama_qr_rest_create_order',
			)
		);
	}
);

function reklama_qr_rest_get_menu( WP_REST_Request $request ): WP_REST_Response {
	$lang = sanitize_text_field( (string) $request->get_param( "lang" ) );
	$lang = in_array( $lang, array( "ru", "kz" ), true ) ? $lang : reklama_qr_get_lang();

	return new WP_REST_Response( reklama_qr_get_menu_payload( $lang ) );
}

function reklama_qr_get_menu_payload( string $lang ): array {
	$cache_key = 'qr_menu_payload_' . $lang;
	$cached = get_transient($cache_key);

	if ($cached !== false) {
		return $cached;
	}
	$categories = get_terms( array( 'taxonomy' => 'menu_category', 'hide_empty' => false ) );
	if ( is_wp_error( $categories ) ) {
		$categories = array();
	}
	$data = array();

	foreach ( $categories as $category ) {
		$items = get_posts(
			array(
				'post_type'      => 'menu_item',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'tax_query'      => array(
					array(
						'taxonomy' => 'menu_category',
						'field'    => 'term_id',
						'terms'    => $category->term_id,
					),
				),
			)
		);

		$prepared_items = array();
		foreach ( $items as $item ) {
			$prepared_items[] = reklama_qr_prepare_menu_item( $item, $lang );
		}

		if ( ! empty( $prepared_items ) ) {
			$data[] = array(
				'id'    => $category->term_id,
				'name'  => $category->name,
				'slug'  => $category->slug,
				'items' => $prepared_items,
			);
		}
	}


	if ( empty( $data ) ) {
		$all_items = get_posts(
			array(
				'post_type'      => 'menu_item',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			)
		);
		$prepared_items = array();
		foreach ( $all_items as $item ) {
			$prepared_items[] = reklama_qr_prepare_menu_item( $item, $lang );
		}
		if ( ! empty( $prepared_items ) ) {
			$data[] = array(
				'id'    => 0,
				'name'  => __( 'Меню', 'reklama-qr-menu' ),
				'slug'  => 'all',
				'items' => $prepared_items,
			);
		}
	}

	return array( 'categories' => $data );
	$result = array( 'categories' => $data );

	set_transient($cache_key, $result, 3600);

	return $result;
}


function reklama_qr_prepare_menu_item( WP_Post $item, string $lang ): array {
	$modifiers = get_post_meta( $item->ID, '_menu_modifiers', true );
	$title_ru  = get_post_meta( $item->ID, '_menu_title_ru', true );
	$title_kz  = get_post_meta( $item->ID, '_menu_title_kz', true );
	$desc_ru   = get_post_meta( $item->ID, '_menu_description_ru', true );
	$desc_kz   = get_post_meta( $item->ID, '_menu_description_kz', true );
	$ing_ru    = get_post_meta( $item->ID, '_menu_ingredients_ru', true );
	$ing_kz    = get_post_meta( $item->ID, '_menu_ingredients_kz', true );

	return array(
		'id'             => $item->ID,
		'title'          => 'kz' === $lang ? ( $title_kz ?: ( $title_ru ?: $item->post_title ) ) : ( $title_ru ?: ( $title_kz ?: $item->post_title ) ),
		'title_ru'       => $title_ru ?: $item->post_title,
		'title_kz'       => $title_kz ?: ( $title_ru ?: $item->post_title ),
		'description'    => 'kz' === $lang ? ( $desc_kz ?: ( $desc_ru ?: wp_strip_all_tags( $item->post_content ) ) ) : ( $desc_ru ?: ( $desc_kz ?: wp_strip_all_tags( $item->post_content ) ) ),
		'description_ru' => $desc_ru ?: wp_strip_all_tags( $item->post_content ),
		'description_kz' => $desc_kz ?: ( $desc_ru ?: wp_strip_all_tags( $item->post_content ) ),
		'price'          => (float) get_post_meta( $item->ID, '_menu_price', true ),
		'weight'         => get_post_meta( $item->ID, '_menu_weight', true ),
		'ingredients'    => 'kz' === $lang ? ( $ing_kz ?: ( $ing_ru ?: get_post_meta( $item->ID, '_menu_ingredients', true ) ) ) : ( $ing_ru ?: ( $ing_kz ?: get_post_meta( $item->ID, '_menu_ingredients', true ) ) ),
		'ingredients_ru' => $ing_ru ?: get_post_meta( $item->ID, '_menu_ingredients', true ),
		'ingredients_kz' => $ing_kz ?: ( $ing_ru ?: get_post_meta( $item->ID, '_menu_ingredients', true ) ),
		'available'      => '1' === get_post_meta( $item->ID, '_menu_available', true ),
		'badges'         => (array) get_post_meta( $item->ID, '_menu_badges', true ),
		'image'          => get_the_post_thumbnail_url( $item->ID, 'medium' ) ?: '',
		'modifiers'      => reklama_qr_parse_modifiers( $modifiers ),
	);
}

function reklama_qr_parse_modifiers( string $json ): array {
	if ( empty( $json ) ) {
		return array();
	}
	$decoded = json_decode( $json, true );
	return is_array( $decoded ) ? $decoded : array();
}

function reklama_qr_rest_create_order( WP_REST_Request $request ) {
	$payload = $request->get_json_params();
	if ( empty( $payload['items'] ) || ! is_array( $payload['items'] ) ) {
		return new WP_Error( 'invalid_items', __( 'Пустой заказ', 'reklama-qr-menu' ), array( 'status' => 400 ) );
	}

	$total = 0;
	$prepared_items = array();
	$lang = isset( $payload['lang'] ) && in_array( $payload['lang'], array( 'ru', 'kz' ), true ) ? $payload['lang'] : 'ru';
	foreach ( $payload['items'] as $item ) {
		$id = absint( $item['id'] ?? 0 );
		$qty = max( 1, absint( $item['qty'] ?? 1 ) );
		$price = (float) get_post_meta( $id, '_menu_price', true );
		if ( $id <= 0 || $price <= 0 ) {
			continue;
		}
		$total += $price * $qty;
		$title_ru = get_post_meta( $id, '_menu_title_ru', true );
		$title_kz = get_post_meta( $id, '_menu_title_kz', true );
		$name     = 'kz' === $lang ? ( $title_kz ?: ( $title_ru ?: get_the_title( $id ) ) ) : ( $title_ru ?: ( $title_kz ?: get_the_title( $id ) ) );

		$prepared_items[] = array(
			'id'        => $id,
			'name'      => $name,
			'qty'       => $qty,
			'price'     => $price,
			'modifiers' => isset( $item['modifiers'] ) ? array_map( 'sanitize_text_field', (array) $item['modifiers'] ) : array(),
		);
	}

	if ( empty( $prepared_items ) ) {
		return new WP_Error( 'invalid_items', __( 'Нет валидных позиций', 'reklama-qr-menu' ), array( 'status' => 400 ) );
	}

	$order_number = 'QR-' . gmdate( 'ymd' ) . '-' . wp_rand( 1000, 9999 );
	$order_id = wp_insert_post(
		array(
			'post_type'   => 'qr_order',
			'post_title'  => $order_number,
			'post_status' => 'order_accepted',
		)
	);

	if ( is_wp_error( $order_id ) ) {
		return $order_id;
	}

	update_post_meta( $order_id, '_order_number', $order_number );
	update_post_meta( $order_id, '_order_table', sanitize_text_field( $payload['table'] ?? '' ) );
	update_post_meta( $order_id, '_order_customer_name', sanitize_text_field( $payload['customerName'] ?? '' ) );
	update_post_meta( $order_id, '_order_phone', sanitize_text_field( $payload['phone'] ?? '' ) );
	update_post_meta( $order_id, '_order_comment', sanitize_textarea_field( $payload['comment'] ?? '' ) );
	update_post_meta( $order_id, '_order_type', sanitize_text_field( $payload['orderType'] ?? 'dine-in' ) );
	update_post_meta( $order_id, '_order_total', $total );
	update_post_meta( $order_id, '_order_items', wp_json_encode( $prepared_items, JSON_UNESCAPED_UNICODE ) );
	update_post_meta( $order_id, '_order_eta', '15-25 мин' );

	return new WP_REST_Response(
		array(
			'orderId'     => $order_id,
			'orderNumber' => $order_number,
			'status'      => 'accepted',
			'eta'         => '15-25 мин',
		),
		201
	);
}

add_action('save_post_menu_item', function () {
    delete_transient('qr_menu_payload_ru');
    delete_transient('qr_menu_payload_kz');
});