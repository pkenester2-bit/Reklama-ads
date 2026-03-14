<?php
/**
 * Theme setup and assets.
 *
 * @package ReklamaQRMenu
 */

add_action(
	'after_setup_theme',
	function() {
		load_theme_textdomain( 'reklama-qr-menu', REKLAMA_QR_MENU_PATH . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 80,
				'width'       => 80,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption' ) );
	}
);

add_action(
	'wp_enqueue_scripts',
	function() {
		$translations = reklama_qr_get_translations();
		$lang         = reklama_qr_get_lang();
		$slides       = array();
		for ( $i = 1; $i <= 5; $i++ ) {
			$slide = get_theme_mod( 'qr_hero_slide_' . $i, '' );
			if ( ! empty( $slide ) ) {
				$slides[] = esc_url_raw( $slide );
			}
		}

		wp_enqueue_style( 'reklama-qr-menu-style', get_stylesheet_uri(), array(), REKLAMA_QR_MENU_VERSION );
		wp_enqueue_script( 'reklama-qr-menu-app', REKLAMA_QR_MENU_URI . '/assets/js/menu-app.js', array(), REKLAMA_QR_MENU_VERSION, true );

		add_filter('script_loader_tag', function ($tag, $handle) {
			if ($handle === 'reklama-qr-menu-app') {
				return str_replace(' src', ' defer src', $tag);
			}
			return $tag;
		}, 10, 2);

		$preloaded_menu = function_exists( 'reklama_qr_get_menu_payload' ) ? reklama_qr_get_menu_payload( $lang ) : array( 'categories' => array() );

		wp_localize_script(
			'reklama-qr-menu-app',
			'qrMenuData',
			array(
				'restUrl'       => esc_url_raw( rest_url( 'reklama-qr/v1' ) ),
				'nonce'         => wp_create_nonce( 'wp_rest' ),
				'lang'          => $lang,
				'translations'  => $translations,
				'bookingPhone'  => get_theme_mod( 'qr_booking_phone', '+7 777 000 00 00' ),
				'slides'        => $slides,
				'preloadedMenu' => $preloaded_menu,
			)
		);
	}
);

add_action(
	'customize_register',
	function( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_section( 'qr_menu_general', array( 'title' => __( 'QR Menu', 'reklama-qr-menu' ) ) );

		$wp_customize->add_setting( 'qr_restaurant_name', array( 'default' => get_bloginfo( 'name' ) ) );
		$wp_customize->add_control( 'qr_restaurant_name', array( 'label' => __( 'Название ресторана', 'reklama-qr-menu' ), 'section' => 'qr_menu_general' ) );

		$wp_customize->add_setting( 'qr_restaurant_description', array( 'default' => __( 'Уютный ресторан с авторской кухней.', 'reklama-qr-menu' ) ) );
		$wp_customize->add_control(
			'qr_restaurant_description',
			array(
				'label'   => __( 'Описание ресторана', 'reklama-qr-menu' ),
				'section' => 'qr_menu_general',
				'type'    => 'textarea',
			)
		);

		$wp_customize->add_setting(
			'qr_default_lang',
			array(
				'default'           => 'ru',
				'sanitize_callback' => function( $value ) {
					return in_array( $value, array( 'ru', 'kz' ), true ) ? $value : 'ru';
				},
			)
		);
		$wp_customize->add_control(
			'qr_default_lang',
			array(
				'label'   => __( 'Язык по умолчанию', 'reklama-qr-menu' ),
				'section' => 'qr_menu_general',
				'type'    => 'select',
				'choices' => array( 'ru' => 'RU', 'kz' => 'KZ' ),
			)
		);

		$wp_customize->add_setting( 'qr_booking_phone', array( 'default' => '+7 777 000 00 00' ) );
		$wp_customize->add_control(
			'qr_booking_phone',
			array(
				'label'   => __( 'Телефон для брони', 'reklama-qr-menu' ),
				'section' => 'qr_menu_general',
				'type'    => 'text',
			)
		);

		for ( $i = 1; $i <= 5; $i++ ) {
			$setting_id = 'qr_hero_slide_' . $i;
			$wp_customize->add_setting( $setting_id, array( 'default' => '' ) );
			$wp_customize->add_control(
				new WP_Customize_Image_Control(
					$wp_customize,
					$setting_id,
					array(
						'label'   => sprintf( __( 'Слайд %d', 'reklama-qr-menu' ), $i ),
						'section' => 'qr_menu_general',
					)
				)
			);
		}
	}
);
