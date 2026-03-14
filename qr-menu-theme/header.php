<?php
/**
 * Header template.
 *
 * @package ReklamaQRMenu
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="preconnect" href="https://4dsa.ru">
  <link rel="dns-prefetch" href="https://4dsa.ru">
  <link rel="preload" href="<?php echo get_stylesheet_uri(); ?>" as="style">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
