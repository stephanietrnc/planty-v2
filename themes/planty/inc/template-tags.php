<?php
/**
 * Petites fonctions d'affichage.
 *
 * @package planty
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL d'un fichier du thème (images de décor).
 *
 * @param string $file Chemin dans assets/img.
 * @return string
 */
function planty_asset( $file ) {
	return get_template_directory_uri() . '/assets/img/' . $file;
}

/**
 * Texte sur plusieurs lignes : chaque retour à la ligne devient un <br>.
 *
 * @param string $text Texte.
 * @return string HTML échappé.
 */
function planty_lines( $text ) {
	$lines = array_map( 'esc_html', preg_split( '/\r\n|\r|\n/', trim( $text ) ) );
	return implode( '<br>', $lines );
}

/**
 * URL de la première page qui utilise un modèle donné.
 *
 * @param string $template Fichier du modèle (ex. template-commander.php).
 * @return string URL ou chaîne vide.
 */
function planty_template_url( $template ) {
	static $cache = array();
	if ( ! isset( $cache[ $template ] ) ) {
		$pages              = get_pages(
			array(
				'meta_key'   => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery -- une seule requête, mise en cache.
				'meta_value' => $template, // phpcs:ignore WordPress.DB.SlowDBQuery
				'number'     => 1,
			)
		);
		$cache[ $template ] = $pages ? get_permalink( $pages[0] ) : '';
	}
	return $cache[ $template ];
}

/**
 * Logo : celui choisi dans le Customizer, sinon le logo SVG du thème.
 */
function planty_logo() {
	echo '<a class="site-header__logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home">';
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		echo wp_get_attachment_image( $logo_id, 'full', false, array( 'alt' => get_bloginfo( 'name' ), 'class' => 'site-header__img' ) );
	} else {
		echo '<img class="site-header__img" src="' . esc_url( planty_asset( 'logo-planty.svg' ) ) . '" width="201" height="19" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '">';
	}
	echo '</a>';
}

/**
 * Menu principal par défaut (si aucun menu n'est encore choisi).
 */
function planty_menu_fallback() {
	$items = '';
	$meet  = planty_template_url( 'template-nous-rencontrer.php' );
	$order = planty_template_url( 'template-commander.php' );
	if ( $meet ) {
		$items .= '<li class="menu-item"><a href="' . esc_url( $meet ) . '" data-text="' . esc_attr__( 'Nous rencontrer', 'planty' ) . '">' . esc_html__( 'Nous rencontrer', 'planty' ) . '</a></li>';
	}
	if ( $order ) {
		$items .= '<li class="menu-item bouton"><a href="' . esc_url( $order ) . '">' . esc_html__( 'Commander', 'planty' ) . '</a></li>';
	}
	echo '<ul id="menu-principal" class="nav__list">' . $items . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput -- éléments échappés ci-dessus.
}

/**
 * Image d'un contenu (goût, avis, membre) ou rien.
 *
 * @param WP_Post $post  Contenu.
 * @param string  $size  Taille.
 * @param array   $attr  Attributs.
 * @return string
 */
function planty_thumb( $post, $size, $attr = array() ) {
	if ( ! has_post_thumbnail( $post ) ) {
		return '';
	}
	return get_the_post_thumbnail( $post, $size, array_merge( array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ), $attr ) );
}

/**
 * Nom affiché sur la photo d'un goût (champ dédié, sinon le titre).
 *
 * @param WP_Post $gout Goût.
 * @return string HTML échappé.
 */
function planty_taste_label( $gout ) {
	$label = trim( (string) get_post_meta( $gout->ID, 'planty_label', true ) );
	return planty_lines( '' !== $label ? $label : get_the_title( $gout ) );
}

/**
 * Variables CSS d'un goût : sa couleur (--gout) et son délai d'apparition (--d).
 *
 * @param WP_Post $gout  Goût.
 * @param int     $delay Rang pour le délai.
 * @return string
 */
function planty_taste_style( $gout, $delay = 0 ) {
	$color = sanitize_hex_color( (string) get_post_meta( $gout->ID, 'planty_couleur', true ) );
	return '--d: ' . (int) $delay . ';' . ( $color ? ' --gout: ' . $color . ';' : '' );
}

/**
 * Mode démo : message sous le bouton d'envoi.
 * Avec JavaScript, il est caché et n'apparaît qu'au clic sur le bouton (voir main.js) ;
 * sans JavaScript, il reste visible.
 */
function planty_demo_message() {
	if ( planty_mod( 'demo_mode' ) ) {
		echo '<p class="form-notice form-notice--demo" role="status" data-demo-notice>' . esc_html( planty_mod( 'demo_text' ) ) . '</p>';
	}
}

/**
 * Bouton d'envoi d'un formulaire.
 *
 * @param string $label Texte du bouton.
 */
function planty_submit( $label ) {
	echo '<button class="button" type="submit">' . esc_html( $label ) . '</button>';
}

/**
 * Message après envoi d'un formulaire.
 *
 * @param array  $state   État (planty_form_state).
 * @param string $ok_text Texte de réussite.
 */
function planty_form_notice( $state, $ok_text ) {
	if ( 'ok' === $state['status'] ) {
		echo '<p class="form-notice form-notice--ok" role="status">' . esc_html( $ok_text ) . '</p>';
	} elseif ( 'erreur' === $state['status'] ) {
		$text = $state['errors']
			? __( 'Certains champs sont à corriger.', 'planty' )
			: __( 'Le message n\'a pas pu partir. Réessayez dans un instant.', 'planty' );
		echo '<p class="form-notice form-notice--error" role="alert">' . esc_html( $text ) . '</p>';
	}
}

/**
 * Un champ de formulaire (libellé + champ + erreur éventuelle).
 *
 * @param string $form  contact|commande.
 * @param string $name  Nom du champ.
 * @param array  $field [ libellé, type, obligatoire ].
 * @param array  $state État du formulaire.
 */
function planty_field( $form, $name, $field, $state ) {
	list( $label, $type, $required ) = $field;
	$id    = $form . '-' . $name;
	$value = $state['old'][ $name ] ?? '';
	$error = $state['errors'][ $name ] ?? '';
	$auto  = array(
		'nom'         => 'family-name',
		'prenom'      => 'given-name',
		'email'       => 'email',
		'adresse'     => 'street-address',
		'code_postal' => 'postal-code',
		'ville'       => 'address-level2',
	);
	if ( 'contact' === $form && 'nom' === $name ) {
		$auto['nom'] = 'name';
	}
	$attrs = sprintf(
		'id="%1$s" name="%2$s"%3$s%4$s',
		esc_attr( $id ),
		esc_attr( $name ),
		$required ? ' required' : '',
		$error ? ' aria-invalid="true" aria-describedby="' . esc_attr( $id ) . '-erreur"' : ''
	);
	echo '<div class="field' . ( 'textarea' === $type ? ' field--area' : '' ) . '">';
	echo '<label class="field__label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
	if ( 'textarea' === $type ) {
		echo '<textarea class="field__input" ' . $attrs . ' rows="8">' . esc_textarea( $value ) . '</textarea>'; // phpcs:ignore WordPress.Security.EscapeOutput -- attributs échappés.
	} else {
		$html_type = 'email' === $type ? 'email' : 'text';
		$extra     = 'postal' === $type ? ' inputmode="numeric" pattern="[0-9]{5}" maxlength="5"' : '';
		$extra    .= isset( $auto[ $name ] ) ? ' autocomplete="' . esc_attr( $auto[ $name ] ) . '"' : '';
		echo '<input class="field__input" type="' . esc_attr( $html_type ) . '" ' . $attrs . $extra . ' value="' . esc_attr( $value ) . '">'; // phpcs:ignore WordPress.Security.EscapeOutput -- attributs échappés.
	}
	if ( $error ) {
		echo '<p class="field__error" id="' . esc_attr( $id ) . '-erreur">' . esc_html( $error ) . '</p>';
	}
	echo '</div>';
}
