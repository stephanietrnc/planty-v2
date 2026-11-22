<?php
/**
 * Textes par défaut (ceux de la maquette). Modifiables dans Apparence > Personnaliser.
 *
 * @package planty
 */

defined( 'ABSPATH' ) || exit;

/**
 * Valeurs par défaut des réglages du thème.
 *
 * @return array<string,string>
 */
function planty_defaults() {
	return array(
		// Accueil.
		'hero_title'        => "Boisson énergisante\n100% naturelle",
		'hero_can'          => '',
		'hero_can_alt'      => 'Canette de la boisson énergisante Planty',
		'energy_title'      => 'L’énergie des plantes',
		'energy_text'       => 'Planty, c’est la première boisson énergisante composée à 100 % de produits naturels. Avec son goût frais et fruité, retrouvez votre énergie grâce aux plantes riches en vitamine B2, à n’importe quel moment de la journée.',
		'tastes_title'      => 'Les goûts',
		'tastes_text'       => 'Nous avons une saveur pour chaque moment de votre journée.',
		'tastes_button'     => 'Commander',
		'reviews_title'     => 'Ce qu’ils en pensent',
		// Nous rencontrer.
		'team_title'        => 'L’équipe',
		'contact_title'     => 'Nous contacter',
		'contact_button'    => 'Envoyer',
		'contact_ok'        => 'Merci, votre message est bien parti ! Nous vous répondons rapidement.',
		// Commander.
		'order_title'       => 'Votre commande',
		'order_info_title'  => 'Vos informations',
		'order_ship_title'  => 'Livraison',
		'order_button'      => 'Commander',
		'order_ok'          => 'Merci, votre précommande est bien envoyée ! Nous revenons vers vous très vite.',
		// Pied de page.
		'footer_cans'       => '1',
		// Mode démo (export statique).
		'demo_mode'         => '',
		'demo_text'         => 'Démo : les formulaires sont désactivés sur cette version en ligne.',
	);
}

/**
 * Lit un réglage du thème avec sa valeur par défaut.
 *
 * @param string $key Clé.
 * @return string
 */
function planty_mod( $key ) {
	$defaults = planty_defaults();
	return (string) get_theme_mod( $key, $defaults[ $key ] ?? '' );
}
