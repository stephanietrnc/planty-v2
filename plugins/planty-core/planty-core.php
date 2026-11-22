<?php
/**
 * Plugin Name: Planty — Contenus
 * Description: Goûts, avis, équipe et formulaires (contact et précommande) du site Planty. Les contenus restent si le thème change.
 * Version:     1.0.0
 * Author:      Stéphanie Vétro (stexa)
 * Text Domain: planty-core
 * Requires PHP: 7.4
 *
 * @package planty-core
 */

defined( 'ABSPATH' ) || exit;

define( 'PLANTY_CORE_VERSION', '1.0.0' );
define( 'PLANTY_CORE_DIR', plugin_dir_path( __FILE__ ) );

require PLANTY_CORE_DIR . 'inc/post-types.php';
require PLANTY_CORE_DIR . 'inc/meta.php';
require PLANTY_CORE_DIR . 'inc/forms.php';
require PLANTY_CORE_DIR . 'inc/demo.php';

register_activation_hook(
	__FILE__,
	static function () {
		planty_core_register_post_types();
		flush_rewrite_rules();
	}
);
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
