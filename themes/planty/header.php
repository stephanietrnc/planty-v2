<?php
/**
 * En-tête : logo + menu (le dernier élément avec la classe « bouton » devient le bouton rose).
 *
 * @package planty
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#contenu"><?php esc_html_e( 'Aller au contenu', 'planty' ); ?></a>

<header class="site-header">
	<?php planty_logo(); ?>
	<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="nav-principal">
		<span class="nav-toggle__bar" aria-hidden="true"></span>
		<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'planty' ); ?></span>
	</button>
	<nav class="nav" id="nav-principal" aria-label="<?php esc_attr_e( 'Menu principal', 'planty' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'principal',
				'container'      => false,
				'menu_class'     => 'nav__list',
				'depth'          => 1,
				'fallback_cb'    => 'planty_menu_fallback',
			)
		);
		?>
	</nav>
</header>

<main id="contenu" class="site-main">
