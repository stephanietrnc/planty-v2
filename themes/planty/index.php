<?php
/**
 * Modèle de secours (articles, archives, 404).
 *
 * @package planty
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<article class="page-simple">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<h1 class="page-title page-simple__title"><?php the_title(); ?></h1>
			<div class="prose"><?php the_content(); ?></div>
		<?php endwhile; ?>
	<?php else : ?>
		<h1 class="page-title page-simple__title"><?php esc_html_e( 'Page introuvable', 'planty' ); ?></h1>
		<div class="prose"><p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Retour à l\'accueil', 'planty' ); ?></a></p></div>
	<?php endif; ?>
</article>
<?php
get_footer();
