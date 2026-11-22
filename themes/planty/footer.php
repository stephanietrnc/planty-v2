<?php
/**
 * Pied de page : rangée de canettes (sur l'accueil et Nous rencontrer) + liens légaux.
 *
 * @package planty
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="site-footer">
	<?php
	if ( has_nav_menu( 'pied' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'pied',
				'container'      => 'nav',
				'container_attr' => array( 'aria-label' => __( 'Pied de page', 'planty' ) ),
				'menu_class'     => 'site-footer__list',
				'depth'          => 1,
			)
		);
	} else {
		$planty_legal = get_privacy_policy_url();
		if ( $planty_legal ) {
			echo '<ul class="site-footer__list"><li><a href="' . esc_url( $planty_legal ) . '">' . esc_html__( 'Mentions légales', 'planty' ) . '</a></li></ul>';
		}
	}
	?>
</footer>

<?php wp_footer(); ?>
</body>
</html>
