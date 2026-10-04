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

/**
 * Two switches: `news_archive` takes over the "Aktualności" page and the posts
 * page; `news_terms` takes over category, tag, date and author archives and
 * the search in posts. The second one stays useful when "Aktualności" is an
 * Avada page with [pio_archiwum] – its category chips lead here.
 */
function piodesign_is_news_archive() {
	if ( is_admin() || ! apply_filters( 'piodesign_news_archive', true ) ) {
		return false;
	}
	if ( function_exists( 'tribe_is_event_query' ) && tribe_is_event_query() ) {
		return false;
	}
	if ( is_search() ) {
		return piodesign_option( 'news_terms' ) && 'post' === get_query_var( 'post_type' );
	}
	$page = piodesign_archive_page_id();
	if ( $page && is_page( $page ) ) {
		return (bool) piodesign_option( 'news_archive' );
	}
	if ( is_home() && ! is_front_page() ) {
		return (bool) piodesign_option( 'news_archive' );
	}
	return piodesign_option( 'news_terms' ) && ( is_category() || is_tag() || is_date() || is_author() );
}

/** Current page number on archives and on a static page (/aktualnosci/page/2/). */
function piodesign_paged() {
	return max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
}

add_action(
	'pre_get_posts',
	static function ( WP_Query $q ) {
		if ( is_admin() || ! $q->is_main_query() || 'tribe_events' === $q->get( 'post_type' ) ) {
			return;
		}
		$terms = piodesign_option( 'news_terms' ) && ( $q->is_category() || $q->is_tag() || $q->is_date() || $q->is_author() || ( $q->is_search() && 'post' === $q->get( 'post_type' ) ) );
		$home  = piodesign_option( 'news_archive' ) && $q->is_home() && ! $q->is_front_page();
		if ( $terms || $home ) {
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

	$generic = apply_filters( 'piodesign_generic_categories', [ 'aktualnosci', 'bez-kategorii', 'uncategorized' ] );
	$qo      = $on_page ? null : get_queried_object();
	$is_all  = ! $qo || ! ( is_category() || is_tag() ) || ( is_category() && ( in_array( $qo->slug, $generic, true ) || (int) get_option( 'default_category' ) === (int) $qo->term_id ) );
	$term    = null;

	if ( ( is_category() || is_tag() ) && ! $on_page && ! $is_all ) {
		$kicker = is_category() ? 'Kategoria' : 'Temat';
		$title  = $qo->name;
		$desc   = term_description( $qo );
		$term   = piodesign_term_info( $qo, (int) $query->found_posts, $all_url );
	} elseif ( is_category() && ! $on_page ) {
		// "Aktualności" and other catch-all categories read as the whole archive.
		$desc = term_description( $qo );
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

	$current = ( is_category() && ! $on_page && ! $is_all ) ? (int) get_queried_object_id() : 0;
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

	// The open category goes first, so on phones it isn't scrolled out of sight.
	usort( $cats, static fn( $a, $b ) => (int) $b['active'] <=> (int) $a['active'] );

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
			'all_active'  => ! $current,
			'term'        => $term,
			'page'        => $page,
			'pages'       => $pages,
			'total'       => (int) $query->found_posts,
			'pager'       => piodesign_pager( $page, $pages, static fn( $n ) => get_pagenum_link( $n ) ),
			'opts'        => [ 'excerpt_card' => (int) piodesign_option( 'archive_excerpt' ) ] + piodesign_news_opts(),
			'search'      => [
				'action' => home_url( '/' ),
				'value'  => get_search_query( false ),
				'cat'    => $term && is_category() ? (int) $qo->term_id : (int) get_query_var( 'cat' ),
			],
		]
	);
}

/**
 * Header data for a category or tag: colour, post count and the span of
 * years it covers ("od marca 2024").
 */
function piodesign_term_info( WP_Term $t, $count, $all_url ) {
	$tax   = 'category' === $t->taxonomy ? 'cat' : 'tag_id';
	$edges = [];
	foreach ( [ 'ASC', 'DESC' ] as $order ) {
		$q = get_posts(
			[
				'post_type'        => 'post',
				'posts_per_page'   => 1,
				'orderby'          => 'date',
				'order'            => $order,
				$tax               => $t->term_id,
				'fields'           => 'ids',
				'suppress_filters' => false,
			]
		);
		$edges[] = $q ? new DateTimeImmutable( get_post_field( 'post_date', $q[0] ), wp_timezone() ) : null;
	}
	return [
		'type'   => 'category' === $t->taxonomy ? 'Kategoria' : 'Temat',
		'name'   => html_entity_decode( $t->name, ENT_QUOTES, 'UTF-8' ),
		'color'  => 'category' === $t->taxonomy ? piodesign_category_color( $t->slug ) : 'var(--pio-rust)',
		'count'  => $count,
		'since'  => $edges[0] ? piodesign_months()[ (int) $edges[0]->format( 'n' ) ] . ' ' . $edges[0]->format( 'Y' ) : '',
		'latest' => $edges[1] ? piodesign_date( $edges[1], true ) : '',
		'back'   => $all_url,
	];
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
