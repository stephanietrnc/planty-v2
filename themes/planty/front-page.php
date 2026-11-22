<?php
/**
 * Accueil.
 *
 * @package planty
 */

defined( 'ABSPATH' ) || exit;

get_header();

$planty_order_url = planty_template_url( 'template-commander.php' );
$planty_can_id    = (int) planty_mod( 'hero_can' );
?>

<section class="hero">
	<h1 class="hero__title" data-reveal><?php echo planty_lines( planty_mod( 'hero_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé dans planty_lines(). ?></h1>

	<div class="hero__stage">
		<div class="hero__item hero__item--left" aria-hidden="true">
			<img class="hero__float" src="<?php echo esc_url( planty_asset( 'plante-gauche.webp' ) ); ?>" width="350" height="401" alt="" decoding="async">
		</div>
		<div class="hero__item hero__item--right" aria-hidden="true">
			<img class="hero__float" src="<?php echo esc_url( planty_asset( 'plante-droite.webp' ) ); ?>" width="350" height="401" alt="" decoding="async">
		</div>
		<div class="hero__item hero__item--can">
			<?php
			if ( $planty_can_id ) {
				echo wp_get_attachment_image(
					$planty_can_id,
					'full',
					false,
					array(
						'class'         => 'hero__float',
						'alt'           => planty_mod( 'hero_can_alt' ),
						'fetchpriority' => 'high',
						'sizes'         => '(max-width: 760px) 55vw, 387px',
					)
				);
			} else {
				printf(
					'<img class="hero__float" src="%1$s" width="387" height="618" alt="%2$s" fetchpriority="high" decoding="async">',
					esc_url( planty_asset( 'canette.webp' ) ),
					esc_attr( planty_mod( 'hero_can_alt' ) )
				);
			}
			?>
		</div>
	</div>
</section>

<section class="band band--energy">
	<div class="band__inner">
		<h2 class="title title--green-bold" data-reveal><?php echo esc_html( planty_mod( 'energy_title' ) ); ?></h2>
		<p class="energy__text" data-reveal><?php echo esc_html( planty_mod( 'energy_text' ) ); ?></p>
	</div>
</section>

<?php
$planty_tastes = function_exists( 'planty_get' ) ? planty_get( 'planty_gout' ) : array();
?>
<section class="tastes" id="gouts">
	<h2 class="title title--pink" data-reveal><?php echo esc_html( planty_mod( 'tastes_title' ) ); ?></h2>
	<p class="tastes__text" data-reveal><?php echo esc_html( planty_mod( 'tastes_text' ) ); ?></p>
	<?php if ( $planty_tastes ) : ?>
		<ul class="tastes__grid">
			<?php foreach ( $planty_tastes as $planty_i => $planty_taste ) : ?>
				<li class="taste" data-reveal style="<?php echo esc_attr( planty_taste_style( $planty_taste, $planty_i % 2 ) ); ?>">
					<?php if ( $planty_order_url ) : ?>
						<a class="taste__link" href="<?php echo esc_url( add_query_arg( 'gout', $planty_taste->ID, $planty_order_url ) . '#commande' ); ?>">
					<?php endif; ?>
					<?php echo planty_thumb( $planty_taste, 'planty-gout', array( 'class' => 'taste__img', 'sizes' => '(max-width: 860px) 90vw, 393px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- HTML de WordPress. ?>
					<h3 class="taste__name"><span class="screen-reader-text"><?php echo esc_html( get_the_title( $planty_taste ) ); ?></span><span aria-hidden="true"><?php echo planty_taste_label( $planty_taste ); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé. ?></span></h3>
					<?php if ( $planty_order_url ) : ?>
							<span class="taste__cta"><?php esc_html_e( 'Commander ce goût', 'planty' ); ?></span>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php if ( $planty_order_url && planty_mod( 'tastes_button' ) ) : ?>
		<a class="button" href="<?php echo esc_url( $planty_order_url ); ?>"><?php echo esc_html( planty_mod( 'tastes_button' ) ); ?></a>
	<?php endif; ?>
</section>

<?php
$planty_reviews = function_exists( 'planty_get' ) ? planty_get( 'planty_avis' ) : array();
?>
<section class="band band--reviews">
	<h2 class="title title--green" data-reveal><?php echo esc_html( planty_mod( 'reviews_title' ) ); ?></h2>
	<?php if ( $planty_reviews ) : ?>
		<ul class="reviews">
			<?php foreach ( $planty_reviews as $planty_i => $planty_review ) : ?>
				<li class="review" data-reveal style="--d: <?php echo esc_attr( (string) $planty_i ); ?>">
					<?php echo planty_thumb( $planty_review, 'medium', array( 'class' => 'review__photo', 'sizes' => '126px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- HTML de WordPress. ?>
					<figure class="review__body">
						<blockquote class="review__text"><p><?php echo esc_html( get_post_meta( $planty_review->ID, 'planty_texte', true ) ); ?></p></blockquote>
						<figcaption class="review__name"><?php echo esc_html( get_the_title( $planty_review ) ); ?></figcaption>
					</figure>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php get_template_part( 'template-parts/cans' ); ?>
</section>

<?php
get_footer();
