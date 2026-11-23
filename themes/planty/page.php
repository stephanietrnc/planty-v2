<?php
/**
 * Page simple (mentions légales, etc.).
 *
 * @package planty
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article class="page-simple">
		<h1 class="page-title page-simple__title"><?php the_title(); ?></h1>
		<div class="prose"><?php the_content(); ?></div>
	</article>
	<?php
endwhile;

get_footer();
