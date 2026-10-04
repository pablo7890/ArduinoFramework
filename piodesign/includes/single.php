<?php
/**
 * Single news post: our template inside the Avada shell.
 *
 * @package PioDesign
 */

defined( 'ABSPATH' ) || exit;

function piodesign_is_single_post() {
	return ! is_admin() && is_singular( 'post' ) && piodesign_option( 'single_post' ) && apply_filters( 'piodesign_single_post', true );
}

/**
 * True when an Avada Layout (Avada → Layouts) replaces the content of this
 * page. Then the layout wins and the [pio_wpis] / [pio_wydarzenie]
 * shortcodes put our view inside it.
 */
function piodesign_avada_layout() {
	if ( ! class_exists( 'Fusion_Template_Builder' ) || ! method_exists( 'Fusion_Template_Builder', 'get_instance' ) ) {
		return false;
	}
	$builder = Fusion_Template_Builder::get_instance();
	return method_exists( $builder, 'get_override' ) && (bool) $builder->get_override( 'content' );
}

add_filter(
	'template_include',
	static function ( $template ) {
		return piodesign_is_single_post() && ! piodesign_avada_layout() ? PIODESIGN_DIR . 'wp/single-post.php' : $template;
	},
	99
);

/*
 * [pio_wpis] and [pio_wydarzenie]: the single post / single event view for
 * an Avada Layout section (Code Block element). Without `id` they show the
 * post or event being viewed.
 */
add_shortcode(
	'pio_wpis',
	static function ( $atts ) {
		static $busy = false;
		$atts = shortcode_atts( [ 'id' => 0 ], $atts, 'pio_wpis' );
		$id   = (int) $atts['id'] ?: ( is_singular( 'post' ) ? get_queried_object_id() : 0 );
		if ( $busy || ! $id || 'post' !== get_post_type( $id ) ) {
			return '';
		}
		$busy = true;
		piodesign_enqueue_late();
		$html = piodesign_single_post_html( $id );
		$busy = false;
		return $html;
	}
);

add_shortcode(
	'pio_wydarzenie',
	static function ( $atts ) {
		static $busy = false;
		$atts = shortcode_atts( [ 'id' => 0 ], $atts, 'pio_wydarzenie' );
		$id   = (int) $atts['id'] ?: ( is_singular( 'tribe_events' ) ? get_queried_object_id() : 0 );
		if ( $busy || ! $id || 'tribe_events' !== get_post_type( $id ) || ! function_exists( 'piodesign_single_event_html' ) ) {
			return '';
		}
		$busy = true;
		piodesign_enqueue_late();
		$html = piodesign_single_event_html( $id );
		$busy = false;
		return $html;
	}
);

add_filter(
	'body_class',
	static function ( $classes ) {
		if ( piodesign_is_single_post() ) {
			$classes   = array_diff( $classes, [ 'has-sidebar', 'double-sidebars' ] );
			$classes[] = 'pio-single-post';
		}
		return $classes;
	},
	99
);

/** Small card data for prev / next links. */
function piodesign_post_link( $post ) {
	if ( ! $post ) {
		return null;
	}
	return [
		'title' => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
		'url'   => get_permalink( $post ),
		'date'  => piodesign_date( new DateTimeImmutable( $post->post_date, wp_timezone() ) ),
	];
}

function piodesign_single_post_html( $post_id ) {
	$post = get_post( $post_id );
	$p    = piodesign_wp_posts( [ $post ] )[0];

	$cat     = piodesign_wp_category( $post->ID );
	$cat_obj = $cat ? get_category_by_slug( $cat['slug'] ) : null;

	$related = [];
	$count   = (int) piodesign_option( 'single_related' );
	if ( $count > 0 ) {
		$args = [
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'post__not_in'        => [ $post->ID ],
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		];
		if ( $cat_obj ) {
			$args['cat'] = $cat_obj->term_id;
		}
		$q = new WP_Query( $args );
		if ( count( $q->posts ) < $count ) {
			unset( $args['cat'] );
			$args['post__not_in'] = array_merge( [ $post->ID ], wp_list_pluck( $q->posts, 'ID' ) );
			$args['posts_per_page'] = $count - count( $q->posts );
			$q->posts = array_merge( $q->posts, ( new WP_Query( $args ) )->posts );
		}
		$related = piodesign_wp_posts( $q->posts );
	}

	$tags = get_the_tags( $post->ID );

	// Hero: a landscape photo that the text doesn't open with; if the only
	// good one does, it moves to the top and leaves the text.
	$hero    = piodesign_pick_image( $post, true );
	$content = apply_filters( 'the_content', $post->post_content );
	if ( $hero['strip'] ) {
		$content = piodesign_strip_image( $content, $hero['id'] );
	}

	return piodesign_render(
		'single-post',
		[
			'p'        => $p,
			'content'  => $content,
			'lede'     => has_excerpt( $post ) ? html_entity_decode( get_the_excerpt( $post ), ENT_QUOTES, 'UTF-8' ) : '',
			'cat_url'  => $cat_obj ? get_category_link( $cat_obj ) : '',
			'prev'     => piodesign_post_link( get_previous_post() ),
			'next'     => piodesign_post_link( get_next_post() ),
			'related'  => $related,
			'tags'     => $tags && ! is_wp_error( $tags ) ? array_map( static fn( $t ) => [ 'name' => $t->name, 'url' => get_tag_link( $t ) ], $tags ) : [],
			'back_url' => piodesign_archive_url(),
			'share'    => (bool) piodesign_option( 'single_share' ),
			'image'    => piodesign_wp_image( $hero['id'], 'full' ),
		]
	);
}
