<?php
/**
 * Contenu de démo : un clic pour remplir le site avec les contenus de la maquette
 * (pages, menus, goûts, avis, équipe et leurs photos). Ne fait rien si des goûts existent déjà.
 *
 * @package planty-core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bandeau avec le bouton, tant qu'il n'y a aucun goût.
 */
function planty_demo_notice() {
	if ( ! current_user_can( 'manage_options' ) || get_posts( array( 'post_type' => 'planty_gout', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) ) ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=planty_demo' ), 'planty_demo' );
	echo '<div class="notice notice-info"><p><strong>Planty</strong> : le site est vide. ';
	echo '<a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Installer le contenu de la maquette', 'planty-core' ) . '</a></p></div>';
}
add_action( 'admin_notices', 'planty_demo_notice' );

/**
 * Importe une image du dossier demo/ dans la médiathèque.
 *
 * @param string $file Nom du fichier.
 * @param string $alt  Texte alternatif.
 * @return int ID de l'image (0 si échec).
 */
function planty_demo_image( $file, $alt = '' ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( $file );
	if ( ! $tmp || ! copy( PLANTY_CORE_DIR . 'demo/' . $file, $tmp ) ) {
		return 0;
	}
	$id = media_handle_sideload( array( 'name' => $file, 'tmp_name' => $tmp ), 0 );
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}
	if ( $alt ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	}
	return (int) $id;
}

/**
 * Crée un contenu avec photo et champ.
 *
 * @param string $type  Type.
 * @param string $title Titre.
 * @param int    $order Ordre.
 * @param string $image Fichier image.
 * @param array  $meta  Champs.
 */
function planty_demo_item( $type, $title, $order, $image, $meta = array() ) {
	$id = wp_insert_post(
		array(
			'post_type'   => $type,
			'post_title'  => $title,
			'post_status' => 'publish',
			'menu_order'  => $order,
		)
	);
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}
	foreach ( $meta as $key => $value ) {
		update_post_meta( $id, $key, $value );
	}
	$img = planty_demo_image( $image );
	if ( $img ) {
		set_post_thumbnail( $id, $img );
	}
}

/**
 * Page (retrouvée par son slug ou créée).
 *
 * @param string $title    Titre.
 * @param string $slug     Slug.
 * @param string $content  Contenu.
 * @param string $template Modèle.
 * @return int
 */
function planty_demo_page( $title, $slug, $content = '', $template = '' ) {
	$page = get_page_by_path( $slug );
	$id   = $page ? $page->ID : wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => $content,
			'post_status'  => 'publish',
		)
	);
	if ( $template && $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_wp_page_template', $template );
	}
	return (int) $id;
}

/**
 * Lance l'import.
 */
function planty_demo_run() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'planty-core' ) );
	}
	check_admin_referer( 'planty_demo' );

	if ( ! get_posts( array( 'post_type' => 'planty_gout', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) ) ) {
		// Goûts.
		planty_demo_item( 'planty_gout', 'Fraise', 1, 'gout-fraise.webp', array( 'planty_couleur' => '#d8414f' ) );
		planty_demo_item( 'planty_gout', 'Pamplemousse', 2, 'gout-pamplemousse.webp', array( 'planty_label' => "Pample\nmousse", 'planty_couleur' => '#e15319' ) );
		planty_demo_item( 'planty_gout', 'Framboise', 3, 'gout-framboise.webp', array( 'planty_label' => "Fram\nboise", 'planty_couleur' => '#c2307a' ) );
		planty_demo_item( 'planty_gout', 'Citron', 4, 'gout-citron.webp', array( 'planty_couleur' => '#f2c230' ) );
		// Avis.
		planty_demo_item( 'planty_avis', 'Fatiha', 1, 'fatiha.webp', array( 'planty_texte' => 'La meilleure boisson énergisante disponible sur le marché !' ) );
		planty_demo_item( 'planty_avis', 'Véro', 2, 'vero.webp', array( 'planty_texte' => 'Frais & fruité c’est parfait pour accompagner le goûter.' ) );
		planty_demo_item( 'planty_avis', 'Marc', 3, 'marc.webp', array( 'planty_texte' => 'Un boost d’énergie sans produits chimiques, un vrai plus dans ma journée.' ) );
		// Équipe.
		planty_demo_item( 'planty_membre', 'Mégane', 1, 'megane.webp', array( 'planty_role' => 'CEO' ) );
		planty_demo_item( 'planty_membre', 'Brooke', 2, 'brooke.webp', array( 'planty_role' => 'Nutritionniste' ) );
		planty_demo_item( 'planty_membre', 'Sylvie', 3, 'sylvie.webp', array( 'planty_role' => 'Mixologue' ) );
	}

	// Pages.
	$home  = planty_demo_page( 'Accueil', 'accueil' );
	$meet  = planty_demo_page( 'Nous rencontrer', 'nous-rencontrer', "<!-- wp:paragraph -->\n<p>Chez Planty nous sommes tous passionnés par le bien-être, et ça se retrouve dans nos boissons ! Notre start-up s’est construite au fur et à mesure de rencontres entre amoureux des plantes.</p>\n<!-- /wp:paragraph -->", 'template-nous-rencontrer.php' );
	$order = planty_demo_page( 'Commander', 'commander', '', 'template-commander.php' );
	$legal = planty_demo_page( 'Mentions légales', 'mentions-legales', "<!-- wp:paragraph -->\n<p>À compléter.</p>\n<!-- /wp:paragraph -->" );
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home );

	// Menus.
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( empty( $locations['principal'] ) ) {
		$menu = wp_create_nav_menu( 'Menu principal' );
		if ( ! is_wp_error( $menu ) ) {
			wp_update_nav_menu_item( $menu, 0, array( 'menu-item-object-id' => $meet, 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
			wp_update_nav_menu_item( $menu, 0, array( 'menu-item-object-id' => $order, 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish', 'menu-item-classes' => 'bouton' ) );
			$locations['principal'] = $menu;
		}
	}
	if ( empty( $locations['pied'] ) ) {
		$menu = wp_create_nav_menu( 'Pied de page' );
		if ( ! is_wp_error( $menu ) ) {
			wp_update_nav_menu_item( $menu, 0, array( 'menu-item-object-id' => $legal, 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
			$locations['pied'] = $menu;
		}
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	wp_safe_redirect( home_url( '/' ) );
	exit;
}
add_action( 'admin_post_planty_demo', 'planty_demo_run' );
