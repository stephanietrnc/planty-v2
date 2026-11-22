<?php
/**
 * Formulaires de contact et de précommande, sans extension tierce.
 * Envoi par wp_mail() à l'adresse réglée dans Apparence > Personnaliser > Formulaires.
 * Le thème affiche les champs ; ce fichier les vérifie, envoie le mail et renvoie sur la page.
 *
 * @package planty-core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adresse qui reçoit les messages.
 *
 * @return string
 */
function planty_form_recipient() {
	$email = sanitize_email( (string) get_theme_mod( 'planty_form_email', 'planty.drinks@gmail.com' ) );
	return is_email( $email ) ? $email : get_option( 'admin_email' );
}

/**
 * Réglage de l'adresse dans le Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function planty_core_customizer( $wp_customize ) {
	$wp_customize->add_section(
		'planty_forms',
		array(
			'title'    => __( 'Formulaires', 'planty-core' ),
			'priority' => 160,
		)
	);
	$wp_customize->add_setting(
		'planty_form_email',
		array(
			'default'           => 'planty.drinks@gmail.com',
			'sanitize_callback' => 'sanitize_email',
		)
	);
	$wp_customize->add_control(
		'planty_form_email',
		array(
			'label'       => __( 'Adresse qui reçoit les messages et les commandes', 'planty-core' ),
			'section'     => 'planty_forms',
			'type'        => 'email',
		)
	);
}
add_action( 'customize_register', 'planty_core_customizer' );

/**
 * Champs attendus par formulaire : nom => [ libellé, type, obligatoire ].
 *
 * @param string $form contact|commande.
 * @return array
 */
function planty_form_fields( $form ) {
	if ( 'contact' === $form ) {
		return array(
			'nom'     => array( __( 'Nom', 'planty-core' ), 'text', true ),
			'email'   => array( __( 'E-mail', 'planty-core' ), 'email', true ),
			'message' => array( __( 'Message', 'planty-core' ), 'textarea', true ),
		);
	}
	return array(
		'nom'         => array( __( 'Nom', 'planty-core' ), 'text', true ),
		'prenom'      => array( __( 'Prénom', 'planty-core' ), 'text', true ),
		'email'       => array( __( 'E-mail', 'planty-core' ), 'email', true ),
		'adresse'     => array( __( 'Adresse de livraison', 'planty-core' ), 'text', true ),
		'code_postal' => array( __( 'Code postal', 'planty-core' ), 'postal', true ),
		'ville'       => array( __( 'Ville', 'planty-core' ), 'text', true ),
	);
}

/**
 * Champs cachés à mettre dans chaque formulaire (action, jeton, piège à robots).
 *
 * @param string $form contact|commande.
 */
function planty_form_hidden( $form ) {
	echo '<input type="hidden" name="action" value="planty_' . esc_attr( $form ) . '">';
	echo '<input type="hidden" name="planty_back" value="' . esc_url( get_permalink() ) . '">';
	wp_nonce_field( 'planty_' . $form, 'planty_nonce' );
	// Piège à robots : invisible pour les humains, rempli par les robots.
	echo '<div class="hp" aria-hidden="true"><label>Site web <input type="text" name="site_web" tabindex="-1" autocomplete="off"></label></div>';
}

/**
 * État du formulaire après envoi (succès, erreurs, valeurs saisies).
 *
 * @param string $form contact|commande.
 * @return array{status:string,errors:array,old:array}
 */
function planty_form_state( $form ) {
	$state = array( 'status' => '', 'errors' => array(), 'old' => array() );
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- simple lecture de l'état affiché.
	if ( empty( $_GET['envoi'] ) || empty( $_GET['form'] ) || $form !== sanitize_key( $_GET['form'] ) ) {
		return $state;
	}
	$state['status'] = 'ok' === sanitize_key( $_GET['envoi'] ) ? 'ok' : 'erreur';
	if ( 'erreur' === $state['status'] && ! empty( $_GET['t'] ) ) {
		$saved = get_transient( 'planty_form_' . sanitize_key( $_GET['t'] ) );
		if ( is_array( $saved ) ) {
			$state['errors'] = $saved['errors'];
			$state['old']    = $saved['old'];
		}
	}
	// phpcs:enable
	return $state;
}

/**
 * Traite un envoi.
 *
 * @param string $form contact|commande.
 */
function planty_form_handle( $form ) {
	$back = isset( $_POST['planty_back'] ) ? esc_url_raw( wp_unslash( $_POST['planty_back'] ) ) : home_url( '/' );
	$back = wp_validate_redirect( $back, home_url( '/' ) );

	if ( ! isset( $_POST['planty_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['planty_nonce'] ), 'planty_' . $form ) ) {
		wp_safe_redirect( add_query_arg( array( 'envoi' => 'erreur', 'form' => $form ), $back ) . '#' . $form );
		exit;
	}
	// Robot : on fait comme si tout allait bien.
	if ( ! empty( $_POST['site_web'] ) ) {
		wp_safe_redirect( add_query_arg( array( 'envoi' => 'ok', 'form' => $form ), $back ) . '#' . $form );
		exit;
	}

	$old    = array();
	$errors = array();
	foreach ( planty_form_fields( $form ) as $name => $field ) {
		$raw          = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nettoyé juste en dessous.
		$value        = 'textarea' === $field[1] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		$old[ $name ] = $value;
		if ( $field[2] && '' === trim( $value ) ) {
			$errors[ $name ] = __( 'Ce champ est obligatoire.', 'planty-core' );
		} elseif ( 'email' === $field[1] && ! is_email( $value ) ) {
			$errors[ $name ] = __( 'Cette adresse e-mail n\'est pas valide.', 'planty-core' );
		} elseif ( 'postal' === $field[1] && ! preg_match( '/^\d{5}$/', $value ) ) {
			$errors[ $name ] = __( 'Le code postal doit faire 5 chiffres.', 'planty-core' );
		}
	}

	$lines = array();
	if ( 'commande' === $form ) {
		$qty   = isset( $_POST['quantite'] ) && is_array( $_POST['quantite'] ) ? array_map( 'absint', wp_unslash( $_POST['quantite'] ) ) : array();
		$total  = 0;
		$amount = 0.0;
		$priced = true; // Le total en euros n'est donné que si chaque goût commandé a un prix.
		foreach ( planty_get( 'planty_gout' ) as $gout ) {
			$n = min( 99, $qty[ $gout->ID ] ?? 0 );
			$old['quantite'][ $gout->ID ] = $n;
			if ( $n > 0 ) {
				$price = planty_price( $gout->ID );
				if ( null === $price ) {
					$priced  = false;
					$lines[] = sprintf( '- %s : %d', get_the_title( $gout ), $n );
				} else {
					$amount += $n * $price;
					$lines[] = sprintf( '- %s : %d × %s', get_the_title( $gout ), $n, planty_format_price( $price ) );
				}
				$total += $n;
			}
		}
		$lines[] = sprintf( 'Total : %d canette%s%s', $total, $total > 1 ? 's' : '', $priced && $total ? ' – ' . planty_format_price( $amount ) : '' );
		if ( 0 === $total ) {
			$errors['quantite'] = __( 'Choisissez au moins une canette.', 'planty-core' );
		}
	}

	if ( $errors ) {
		$token = wp_generate_password( 12, false );
		set_transient( 'planty_form_' . strtolower( $token ), array( 'errors' => $errors, 'old' => $old ), 10 * MINUTE_IN_SECONDS );
		wp_safe_redirect( add_query_arg( array( 'envoi' => 'erreur', 'form' => $form, 't' => strtolower( $token ) ), $back ) . '#' . $form );
		exit;
	}

	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	if ( 'contact' === $form ) {
		$subject = sprintf( '[%s] Message de %s', $site, $old['nom'] );
		$body    = sprintf( "Nom : %s\nE-mail : %s\n\n%s\n", $old['nom'], $old['email'], $old['message'] );
	} else {
		$subject = sprintf( '[%s] Précommande de %s %s', $site, $old['prenom'], $old['nom'] );
		$body    = "Commande :\n" . implode( "\n", $lines ) . "\n\n"
			. sprintf( "Nom : %s\nPrénom : %s\nE-mail : %s\n\nLivraison :\n%s\n%s %s\n", $old['nom'], $old['prenom'], $old['email'], $old['adresse'], $old['code_postal'], $old['ville'] );
	}
	$sent = wp_mail( planty_form_recipient(), $subject, $body, array( 'Reply-To: ' . $old['email'] ) );

	wp_safe_redirect( add_query_arg( array( 'envoi' => $sent ? 'ok' : 'erreur', 'form' => $form ), $back ) . '#' . $form );
	exit;
}

foreach ( array( 'contact', 'commande' ) as $planty_form ) {
	add_action( 'admin_post_nopriv_planty_' . $planty_form, static fn() => planty_form_handle( $planty_form ) );
	add_action( 'admin_post_planty_' . $planty_form, static fn() => planty_form_handle( $planty_form ) );
}
unset( $planty_form );
