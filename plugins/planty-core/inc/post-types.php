<?php
/**
 * Types de contenu : Goûts, Avis, Équipe.
 * Le titre = le nom, l'image mise en avant = la photo, l'ordre = « Attributs > Ordre ».
 *
 * @package planty-core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Déclare les trois types de contenu.
 */
function planty_core_register_post_types() {
	$common = array(
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => true,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'capability_type'     => 'post',
	);

	register_post_type(
		'planty_gout',
		$common + array(
			'labels'        => array(
				'name'               => __( 'Goûts', 'planty-core' ),
				'singular_name'      => __( 'Goût', 'planty-core' ),
				'add_new'            => __( 'Ajouter un goût', 'planty-core' ),
				'add_new_item'       => __( 'Ajouter un goût', 'planty-core' ),
				'edit_item'          => __( 'Modifier le goût', 'planty-core' ),
				'all_items'          => __( 'Tous les goûts', 'planty-core' ),
				'featured_image'     => __( 'Photo du goût', 'planty-core' ),
				'set_featured_image' => __( 'Choisir la photo', 'planty-core' ),
				'not_found'          => __( 'Aucun goût.', 'planty-core' ),
			),
			'menu_icon'     => 'dashicons-carrot',
			'menu_position' => 20,
			'supports'      => array( 'title', 'thumbnail', 'page-attributes', 'custom-fields' ),
		)
	);

	register_post_type(
		'planty_avis',
		$common + array(
			'labels'        => array(
				'name'               => __( 'Avis', 'planty-core' ),
				'singular_name'      => __( 'Avis', 'planty-core' ),
				'add_new'            => __( 'Ajouter un avis', 'planty-core' ),
				'add_new_item'       => __( 'Ajouter un avis', 'planty-core' ),
				'edit_item'          => __( 'Modifier l\'avis', 'planty-core' ),
				'all_items'          => __( 'Tous les avis', 'planty-core' ),
				'featured_image'     => __( 'Photo de la personne', 'planty-core' ),
				'set_featured_image' => __( 'Choisir la photo', 'planty-core' ),
				'not_found'          => __( 'Aucun avis.', 'planty-core' ),
			),
			'menu_icon'     => 'dashicons-format-quote',
			'menu_position' => 21,
			'supports'      => array( 'title', 'thumbnail', 'page-attributes', 'custom-fields' ),
		)
	);

	register_post_type(
		'planty_membre',
		$common + array(
			'labels'        => array(
				'name'               => __( 'Équipe', 'planty-core' ),
				'singular_name'      => __( 'Membre', 'planty-core' ),
				'add_new'            => __( 'Ajouter une personne', 'planty-core' ),
				'add_new_item'       => __( 'Ajouter une personne', 'planty-core' ),
				'edit_item'          => __( 'Modifier la personne', 'planty-core' ),
				'all_items'          => __( 'Toute l\'équipe', 'planty-core' ),
				'featured_image'     => __( 'Photo', 'planty-core' ),
				'set_featured_image' => __( 'Choisir la photo', 'planty-core' ),
				'not_found'          => __( 'Personne pour l\'instant.', 'planty-core' ),
			),
			'menu_icon'     => 'dashicons-groups',
			'menu_position' => 22,
			'supports'      => array( 'title', 'thumbnail', 'page-attributes', 'custom-fields' ),
		)
	);
}
add_action( 'init', 'planty_core_register_post_types' );

/**
 * Placeholder du champ titre selon le type.
 *
 * @param string  $text Texte par défaut.
 * @param WP_Post $post Contenu.
 * @return string
 */
function planty_core_title_placeholder( $text, $post ) {
	$map = array(
		'planty_gout'   => __( 'Nom du goût (ex. Fraise)', 'planty-core' ),
		'planty_avis'   => __( 'Prénom (ex. Fatiha)', 'planty-core' ),
		'planty_membre' => __( 'Prénom (ex. Mégane)', 'planty-core' ),
	);
	return $map[ $post->post_type ] ?? $text;
}
add_filter( 'enter_title_here', 'planty_core_title_placeholder', 10, 2 );

/**
 * Liste triée d'un type (ordre puis date).
 *
 * @param string $type Type de contenu.
 * @return WP_Post[]
 */
function planty_get( $type ) {
	return get_posts(
		array(
			'post_type'      => $type,
			'posts_per_page' => 24,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			'no_found_rows'  => true,
		)
	);
}

/**
 * Colonnes de l'admin : photo + ordre, pour s'y retrouver d'un coup d'œil.
 */
function planty_core_admin_columns() {
	foreach ( array( 'planty_gout', 'planty_avis', 'planty_membre' ) as $type ) {
		add_filter(
			"manage_{$type}_posts_columns",
			static function ( $cols ) {
				$new = array();
				foreach ( $cols as $key => $label ) {
					if ( 'title' === $key ) {
						$new['planty_photo'] = __( 'Photo', 'planty-core' );
					}
					$new[ $key ] = $label;
				}
				$new['menu_order'] = __( 'Ordre', 'planty-core' );
				unset( $new['date'] );
				return $new;
			}
		);
		add_action(
			"manage_{$type}_posts_custom_column",
			static function ( $col, $post_id ) {
				if ( 'planty_photo' === $col ) {
					echo get_the_post_thumbnail( $post_id, array( 48, 48 ), array( 'style' => 'width:48px;height:48px;object-fit:cover;border-radius:6px' ) );
				} elseif ( 'menu_order' === $col ) {
					echo (int) get_post_field( 'menu_order', $post_id );
				}
			},
			10,
			2
		);
	}
}
add_action( 'admin_init', 'planty_core_admin_columns' );
