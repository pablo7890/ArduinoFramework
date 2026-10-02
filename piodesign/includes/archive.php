<?php
/**
 * News archive: posts page, categories, tags, month archives and searches
 * limited to posts use templates/news-archive.php inside the Avada shell.
 *
 * @package PioDesign
 */

defined( 'ABSPATH' ) || exit;

function piodesign_is_news_archive() {
	if ( is_admin() || ! piodesign_option( 'news_archive' ) || ! apply_filters( 'piodesign_news_archive', true ) ) {
		return false;
	}
	if ( is_search() ) {
		return 'post' === get_query_var( 'post_type' );
	}
	return ( is_home() && ! is_front_page() ) || is_category() || is_tag() || ( is_date() && ! is_day() );
}

add_action(
	'pre_get_posts',
	static function ( WP_Query $q ) {
		if ( is_admin() || ! $q->is_main_query() || ! piodesign_option( 'news_archive' ) ) {
			return;
		}
		$is_search = $q->is_search() && 'post' === $q->get( 'post_type' );
		if ( ( $q->is_home() && ! $q->is_front_page() ) || $q->is_category() || $q->is_tag() || ( $q->is_date() && ! $q->is_day() ) || $is_search ) {
			$q->set( 'posts_per_page', (int) piodesign_option( 'archive_per_page' ) );
		}
	}
);

add_filter(
	'template_include',
	static function ( $template ) {
		return piodesign_is_news_archive() ? PIODESIGN_DIR . 'wp/news-archive.php' : $template;
	},
	99
);

add_filter(
	'body_class',
	static function ( $classes ) {
		if ( piodesign_is_news_archive() ) {
			$classes   = array_diff( $classes, [ 'has-sidebar', 'double-sidebars' ] );
			$classes[] = 'pio-news-archive';
		}
		return $classes;
	},
	99
);

/**
 * HTML of the archive for the current main query.
 */
function piodesign_news_archive_html() {
	global $wp_query;

	$posts_page = (int) get_option( 'page_for_posts' );
	$all_url    = $posts_page ? get_permalink( $posts_page ) : home_url( '/' );
	$kicker     = 'Z życia parafii';
	$title      = 'Aktualności';
	$desc       = '';

	if ( is_category() ) {
		$kicker = 'Kategoria';
		$title  = single_cat_title( '', false );
		$desc   = term_description();
	} elseif ( is_tag() ) {
		$kicker = 'Temat';
		$title  = single_tag_title( '', false );
		$desc   = term_description();
	} elseif ( is_month() ) {
		$kicker = 'Archiwum';
		$title  = piodesign_months( 'nom' )[ (int) get_query_var( 'monthnum' ) ] . ' ' . get_query_var( 'year' );
	} elseif ( is_year() ) {
		$kicker = 'Archiwum';
		$title  = 'Rok ' . get_query_var( 'year' );
	} elseif ( is_search() ) {
		$kicker = 'Wyniki wyszukiwania';
		$title  = '„' . get_search_query( false ) . '”';
	}

	$generic = apply_filters( 'piodesign_generic_categories', [ 'aktualnosci', 'bez-kategorii', 'uncategorized' ] );
	$current = is_category() ? (int) get_queried_object_id() : 0;
	$cats    = [];
	foreach ( get_categories( [ 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true, 'number' => 12 ] ) as $t ) {
		if ( in_array( $t->slug, $generic, true ) || (int) get_option( 'default_category' ) === $t->term_id ) {
			continue;
		}
		$cats[] = [
			'name'   => $t->name,
			'url'    => get_category_link( $t ),
			'count'  => (int) $t->count,
			'color'  => piodesign_category_color( $t->slug ),
			'active' => $current === $t->term_id,
		];
	}

	$page  = max( 1, (int) get_query_var( 'paged' ) );
	$pages = (int) $wp_query->max_num_pages;

	return piodesign_render(
		'news-archive',
		[
			'posts'       => piodesign_wp_posts( $wp_query->posts ),
			'title'       => html_entity_decode( (string) $title, ENT_QUOTES, 'UTF-8' ),
			'kicker'      => $kicker,
			'description' => $desc ? wp_kses_post( $desc ) : '',
			'cats'        => $cats,
			'all_url'     => $all_url,
			'all_active'  => is_home(),
			'page'        => $page,
			'pages'       => $pages,
			'total'       => (int) $wp_query->found_posts,
			'pager'       => piodesign_pager( $page, $pages, static fn( $n ) => get_pagenum_link( $n ) ),
			'search'      => [
				'action' => home_url( '/' ),
				'value'  => get_search_query( false ),
			],
		]
	);
}
