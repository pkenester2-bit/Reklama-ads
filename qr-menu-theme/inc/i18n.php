<?php
/**
 * Lightweight RU/KZ translations.
 *
 * @package ReklamaQRMenu
 */

function reklama_qr_get_translations(): array {
	return array(
		'ru' => array(
			'home'                  => 'Главная',
			'contacts'              => 'Контакты',
			'menu'                  => 'Меню',
			'cart'                  => 'Корзина',
			'call_waiter'           => 'Вызвать официанта',
			'book_table'            => 'Забронировать столик',
			'open_menu'             => 'Открыть меню',
			'close'                 => 'Закрыть',
			'added'                 => 'Добавлено в корзину',
			'empty_cart'            => 'Корзина пуста',
			'empty_results'         => 'Здесь пока нет блюд',
			'add'                   => 'Добавить',
			'unavailable'           => 'Нет в наличии',
			'your_order'            => 'Ваш заказ',
			'no_modifiers'          => 'Без модификаторов',
			'total'                 => 'Итого',
			'quantity'              => 'Кол-во',
			'order_comment'         => 'Комментарий к заказу',
			'table_number'          => 'Номер стола',
			'customer_name'         => 'Имя',
			'phone_optional'        => 'Телефон',
			'order_type_hall'       => 'В зал',
			'order_type_takeaway'   => 'С собой',
			'order_type_delivery'   => 'Доставка',
			'checkout'              => 'Оформить заказ',
			'show_waiter'           => 'Показать официанту',
			'waiter_list_title'     => 'Список для официанта',
			'order_accepted'        => 'Заказ принят',
			'order_number'          => 'Номер заказа',
			'status'                => 'Статус',
			'accepted'              => 'Принят',
			'eta'                   => 'Время приготовления',
			'back_to_menu'          => 'Вернуться в меню',
			'order_error'           => 'Не удалось оформить заказ',
			'booking_title'         => 'Бронирование столика',
			'booking_phone'         => 'Телефон для бронирования',
			'call_now'              => 'Позвонить',
			'no_booking_phone'      => 'Телефон пока не указан',
			'hero_fallback'         => 'Добавьте фото в настройках темы',
			'choose_category'       => 'Выберите категорию меню',
		),
		'kz' => array(
			'home'                  => 'Басты бет',
			'contacts'              => 'Байланыс',
			'menu'                  => 'Мәзір',
			'cart'                  => 'Себет',
			'call_waiter'           => 'Даяшы шақыру',
			'book_table'            => 'Үстел брондау',
			'open_menu'             => 'Мәзірді ашу',
			'close'                 => 'Жабу',
			'added'                 => 'Себетке қосылды',
			'empty_cart'            => 'Себет бос',
			'empty_results'         => 'Мұнда әзірге тағам жоқ',
			'add'                   => 'Қосу',
			'unavailable'           => 'Қолжетімсіз',
			'your_order'            => 'Сіздің тапсырысыңыз',
			'no_modifiers'          => 'Қосымша опция жоқ',
			'total'                 => 'Жалпы',
			'quantity'              => 'Саны',
			'order_comment'         => 'Тапсырысқа пікір',
			'table_number'          => 'Үстел нөмірі',
			'customer_name'         => 'Аты (міндетті емес)',
			'phone_optional'        => 'Телефон (міндетті емес)',
			'order_type_hall'       => 'Залға',
			'order_type_takeaway'   => 'Өзімен алып кету',
			'order_type_delivery'   => 'Жеткізу',
			'checkout'              => 'Тапсырыс беру',
			'show_waiter'           => 'Даяшыға көрсету',
			'waiter_list_title'     => 'Даяшыға арналған тізім',
			'order_accepted'        => 'Тапсырыс қабылданды',
			'order_number'          => 'Тапсырыс нөмірі',
			'status'                => 'Күйі',
			'accepted'              => 'Қабылданды',
			'eta'                   => 'Дайындау уақыты',
			'back_to_menu'          => 'Мәзірге оралу',
			'order_error'           => 'Тапсырысты жіберу сәтсіз',
			'booking_title'         => 'Үстел брондау',
			'booking_phone'         => 'Брондау телефоны',
			'call_now'              => 'Қоңырау шалу',
			'no_booking_phone'      => 'Телефон көрсетілмеген',
			'hero_fallback'         => 'Тақырыпқа фотоны баптаудан қосыңыз',
			'choose_category'       => 'Мәзір санатын таңдаңыз',
		),
	);
}

function reklama_qr_get_lang(): string {
	$lang = isset( $_GET['lang'] ) ? sanitize_text_field( wp_unslash( $_GET['lang'] ) ) : '';
	if ( ! in_array( $lang, array( 'ru', 'kz' ), true ) ) {
		$lang = get_theme_mod( 'qr_default_lang', 'ru' );
	}
	return in_array( $lang, array( 'ru', 'kz' ), true ) ? $lang : 'ru';
}

function reklama_qr_t( string $key, ?string $lang = null ): string {
	$translations = reklama_qr_get_translations();
	$lang         = $lang ?: reklama_qr_get_lang();
	if ( isset( $translations[ $lang ][ $key ] ) ) {
		return $translations[ $lang ][ $key ];
	}
	return $translations['ru'][ $key ] ?? $key;
}
