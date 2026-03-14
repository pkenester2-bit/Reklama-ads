<?php
/**
 * Front page QR menu app.
 *
 * @package ReklamaQRMenu
 */

get_header();

$table           = isset( $_GET['table'] ) ? sanitize_text_field( wp_unslash( $_GET['table'] ) ) : '';
$lang            = reklama_qr_get_lang();
$restaurant_name = get_theme_mod( 'qr_restaurant_name', get_bloginfo( 'name' ) );
$slides          = array();

for ( $i = 1; $i <= 5; $i++ ) {
	$slide = get_theme_mod( 'qr_hero_slide_' . $i, '' );
	if ( ! empty( $slide ) ) {
		$slides[] = esc_url( $slide );
	}
}
?>
<main class="qr-shell" id="qrApp" data-table="<?php echo esc_attr( $table ); ?>" data-lang="<?php echo esc_attr( $lang ); ?>">
	<header class="qr-header">
		<div class="qr-brand">
			<div class="qr-brand-main">
				<div class="qr-logo">
					<?php if ( has_custom_logo() ) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<?php echo esc_html( function_exists( 'mb_substr' ) ? mb_substr( $restaurant_name, 0, 2 ) : substr( $restaurant_name, 0, 2 ) ); ?>
					<?php endif; ?>
				</div>

				<strong class="qr-title"><?php echo esc_html( $restaurant_name ); ?></strong>
			</div>

			<div class="qr-header-controls">
				<div class="qr-lang-dropdown">
					<button class="qr-chip qr-lang-toggle" id="langToggleBtn" type="button" aria-expanded="false" aria-controls="langMenu">
						<span id="langToggleLabel"><?php echo esc_html( strtoupper( $lang ) ); ?></span>
						<span class="qr-caret" aria-hidden="true">▾</span>
					</button>

					<div class="qr-lang-menu" id="langMenu">
						<button class="qr-chip qr-lang-option" id="langSwapOption" type="button">
							<?php echo esc_html( 'ru' === $lang ? 'KZ' : 'RU' ); ?>
						</button>
					</div>
				</div>

				<button class="qr-chip qr-theme-toggle" id="themeToggle" type="button" aria-label="Theme toggle">
					<span class="theme-icon theme-icon-moon" aria-hidden="true">☾</span>
					<span class="theme-icon theme-icon-sun" aria-hidden="true">☀</span>
				</button>
			</div>
		</div>
	</header>

	<section class="qr-hero-slider" id="heroSlider" aria-label="Hero Slider">
		<?php if ( ! empty( $slides ) ) : ?>
			<?php foreach ( $slides as $index => $slide ) : ?>
				<div class="qr-hero-slide <?php echo 0 === $index ? 'active' : ''; ?>">
					<img src="<?php echo esc_url( $slide ); ?>" alt="<?php echo esc_attr( $restaurant_name ); ?>">
				</div>
			<?php endforeach; ?>
			<div class="qr-hero-dots" id="heroDots"></div>
		<?php else : ?>
			<div class="qr-hero-fallback" data-i18n="hero_fallback">
				<?php echo esc_html( reklama_qr_t( 'hero_fallback', $lang ) ); ?>
			</div>
		<?php endif; ?>
	</section>

	<section class="qr-dishes-wrap" id="dishesRoot">
		<section id="menuSkeleton" class="qr-grid" aria-hidden="true">
			<div class="qr-skeleton"></div>
			<div class="qr-skeleton"></div>
			<div class="qr-skeleton"></div>
		</section>

		<section id="menuContent"></section>
	</section>

	<section class="qr-menu-strip" id="menuSheet" aria-label="menu strip">
		<div class="qr-menu-strip-scroll" id="categoryBar"></div>
	</section>

	<div class="qr-toast" id="toast"></div>

	<div class="qr-modal" id="dishModal">
		<div class="qr-modal-panel" id="dishModalPanel"></div>
	</div>

	<div class="qr-modal" id="cartModal">
		<div class="qr-modal-panel" id="cartModalPanel"></div>
	</div>

	<div class="qr-modal" id="waiterModal">
		<div class="qr-modal-panel" id="waiterModalPanel"></div>
	</div>

	<div class="qr-modal" id="bookingModal">
		<div class="qr-modal-panel" id="bookingModalPanel"></div>
	</div>

	<div class="qr-modal" id="successModal">
		<div class="qr-modal-panel" id="successModalPanel"></div>
	</div>

	<nav class="qr-bottom-nav" id="bottomNav" aria-label="bottom navigation">
		<button class="qr-bottom-item active" type="button" data-nav="home">
			<span class="qr-bottom-icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" focusable="false">
					<path d="M12 3.2 3.5 10v10.3h6.1v-6.2h4.8v6.2h6.1V10L12 3.2Zm6.3 15.3h-1.7v-6.2H7.4v6.2H5.7v-7.6L12 6l6.3 4.9v7.6Z"/>
				</svg>
			</span>
			<span data-i18n="home"><?php echo esc_html( reklama_qr_t( 'home', $lang ) ); ?></span>
		</button>

		<button class="qr-bottom-item" type="button" data-nav="contacts">
			<span class="qr-bottom-icon">☎</span>
			<span data-i18n="contacts"><?php echo esc_html( reklama_qr_t( 'contacts', $lang ) ); ?></span>
		</button>

		<button class="qr-bottom-item" type="button" data-nav="menu">
			<span class="qr-bottom-icon">☰</span>
			<span data-i18n="menu"><?php echo esc_html( reklama_qr_t( 'menu', $lang ) ); ?></span>
		</button>

		<button class="qr-bottom-item" type="button" data-nav="cart">
			<span class="qr-bottom-icon">🛒</span>
			<span data-i18n="cart"><?php echo esc_html( reklama_qr_t( 'cart', $lang ) ); ?></span>
			<b class="qr-bottom-badge" id="cartCounter">0</b>
		</button>
	</nav>
</main>
<?php
get_footer();