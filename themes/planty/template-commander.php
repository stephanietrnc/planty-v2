<?php
/**
 * Template Name: Commander
 *
 * Formulaire de précommande : les goûts viennent du menu « Goûts ».
 *
 * @package planty
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$planty_state  = function_exists( 'planty_form_state' ) ? planty_form_state( 'commande' ) : null;
	$planty_tastes = function_exists( 'planty_get' ) ? planty_get( 'planty_gout' ) : array();
	?>

	<section class="order" id="commande">
		<h1 class="order__title" data-reveal><?php the_title(); ?></h1>

		<?php if ( $planty_state ) : ?>
			<form<?php echo planty_mod( 'demo_mode' ) ? ' data-demo' : ''; ?> class="form form--order" action="<?php echo esc_url( planty_mod( 'demo_mode' ) ? '#' : admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate>
				<?php
				planty_form_notice( $planty_state, planty_mod( 'order_ok' ) );
				planty_form_hidden( 'commande' );
				$planty_qty_error = $planty_state['errors']['quantite'] ?? '';
				?>

				<fieldset class="order__tastes"<?php echo $planty_qty_error ? ' aria-describedby="commande-quantite-erreur"' : ''; ?>>
					<legend class="order__legend"><?php echo esc_html( planty_mod( 'order_title' ) ); ?></legend>
					<?php if ( $planty_qty_error ) : ?>
						<p class="field__error" id="commande-quantite-erreur"><?php echo esc_html( $planty_qty_error ); ?></p>
					<?php endif; ?>
					<?php
					// Goût choisi depuis l'accueil (?gout=ID) : quantité 1 au départ.
					$planty_picked = isset( $_GET['gout'] ) ? absint( $_GET['gout'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- simple préremplissage.
					?>
					<ul class="order__grid">
						<?php foreach ( $planty_tastes as $planty_i => $planty_taste ) : ?>
							<?php
							$planty_id    = 'quantite-' . $planty_taste->ID;
							$planty_price = function_exists( 'planty_price' ) ? planty_price( $planty_taste->ID ) : null;
							$planty_qty   = $planty_state['old']['quantite'][ $planty_taste->ID ] ?? ( $planty_picked === $planty_taste->ID ? 1 : 0 );
							?>
							<li class="order-taste" data-reveal style="<?php echo esc_attr( planty_taste_style( $planty_taste, $planty_i ) ); ?>">
								<div class="order-taste__visual">
									<?php echo planty_thumb( $planty_taste, 'planty-gout', array( 'class' => 'order-taste__img', 'sizes' => '(max-width: 760px) 45vw, 193px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- HTML de WordPress. ?>
									<span class="order-taste__name" aria-hidden="true"><?php echo planty_taste_label( $planty_taste ); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé. ?></span>
								</div>
								<label class="screen-reader-text" for="<?php echo esc_attr( $planty_id ); ?>">
									<?php
									/* translators: %s : nom du goût. */
									printf( esc_html__( 'Quantité %s', 'planty' ), esc_html( get_the_title( $planty_taste ) ) );
									?>
								</label>
								<div class="qty">
									<button class="qty__btn" type="button" data-step="-1" aria-controls="<?php echo esc_attr( $planty_id ); ?>" hidden>
										<span aria-hidden="true">−</span>
										<span class="screen-reader-text">
											<?php
											/* translators: %s : nom du goût. */
											printf( esc_html__( 'Une canette %s de moins', 'planty' ), esc_html( get_the_title( $planty_taste ) ) );
											?>
										</span>
									</button>
									<input class="order-taste__qty" type="number" id="<?php echo esc_attr( $planty_id ); ?>" name="quantite[<?php echo esc_attr( (string) $planty_taste->ID ); ?>]" value="<?php echo esc_attr( (string) $planty_qty ); ?>" min="0" max="99" step="1" inputmode="numeric" data-name="<?php echo esc_attr( get_the_title( $planty_taste ) ); ?>"<?php echo null !== $planty_price ? ' data-price="' . esc_attr( (string) $planty_price ) . '"' : ''; ?>>
									<button class="qty__btn" type="button" data-step="1" aria-controls="<?php echo esc_attr( $planty_id ); ?>" hidden>
										<span aria-hidden="true">+</span>
										<span class="screen-reader-text">
											<?php
											/* translators: %s : nom du goût. */
											printf( esc_html__( 'Une canette %s de plus', 'planty' ), esc_html( get_the_title( $planty_taste ) ) );
											?>
										</span>
									</button>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
					<?php // Récapitulatif rempli en JS (caché sans JS : les quantités suffisent). ?>
					<p class="order__summary" aria-live="polite" hidden></p>
				</fieldset>

				<div class="order__columns">
					<?php
					$planty_fields = planty_form_fields( 'commande' );
					$planty_groups = array(
						'order_info_title' => array( 'nom', 'prenom', 'email' ),
						'order_ship_title' => array( 'adresse', 'code_postal', 'ville' ),
					);
					foreach ( $planty_groups as $planty_title => $planty_names ) :
						?>
						<fieldset class="order__group">
							<legend class="order__legend"><?php echo esc_html( planty_mod( $planty_title ) ); ?></legend>
							<?php
							foreach ( $planty_names as $planty_name ) {
								planty_field( 'commande', $planty_name, $planty_fields[ $planty_name ], $planty_state );
							}
							?>
						</fieldset>
					<?php endforeach; ?>
				</div>

				<div class="form__actions">
					<?php planty_submit( planty_mod( 'order_button' ) ); ?>
				</div>
				<?php planty_demo_message(); ?>
			</form>
		<?php endif; ?>
	</section>

	<?php
endwhile;

get_footer();
