<?php
/**
 * News archive: the "Aktualności" page (Settings → PioDesign, or the page
 * with the slug "aktualnosci"), the posts page, categories, tags, month
 * archives and searches limited to posts use templates/news-archive.php
 * inside the Avada shell. [pio_archiwum] renders the same archive anywhere.
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
	$page = piodesign_archive_page_id();
	if ( $page && is_page( $page ) ) {
		return true;
	}
	if ( function_exists( 'tribe_is_event_query' ) && tribe_is_event_query() ) {
		return false;
	}
	return ( is_home() && ! is_front_page() ) || is_category() || is_tag() || is_date() || is_author();
}

/** Current page number on archives and on a static page (/aktualnosci/page/2/). */
function piodesign_paged() {
	return max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
}

add_action(
	'pre_get_posts',
	static function ( WP_Query $q ) {
		if ( is_admin() || ! $q->is_main_query() || ! piodesign_option( 'news_archive' ) ) {
			return;
		}
		$is_search = $q->is_search() && 'post' === $q->get( 'post_type' );
		if ( 'tribe_events' !== $q->get( 'post_type' ) && ( ( $q->is_home() && ! $q->is_front_page() ) || $q->is_category() || $q->is_tag() || $q->is_date() || $q->is_author() || $is_search ) ) {
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
function piodesign_news_archive_html( array $args = [] ) {
	global $wp_query;

	$on_page = is_page() || ! empty( $args['standalone'] );
	if ( $on_page ) {
		// A static page: run our own posts query for the current page number.
		$query = new WP_Query(
			apply_filters(
				'piodesign_archive_query',
				[
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => (int) piodesign_option( 'archive_per_page' ),
					'paged'          => piodesign_paged(),
					'category_name'  => $args['category'] ?? '',
				]
			)
		);
	} else {
		$query = $wp_query;
	}

	$all_url = piodesign_archive_url();
	$kicker  = $args['kicker'] ?? 'Z życia parafii';
	$title   = $args['title'] ?? 'Aktualności';
	$desc    = '';

	if ( is_category() && ! $on_page ) {
		$kicker = 'Kategoria';
		$title  = single_cat_title( '', false );
		$desc   = term_description();
	} elseif ( is_tag() && ! $on_page ) {
		$kicker = 'Temat';
		$title  = single_tag_title( '', false );
		$desc   = term_description();
	} elseif ( is_month() && ! $on_page ) {
		$kicker = 'Archiwum';
		$title  = piodesign_months( 'nom' )[ (int) get_query_var( 'monthnum' ) ] . ' ' . get_query_var( 'year' );
	} elseif ( is_day() && ! $on_page ) {
		$kicker = 'Archiwum';
		$title  = (int) get_query_var( 'day' ) . ' ' . piodesign_months()[ (int) get_query_var( 'monthnum' ) ] . ' ' . get_query_var( 'year' );
	} elseif ( is_author() && ! $on_page ) {
		$kicker = 'Autor';
		$title  = get_the_author_meta( 'display_name', (int) get_query_var( 'author' ) );
	} elseif ( is_year() && ! $on_page ) {
		$kicker = 'Archiwum';
		$title  = 'Rok ' . get_query_var( 'year' );
	} elseif ( is_search() && ! $on_page ) {
		$kicker = 'Wyniki wyszukiwania';
		$title  = '„' . get_search_query( false ) . '”';
	}

	$generic = apply_filters( 'piodesign_generic_categories', [ 'aktualnosci', 'bez-kategorii', 'uncategorized' ] );
	$current = ( is_category() && ! $on_page ) ? (int) get_queried_object_id() : 0;
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

	$page  = piodesign_paged();
	$pages = (int) $query->max_num_pages;

	return piodesign_render(
		'news-archive',
		[
			'posts'       => piodesign_wp_posts( $query->posts ),
			'title'       => html_entity_decode( (string) $title, ENT_QUOTES, 'UTF-8' ),
			'kicker'      => $kicker,
			'description' => $desc ? wp_kses_post( $desc ) : '',
			'cats'        => $cats,
			'all_url'     => $all_url,
			'all_active'  => ! is_category(),
			'page'        => $page,
			'pages'       => $pages,
			'total'       => (int) $query->found_posts,
			'pager'       => piodesign_pager( $page, $pages, static fn( $n ) => get_pagenum_link( $n ) ),
			'opts'        => [ 'excerpt_card' => (int) piodesign_option( 'archive_excerpt' ) ] + piodesign_news_opts(),
			'search'      => [
				'action' => home_url( '/' ),
				'value'  => get_search_query( false ),
			],
		]
	);
}

add_shortcode(
	'pio_archiwum',
	static function ( $atts ) {
		$atts = shortcode_atts(
			[
				'title'    => 'Aktualności',
				'kicker'   => 'Z życia parafii',
				'category' => '',
			],
			$atts,
			'pio_archiwum'
		);
		piodesign_enqueue_late();
		return piodesign_news_archive_html( $atts + [ 'standalone' => true ] );
	}
);
