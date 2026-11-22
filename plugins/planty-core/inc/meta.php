<?php
/**
 * Champs complémentaires des contenus :
 * - Goût : nom affiché sur la photo, couleur, prix ;
 * - Avis : texte ;
 * - Membre : rôle.
 *
 * @package planty-core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Définition des champs : type => [ clé => [ libellé, aide, genre ] ].
 * Genres : text, textarea, color, price.
 *
 * @return array
 */
function planty_core_fields() {
	return array(
		'planty_gout'   => array(
			'planty_label'   => array( __( 'Nom affiché sur la photo', 'planty-core' ), __( 'Facultatif. Pour couper un nom long, passez à la ligne (ex. « Pample » puis « mousse »). Vide = le titre.', 'planty-core' ), 'textarea' ),
			'planty_couleur' => array( __( 'Couleur du goût', 'planty-core' ), __( 'Utilisée au survol de la photo et dans le récapitulatif de commande.', 'planty-core' ), 'color' ),
			'planty_prix'    => array( __( 'Prix d\'une canette (€)', 'planty-core' ), __( 'Facultatif. Ex. 2,50. Si tous les goûts ont un prix, le total s\'affiche sur la page Commander.', 'planty-core' ), 'price' ),
		),
		'planty_avis'   => array(
			'planty_texte' => array( __( 'Avis', 'planty-core' ), __( 'Le texte de l\'avis, sans guillemets.', 'planty-core' ), 'textarea' ),
		),
		'planty_membre' => array(
			'planty_role' => array( __( 'Rôle', 'planty-core' ), __( 'Ex. CEO, Nutritionniste…', 'planty-core' ), 'text' ),
		),
	);
}

/**
 * Nettoie une valeur selon le genre du champ.
 *
 * @param string $value Valeur brute.
 * @param string $kind  Genre.
 * @return string
 */
function planty_core_sanitize( $value, $kind ) {
	switch ( $kind ) {
		case 'textarea':
			return sanitize_textarea_field( $value );
		case 'color':
			return (string) sanitize_hex_color( $value );
		case 'price':
			$value = str_replace( array( ' ', '€', ',' ), array( '', '', '.' ), (string) $value );
			return is_numeric( $value ) && (float) $value >= 0 ? number_format( (float) $value, 2, '.', '' ) : '';
		default:
			return sanitize_text_field( $value );
	}
}

/**
 * Enregistre les champs (disponibles aussi dans l'API REST).
 */
function planty_core_register_meta() {
	foreach ( planty_core_fields() as $type => $fields ) {
		foreach ( $fields as $key => $field ) {
			$kind = $field[2];
			register_post_meta(
				$type,
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => static fn( $v ) => planty_core_sanitize( $v, $kind ),
					'auth_callback'     => static fn() => current_user_can( 'edit_posts' ),
				)
			);
		}
	}
}
add_action( 'init', 'planty_core_register_meta' );

/**
 * Ajoute une boîte « Informations » sous le titre.
 */
function planty_core_add_boxes() {
	foreach ( planty_core_fields() as $type => $fields ) {
		add_meta_box( 'planty-infos', __( 'Informations', 'planty-core' ), 'planty_core_render_box', $type, 'normal', 'high', $fields );
	}
}
add_action( 'add_meta_boxes', 'planty_core_add_boxes' );

/**
 * Affiche les champs.
 *
 * @param WP_Post $post Contenu.
 * @param array   $box  Boîte (args = champs).
 */
function planty_core_render_box( $post, $box ) {
	wp_nonce_field( 'planty_core_meta', 'planty_core_meta_nonce' );
	foreach ( $box['args'] as $key => $field ) {
		list( $label, $help, $kind ) = $field;
		$value = (string) get_post_meta( $post->ID, $key, true );
		if ( 'price' === $kind && '' !== $value ) {
			$value = str_replace( '.', ',', $value );
		}
		echo '<p><label for="' . esc_attr( $key ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
		if ( 'textarea' === $kind ) {
			echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="3" style="width:100%">' . esc_textarea( $value ) . '</textarea>';
		} elseif ( 'color' === $kind ) {
			echo '<input type="color" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ? $value : '#3b8e46' ) . '">';
		} else {
			$extra = 'price' === $kind ? ' inputmode="decimal" style="width:8em"' : ' style="width:100%"';
			echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '"' . $extra . '>'; // phpcs:ignore WordPress.Security.EscapeOutput -- attributs fixes.
		}
		echo '<br><span class="description">' . esc_html( $help ) . '</span></p>';
	}
}

/**
 * Enregistre les champs.
 *
 * @param int $post_id Identifiant.
 */
function planty_core_save_meta( $post_id ) {
	if ( ! isset( $_POST['planty_core_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['planty_core_meta_nonce'] ), 'planty_core_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$fields = planty_core_fields();
	$type   = get_post_type( $post_id );
	if ( ! isset( $fields[ $type ] ) ) {
		return;
	}
	foreach ( $fields[ $type ] as $key => $field ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, planty_core_sanitize( wp_unslash( $_POST[ $key ] ), $field[2] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nettoyé dans planty_core_sanitize().
		}
	}
}
add_action( 'save_post', 'planty_core_save_meta' );

/**
 * Prix d'un goût en nombre (null si non renseigné).
 *
 * @param int $id Goût.
 * @return float|null
 */
function planty_price( $id ) {
	$price = (string) get_post_meta( $id, 'planty_prix', true );
	return '' === $price ? null : (float) $price;
}

/**
 * Prix au format français (2,50 €).
 *
 * @param float $amount Montant.
 * @return string
 */
function planty_format_price( $amount ) {
	return number_format( $amount, 2, ',', ' ' ) . ' €';
}
