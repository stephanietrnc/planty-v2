<?php
/**
 * Apparence > Personnaliser > Planty : les textes des pages, l'image de la canette.
 *
 * @package planty
 */

defined( 'ABSPATH' ) || exit;

/**
 * Déclare les réglages.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function planty_customize_register( $wp_customize ) {
	$d = planty_defaults();

	$wp_customize->add_panel(
		'planty',
		array(
			'title'    => __( 'Planty', 'planty' ),
			'priority' => 30,
		)
	);

	$sections = array(
		'planty_home'    => array(
			'title'  => __( 'Accueil', 'planty' ),
			'fields' => array(
				'hero_title'    => array( __( 'Grand titre (une ligne par retour)', 'planty' ), 'textarea' ),
				'hero_can'      => array( __( 'Image de la canette (PNG ou WebP détouré)', 'planty' ), 'image' ),
				'hero_can_alt'  => array( __( 'Description de la canette (lecteurs d\'écran)', 'planty' ), 'text' ),
				'energy_title'  => array( __( 'Titre « L\'énergie des plantes »', 'planty' ), 'text' ),
				'energy_text'   => array( __( 'Texte de présentation', 'planty' ), 'textarea' ),
				'tastes_title'  => array( __( 'Titre des goûts', 'planty' ), 'text' ),
				'tastes_text'   => array( __( 'Texte sous le titre des goûts', 'planty' ), 'text' ),
				'tastes_button' => array( __( 'Bouton sous les goûts', 'planty' ), 'text' ),
				'reviews_title' => array( __( 'Titre des avis', 'planty' ), 'text' ),
			),
		),
		'planty_meet'    => array(
			'title'       => __( 'Nous rencontrer', 'planty' ),
			'description' => __( 'Le grand titre et le texte d\'introduction sont ceux de la page (Pages > Nous rencontrer). L\'équipe se gère dans le menu « Équipe ».', 'planty' ),
			'fields'      => array(
				'team_title'     => array( __( 'Titre de l\'équipe', 'planty' ), 'text' ),
				'contact_title'  => array( __( 'Titre du formulaire', 'planty' ), 'text' ),
				'contact_button' => array( __( 'Bouton du formulaire', 'planty' ), 'text' ),
				'contact_ok'     => array( __( 'Message après envoi', 'planty' ), 'textarea' ),
			),
		),
		'planty_order'   => array(
			'title'       => __( 'Commander', 'planty' ),
			'description' => __( 'Les goûts proposés viennent du menu « Goûts ».', 'planty' ),
			'fields'      => array(
				'order_title'      => array( __( 'Titre « Votre commande »', 'planty' ), 'text' ),
				'order_info_title' => array( __( 'Titre « Vos informations »', 'planty' ), 'text' ),
				'order_ship_title' => array( __( 'Titre « Livraison »', 'planty' ), 'text' ),
				'order_button'     => array( __( 'Bouton du formulaire', 'planty' ), 'text' ),
				'order_ok'         => array( __( 'Message après envoi', 'planty' ), 'textarea' ),
			),
		),
		'planty_footer'  => array(
			'title'  => __( 'Pied de page', 'planty' ),
			'fields' => array(
				'footer_cans' => array( __( 'Afficher la rangée de canettes', 'planty' ), 'checkbox' ),
			),
		),
		'planty_demo'    => array(
			'title'       => __( 'Mode démo', 'planty' ),
			'description' => __( 'À cocher avant un export statique (Simply Static) : les formulaires affichent un message et n\'envoient rien. À décocher ensuite.', 'planty' ),
			'fields'      => array(
				'demo_mode' => array( __( 'Activer le mode démo', 'planty' ), 'checkbox' ),
				'demo_text' => array( __( 'Message affiché sur les formulaires', 'planty' ), 'text' ),
			),
		),
	);

	$priority = 10;
	foreach ( $sections as $section_id => $section ) {
		$wp_customize->add_section(
			$section_id,
			array(
				'title'       => $section['title'],
				'description' => $section['description'] ?? '',
				'panel'       => 'planty',
				'priority'    => $priority,
			)
		);
		$priority += 10;

		foreach ( $section['fields'] as $key => $field ) {
			list( $label, $type ) = $field;
			$sanitize             = array(
				'text'     => 'sanitize_text_field',
				'textarea' => 'sanitize_textarea_field',
				'image'    => 'absint',
				'checkbox' => static fn( $v ) => $v ? '1' : '',
			);
			$wp_customize->add_setting(
				$key,
				array(
					'default'           => $d[ $key ] ?? '',
					'sanitize_callback' => $sanitize[ $type ],
					'transport'         => 'refresh',
				)
			);
			if ( 'image' === $type ) {
				$wp_customize->add_control(
					new WP_Customize_Media_Control(
						$wp_customize,
						$key,
						array(
							'label'     => $label,
							'section'   => $section_id,
							'mime_type' => 'image',
						)
					)
				);
			} else {
				$wp_customize->add_control(
					$key,
					array(
						'label'   => $label,
						'section' => $section_id,
						'type'    => $type,
					)
				);
			}
		}
	}
}
add_action( 'customize_register', 'planty_customize_register' );
