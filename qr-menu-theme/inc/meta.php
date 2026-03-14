<?php
/**
 * Meta boxes and admin order readability.
 *
 * @package ReklamaQRMenu
 */

add_action(
	'add_meta_boxes',
	function() {
		add_meta_box( 'menu_item_data', __( 'Параметры блюда', 'reklama-qr-menu' ), 'reklama_qr_render_menu_meta', 'menu_item', 'normal', 'high' );
		add_meta_box( 'order_data_human', __( 'Карточка заказа', 'reklama-qr-menu' ), 'reklama_qr_render_order_meta_human', 'qr_order', 'normal', 'high' );
	}
);

function reklama_qr_render_menu_meta( WP_Post $post ) {
	wp_nonce_field( 'reklama_menu_meta', 'reklama_menu_meta_nonce' );
	$price       = get_post_meta( $post->ID, '_menu_price', true );
	$weight      = get_post_meta( $post->ID, '_menu_weight', true );
	$ingredients = get_post_meta( $post->ID, '_menu_ingredients', true );
	$available   = get_post_meta( $post->ID, '_menu_available', true );
	$modifiers   = get_post_meta( $post->ID, '_menu_modifiers', true );
	$badges      = (array) get_post_meta( $post->ID, '_menu_badges', true );
	?>
	<p><label><?php esc_html_e( 'Цена', 'reklama-qr-menu' ); ?> <input type="number" min="0" step="0.01" name="menu_price" value="<?php echo esc_attr( $price ); ?>"></label></p>
	<p><label><?php esc_html_e( 'Вес / объем', 'reklama-qr-menu' ); ?> <input type="text" name="menu_weight" value="<?php echo esc_attr( $weight ); ?>"></label></p>
	<p><label><?php esc_html_e( 'Состав / ингредиенты', 'reklama-qr-menu' ); ?><br><textarea name="menu_ingredients" rows="3" style="width:100%;"><?php echo esc_textarea( $ingredients ); ?></textarea></label></p>
	<hr>
	<p><strong><?php esc_html_e( 'Локализация блюда (RU/KZ)', 'reklama-qr-menu' ); ?></strong></p>
	<p><label>Название (RU) <input type="text" name="menu_title_ru" value="<?php echo esc_attr( get_post_meta( $post->ID, '_menu_title_ru', true ) ); ?>" style="width:100%;"></label></p>
	<p><label>Название (KZ) <input type="text" name="menu_title_kz" value="<?php echo esc_attr( get_post_meta( $post->ID, '_menu_title_kz', true ) ); ?>" style="width:100%;"></label></p>
	<p><label>Описание (RU)<br><textarea name="menu_description_ru" rows="3" style="width:100%;"><?php echo esc_textarea( get_post_meta( $post->ID, '_menu_description_ru', true ) ); ?></textarea></label></p>
	<p><label>Описание (KZ)<br><textarea name="menu_description_kz" rows="3" style="width:100%;"><?php echo esc_textarea( get_post_meta( $post->ID, '_menu_description_kz', true ) ); ?></textarea></label></p>
	<p><label>Состав (RU)<br><textarea name="menu_ingredients_ru" rows="2" style="width:100%;"><?php echo esc_textarea( get_post_meta( $post->ID, '_menu_ingredients_ru', true ) ); ?></textarea></label></p>
	<p><label>Состав (KZ)<br><textarea name="menu_ingredients_kz" rows="2" style="width:100%;"><?php echo esc_textarea( get_post_meta( $post->ID, '_menu_ingredients_kz', true ) ); ?></textarea></label></p>
	<p><label><input type="checkbox" name="menu_available" value="1" <?php checked( $available, '1' ); ?>> <?php esc_html_e( 'В наличии', 'reklama-qr-menu' ); ?></label></p>
	<p><label><?php esc_html_e( 'Модификаторы (JSON)', 'reklama-qr-menu' ); ?><br><textarea name="menu_modifiers" rows="4" style="width:100%;"><?php echo esc_textarea( $modifiers ); ?></textarea></label></p>
	<p><?php esc_html_e( 'Бейджи', 'reklama-qr-menu' ); ?>:
		<label><input type="checkbox" name="menu_badges[]" value="hit" <?php checked( in_array( 'hit', $badges, true ) ); ?>> HIT</label>
		<label><input type="checkbox" name="menu_badges[]" value="new" <?php checked( in_array( 'new', $badges, true ) ); ?>> NEW</label>
		<label><input type="checkbox" name="menu_badges[]" value="spicy" <?php checked( in_array( 'spicy', $badges, true ) ); ?>> SPICY</label>
		<label><input type="checkbox" name="menu_badges[]" value="vegan" <?php checked( in_array( 'vegan', $badges, true ) ); ?>> VEGAN</label>
	</p>
	<?php
}

function reklama_qr_render_order_meta_human( WP_Post $post ) {
	$order_number = get_post_meta( $post->ID, '_order_number', true );
	$table        = get_post_meta( $post->ID, '_order_table', true );
	$name         = get_post_meta( $post->ID, '_order_customer_name', true );
	$phone        = get_post_meta( $post->ID, '_order_phone', true );
	$comment      = get_post_meta( $post->ID, '_order_comment', true );
	$order_type   = get_post_meta( $post->ID, '_order_type', true );
	$total        = (float) get_post_meta( $post->ID, '_order_total', true );
	$eta          = get_post_meta( $post->ID, '_order_eta', true );
	$items_raw    = get_post_meta( $post->ID, '_order_items', true );
	$status       = get_post_status_object( $post->post_status );

	$items = json_decode( (string) $items_raw, true );
	if ( ! is_array( $items ) ) {
		$items = array();
	}

	echo '<style>.qr-order-card{display:grid;grid-template-columns:repeat(2,minmax(220px,1fr));gap:10px}.qr-order-card div{background:#fff;padding:10px;border:1px solid #e0e0e0;border-radius:8px}.qr-order-table{width:100%;border-collapse:collapse;margin-top:12px}.qr-order-table th,.qr-order-table td{border:1px solid #e0e0e0;padding:8px;text-align:left}.qr-order-muted{color:#6f6f6f}</style>';
	echo '<div class="qr-order-card">';
	echo '<div><strong>Номер заказа:</strong><br>' . esc_html( $order_number ?: '—' ) . '</div>';
	echo '<div><strong>Статус:</strong><br>' . esc_html( $status->label ?? $post->post_status ) . '</div>';
	echo '<div><strong>Стол:</strong><br>' . esc_html( $table ?: '—' ) . '</div>';
	echo '<div><strong>Тип заказа:</strong><br>' . esc_html( $order_type ?: '—' ) . '</div>';
	echo '<div><strong>Клиент:</strong><br>' . esc_html( $name ?: '—' ) . '</div>';
	echo '<div><strong>Телефон:</strong><br>' . esc_html( $phone ?: '—' ) . '</div>';
	echo '<div><strong>Комментарий:</strong><br>' . esc_html( $comment ?: '—' ) . '</div>';
	echo '<div><strong>ETA:</strong><br>' . esc_html( $eta ?: '—' ) . '</div>';
	echo '</div>';

	echo '<h3 style="margin-top:16px;">Позиции заказа</h3>';
	if ( empty( $items ) ) {
		echo '<p class="qr-order-muted">Позиции не найдены.</p>';
	} else {
		echo '<table class="qr-order-table"><thead><tr><th>Блюдо</th><th>Кол-во</th><th>Цена</th><th>Сумма</th><th>Модификаторы</th></tr></thead><tbody>';
		foreach ( $items as $item ) {
			$qty        = max( 1, absint( $item['qty'] ?? 1 ) );
			$price      = (float) ( $item['price'] ?? 0 );
			$line_total = $qty * $price;
			$mods       = isset( $item['modifiers'] ) && is_array( $item['modifiers'] ) ? implode( ', ', array_map( 'sanitize_text_field', $item['modifiers'] ) ) : '';

			echo '<tr>';
			echo '<td>' . esc_html( $item['name'] ?? '' ) . '</td>';
			echo '<td>' . esc_html( (string) $qty ) . '</td>';
			echo '<td>' . esc_html( number_format_i18n( $price, 0 ) ) . ' ₸</td>';
			echo '<td>' . esc_html( number_format_i18n( $line_total, 0 ) ) . ' ₸</td>';
			echo '<td>' . esc_html( $mods ?: '—' ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	echo '<p style="margin-top:12px;"><strong>Итоговая сумма: ' . esc_html( number_format_i18n( $total, 0 ) ) . ' ₸</strong></p>';
}

add_action(
	'admin_menu',
	function() {
		remove_meta_box( 'postcustom', 'qr_order', 'normal' );
		remove_meta_box( 'slugdiv', 'qr_order', 'normal' );
	}
);

add_filter(
	'default_hidden_meta_boxes',
	function( array $hidden, WP_Screen $screen ) {
		if ( 'qr_order' === $screen->post_type ) {
			$hidden[] = 'postcustom';
		}
		return $hidden;
	},
	10,
	2
);

add_action(
	'save_post_menu_item',
	function( int $post_id ) {
		if ( ! isset( $_POST['reklama_menu_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['reklama_menu_meta_nonce'] ) ), 'reklama_menu_meta' ) ) {
			return;
		}
		update_post_meta( $post_id, '_menu_price', isset( $_POST['menu_price'] ) ? (float) $_POST['menu_price'] : '' );
		update_post_meta( $post_id, '_menu_weight', isset( $_POST['menu_weight'] ) ? sanitize_text_field( wp_unslash( $_POST['menu_weight'] ) ) : '' );
		update_post_meta( $post_id, '_menu_ingredients', isset( $_POST['menu_ingredients'] ) ? sanitize_textarea_field( wp_unslash( $_POST['menu_ingredients'] ) ) : '' );
		update_post_meta( $post_id, '_menu_available', isset( $_POST['menu_available'] ) ? '1' : '0' );
		update_post_meta( $post_id, '_menu_modifiers', isset( $_POST['menu_modifiers'] ) ? wp_kses_post( wp_unslash( $_POST['menu_modifiers'] ) ) : '' );
		update_post_meta( $post_id, '_menu_title_ru', isset( $_POST['menu_title_ru'] ) ? sanitize_text_field( wp_unslash( $_POST['menu_title_ru'] ) ) : '' );
		update_post_meta( $post_id, '_menu_title_kz', isset( $_POST['menu_title_kz'] ) ? sanitize_text_field( wp_unslash( $_POST['menu_title_kz'] ) ) : '' );
		update_post_meta( $post_id, '_menu_description_ru', isset( $_POST['menu_description_ru'] ) ? sanitize_textarea_field( wp_unslash( $_POST['menu_description_ru'] ) ) : '' );
		update_post_meta( $post_id, '_menu_description_kz', isset( $_POST['menu_description_kz'] ) ? sanitize_textarea_field( wp_unslash( $_POST['menu_description_kz'] ) ) : '' );
		update_post_meta( $post_id, '_menu_ingredients_ru', isset( $_POST['menu_ingredients_ru'] ) ? sanitize_textarea_field( wp_unslash( $_POST['menu_ingredients_ru'] ) ) : '' );
		update_post_meta( $post_id, '_menu_ingredients_kz', isset( $_POST['menu_ingredients_kz'] ) ? sanitize_textarea_field( wp_unslash( $_POST['menu_ingredients_kz'] ) ) : '' );
		update_post_meta( $post_id, '_menu_badges', isset( $_POST['menu_badges'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['menu_badges'] ) ) : array() );
	}
);
