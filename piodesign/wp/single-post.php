<?php
/**
 * Theme template for a single news post (Avada header and footer).
 *
 * @package PioDesign
 */

defined( 'ABSPATH' ) || exit;

wp_enqueue_style( 'piodesign' );
wp_enqueue_script( 'piodesign' );

get_header();
?>
<section id="content" class="full-width piodesign-content" style="width: 100%; float: none;">
	<?php
	while ( have_posts() ) {
		the_post();
		echo piodesign_single_post_html( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput
		if ( comments_open() || get_comments_number() ) {
			echo '<div class="pio-post__comments">';
			comments_template();
			echo '</div>';
		}
	}
	?>
</section>
<?php
get_footer();
