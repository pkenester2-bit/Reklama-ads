<?php
/**
 * Demo seed on theme activation.
 *
 * @package ReklamaQRMenu
 */

add_action(
	'after_switch_theme',
	function() {
		if ( get_option( 'reklama_qr_seeded' ) ) {
			return;
		}

		$categories = array( 'Завтраки', 'Салаты', 'Супы', 'Горячие блюда', 'Пицца', 'Бургеры', 'Десерты', 'Напитки' );
		$term_ids = array();
		foreach ( $categories as $category ) {
			$term = term_exists( $category, 'menu_category' );
			if ( ! $term ) {
				$term = wp_insert_term( $category, 'menu_category' );
			}
			if ( ! is_wp_error( $term ) ) {
				$term_ids[ $category ] = (int) $term['term_id'];
			}
		}

		$items = array(
			array( 'Омлет с трюфельным сыром', 'Завтраки', 3200, '220 г' ),
			array( 'Сырники с ванильным кремом', 'Завтраки', 2900, '180 г' ),
			array( 'Салат Цезарь с курицей', 'Салаты', 3600, '260 г' ),
			array( 'Греческий салат', 'Салаты', 3100, '250 г' ),
			array( 'Крем-суп из тыквы', 'Супы', 2500, '300 мл' ),
			array( 'Том Ям с креветками', 'Супы', 4200, '350 мл' ),
			array( 'Стейк из говядины', 'Горячие блюда', 7900, '320 г' ),
			array( 'Лосось терияки', 'Горячие блюда', 6800, '280 г' ),
			array( 'Пицца Маргарита', 'Пицца', 4300, '540 г' ),
			array( 'Пицца Пепперони', 'Пицца', 4700, '560 г' ),
			array( 'Бургер Классик', 'Бургеры', 3900, '330 г' ),
			array( 'Бургер BBQ', 'Бургеры', 4300, '350 г' ),
			array( 'Тирамису', 'Десерты', 2700, '160 г' ),
			array( 'Чизкейк Нью-Йорк', 'Десерты', 2600, '150 г' ),
			array( 'Лимонад Цитрус', 'Напитки', 1800, '400 мл' ),
			array( 'Кофе Латте', 'Напитки', 1700, '300 мл' ),
			array( 'Матча-тоник', 'Напитки', 2200, '300 мл' ),
			array( 'Фокачча с розмарином', 'Завтраки', 2100, '190 г' ),
		);

		foreach ( $items as $i => $item ) {
			$post_id = wp_insert_post(
				array(
					'post_type'    => 'menu_item',
					'post_status'  => 'publish',
					'post_title'   => $item[0],
					'post_content' => 'Авторское блюдо с качественными ингредиентами и красивой подачей.',
					'menu_order'   => $i,
				)
			);
			if ( is_wp_error( $post_id ) ) {
				continue;
			}
			update_post_meta( $post_id, '_menu_price', $item[2] );
			update_post_meta( $post_id, '_menu_weight', $item[3] );
			update_post_meta( $post_id, '_menu_ingredients', 'Состав уточняйте у официанта.' );
			update_post_meta( $post_id, '_menu_available', '1' );
			update_post_meta( $post_id, '_menu_modifiers', wp_json_encode( array( array( 'name' => 'Размер', 'options' => array( 'Стандарт', 'Большой +500' ) ) ), JSON_UNESCAPED_UNICODE ) );
			if ( 0 === $i % 5 ) {
				update_post_meta( $post_id, '_menu_badges', array( 'hit' ) );
			}
			if ( 0 === $i % 6 ) {
				update_post_meta( $post_id, '_menu_badges', array( 'new' ) );
			}
			if ( isset( $term_ids[ $item[1] ] ) ) {
				wp_set_object_terms( $post_id, array( $term_ids[ $item[1] ] ), 'menu_category' );
			}
		}

		update_option( 'reklama_qr_seeded', 1 );
	}
);
