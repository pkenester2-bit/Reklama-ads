<?php
/**
 * CPT and taxonomy registration.
 *
 * @package ReklamaQRMenu
 */

add_action(
	'init',
	function() {
		register_post_type(
			'menu_item',
			array(
				'label'           => __( 'Блюда', 'reklama-qr-menu' ),
				'public'          => false,
				'show_ui'         => true,
				'show_in_rest'    => true,
				'menu_icon'       => 'dashicons-carrot',
				'supports'        => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
				'capability_type' => 'post',
			)
		);

		register_taxonomy(
			'menu_category',
			'menu_item',
			array(
				'label'        => __( 'Категории меню', 'reklama-qr-menu' ),
				'public'       => true,
				'show_ui'      => true,
				'show_in_rest' => true,
				'hierarchical' => true,
			)
		);

		register_post_type(
			'qr_order',
			array(
				'label'        => __( 'Заказы', 'reklama-qr-menu' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-clipboard',
				'supports'     => array( 'title' ),
			)
		);

		register_post_status(
			'order_accepted',
			array(
				'label'                     => _x( 'Принят', 'order status', 'reklama-qr-menu' ),
				'public'                    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Принят <span class="count">(%s)</span>', 'Принят <span class="count">(%s)</span>', 'reklama-qr-menu' ),
			)
		);
		register_post_status( 'order_cooking', array( 'label' => _x( 'Готовится', 'order status', 'reklama-qr-menu' ), 'public' => true, 'show_in_admin_status_list' => true ) );
		register_post_status( 'order_ready', array( 'label' => _x( 'Готов', 'order status', 'reklama-qr-menu' ), 'public' => true, 'show_in_admin_status_list' => true ) );
		register_post_status( 'order_completed', array( 'label' => _x( 'Выдан', 'order status', 'reklama-qr-menu' ), 'public' => true, 'show_in_admin_status_list' => true ) );
	}
);
