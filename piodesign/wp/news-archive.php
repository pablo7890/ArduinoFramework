<?php
/**
 * Theme template for the news archive. Avada's header.php opens #main and
 * .fusion-row; footer.php closes them, as in Avada's own archive.php.
 *
 * @package PioDesign
 */

defined( 'ABSPATH' ) || exit;

wp_enqueue_style( 'piodesign' );
wp_enqueue_script( 'piodesign' );

get_header();
?>
<section id="content" class="full-width piodesign-content" style="width: 100%; float: none;">
	<?php echo piodesign_news_archive_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</section>
<?php
get_footer();
