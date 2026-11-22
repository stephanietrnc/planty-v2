<?php
/**
 * Thème Planty : réglages, styles et scripts.
 *
 * @package planty
 */

defined( 'ABSPATH' ) || exit;

define( 'PLANTY_VERSION', '1.0.0' );

require get_template_directory() . '/inc/defaults.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/customizer.php';

/**
 * Fonctions du thème.
 */
function planty_setup() {
	load_theme_textdomain( 'planty', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 19,
			'width'       => 201,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	register_nav_menus(
		array(
			'principal' => __( 'Menu principal', 'planty' ),
			'pied'      => __( 'Pied de page', 'planty' ),
		)
	);
	add_image_size( 'planty-gout', 786, 524, true );
	add_image_size( 'planty-portrait', 334, 334, true );
}
add_action( 'after_setup_theme', 'planty_setup' );

/**
 * Styles et scripts.
 */
function planty_assets() {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();
	wp_enqueue_style( 'planty', $uri . '/assets/css/main.css', array(), (string) filemtime( $dir . '/assets/css/main.css' ) );
	wp_enqueue_script( 'planty', $uri . '/assets/js/main.js', array(), (string) filemtime( $dir . '/assets/js/main.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
}
add_action( 'wp_enqueue_scripts', 'planty_assets' );

/**
 * Allège le <head> : retire ce dont le site n'a pas besoin
 * (lien XML-RPC, lien court, version de WordPress, script des émojis).
 */
function planty_clean_head() {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'planty_clean_head' );

/**
 * Précharge les polices utilisées au-dessus de la ligne de flottaison, et signale que JS est actif.
 */
function planty_head() {
	$fonts = get_template_directory_uri() . '/assets/fonts/';
	foreach ( array( 'syne-latin-800-normal.woff2', 'syne-latin-400-normal.woff2' ) as $font ) {
		echo '<link rel="preload" href="' . esc_url( $fonts . $font ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	}
	echo "<script>document.documentElement.classList.add('js');</script>\n";
}
add_action( 'wp_head', 'planty_head', 1 );

/**
 * Classe « est-actif » sur l'élément de menu de la page courante (style du survol Figma).
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function planty_menu_classes( $classes ) {
	if ( array_intersect( array( 'current-menu-item', 'current_page_item' ), $classes ) ) {
		$classes[] = 'est-actif';
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'planty_menu_classes' );

/**
 * Copie du texte du lien (data-text) : sert à réserver la place du texte en gras au survol.
 *
 * @param array   $atts Attributs du lien.
 * @param WP_Post $item Élément de menu.
 * @return array
 */
function planty_menu_link_text( $atts, $item ) {
	$atts['data-text'] = wp_strip_all_tags( $item->title );
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'planty_menu_link_text', 10, 2 );

/**
 * Pas d'extension Planty — Contenus : un message clair dans l'admin.
 */
function planty_require_core() {
	if ( ! function_exists( 'planty_get' ) && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Le thème Planty a besoin de l\'extension « Planty — Contenus » pour afficher les goûts, les avis, l\'équipe et les formulaires.', 'planty' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'planty_require_core' );
