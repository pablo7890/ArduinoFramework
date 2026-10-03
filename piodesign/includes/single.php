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

add_filter(
	'template_include',
	static function ( $template ) {
		return piodesign_is_single_post() ? PIODESIGN_DIR . 'wp/single-post.php' : $template;
	},
	99
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

	return piodesign_render(
		'single-post',
		[
			'p'        => $p,
			'content'  => apply_filters( 'the_content', $post->post_content ),
			'lede'     => has_excerpt( $post ) ? html_entity_decode( get_the_excerpt( $post ), ENT_QUOTES, 'UTF-8' ) : '',
			'cat_url'  => $cat_obj ? get_category_link( $cat_obj ) : '',
			'prev'     => piodesign_post_link( get_previous_post() ),
			'next'     => piodesign_post_link( get_next_post() ),
			'related'  => $related,
			'tags'     => $tags && ! is_wp_error( $tags ) ? array_map( static fn( $t ) => [ 'name' => $t->name, 'url' => get_tag_link( $t ) ], $tags ) : [],
			'back_url' => piodesign_archive_url(),
			'share'    => (bool) piodesign_option( 'single_share' ),
			'image'    => piodesign_wp_image( get_post_thumbnail_id( $post ), 'full' ),
		]
	);
}
