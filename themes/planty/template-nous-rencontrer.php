<?php
/**
 * Template Name: Nous rencontrer
 *
 * Titre et introduction = ceux de la page ; équipe = menu « Équipe » ; formulaire de contact.
 *
 * @package planty
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$planty_state = function_exists( 'planty_form_state' ) ? planty_form_state( 'contact' ) : null;
	$planty_team  = function_exists( 'planty_get' ) ? planty_get( 'planty_membre' ) : array();
	?>

	<section class="page-hero">
		<img class="page-hero__flower" src="<?php echo esc_url( planty_asset( 'fleur.webp' ) ); ?>" width="191" height="188" alt="" aria-hidden="true" decoding="async">
		<h1 class="page-title page-hero__title" data-reveal><?php the_title(); ?></h1>
		<div class="text-lead page-hero__text" data-reveal><?php the_content(); ?></div>
	</section>

	<section class="band band--team">
		<h2 class="title title--black" data-reveal><?php echo esc_html( planty_mod( 'team_title' ) ); ?></h2>
		<div class="team-wrap">
			<?php if ( $planty_team ) : ?>
				<ul class="team">
					<?php foreach ( $planty_team as $planty_i => $planty_member ) : ?>
						<li class="member" data-reveal style="--d: <?php echo esc_attr( (string) $planty_i ); ?>">
							<div class="member__photo"><?php echo planty_thumb( $planty_member, 'planty-portrait', array( 'sizes' => '167px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- HTML de WordPress. ?></div>
							<h3 class="member__name"><?php echo esc_html( get_the_title( $planty_member ) ); ?></h3>
							<p class="member__role"><?php echo esc_html( get_post_meta( $planty_member->ID, 'planty_role', true ) ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php // Branche décorative : accrochée à droite de l'équipe (voir pages/_meet.scss). ?>
			<img class="band__branch" src="<?php echo esc_url( planty_asset( 'plante-gauche.webp' ) ); ?>" width="350" height="401" alt="" aria-hidden="true" loading="lazy" decoding="async">
		</div>
	</section>

	<section class="contact" id="contact">
		<h2 class="title title--black" data-reveal><?php echo esc_html( planty_mod( 'contact_title' ) ); ?></h2>
		<?php if ( $planty_state ) : ?>
			<form<?php echo planty_mod( 'demo_mode' ) ? ' data-demo' : ''; ?> class="form form--contact" action="<?php echo esc_url( planty_mod( 'demo_mode' ) ? '#' : admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate>
				<?php
				planty_form_notice( $planty_state, planty_mod( 'contact_ok' ) );
				planty_form_hidden( 'contact' );
				foreach ( planty_form_fields( 'contact' ) as $planty_name => $planty_field ) {
					planty_field( 'contact', $planty_name, $planty_field, $planty_state );
				}
				?>
				<div class="form__actions">
					<img class="form__flower" src="<?php echo esc_url( planty_asset( 'fleur-form.webp' ) ); ?>" width="185" height="178" alt="" aria-hidden="true" loading="lazy" decoding="async">
					<?php planty_submit( planty_mod( 'contact_button' ) ); ?>
				</div>
				<?php planty_demo_message(); ?>
			</form>
		<?php endif; ?>
		<?php get_template_part( 'template-parts/cans' ); ?>
	</section>

	<?php
endwhile;

get_footer();
