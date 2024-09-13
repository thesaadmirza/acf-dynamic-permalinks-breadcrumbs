<?php
/*
Plugin Name:       Custom Permalinks and Breadcrumbs Fix
Plugin URI:        https://github.com/thesaadmirza/custom-permalinks-breadcrumbs-fix
Description:       Fixes permalinks and breadcrumbs for custom post types with taxonomy placeholders.
Version:           1.0.0
Author:            Saad Mirza
Author URI:        https://github.com/thesaadmirza
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:       custom-permalinks-breadcrumbs-fix
Domain Path:       /languages
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function custom_post_type_permalink( $permalink, $post, $leavename ) {
    $post_type = get_post_type( $post );
    $post_type_object = get_post_type_object( $post_type );

    if ( ! $post_type_object || ! isset( $post_type_object->rewrite['slug'] ) ) {
        return $permalink;
    }

    $slug = $post_type_object->rewrite['slug'];

    if ( strpos( $slug, '%' ) !== false ) {
        preg_match_all( '/%([^%]+)%/', $slug, $matches );

        if ( $matches && isset( $matches[1] ) ) {
            foreach ( $matches[1] as $taxonomy ) {
                $terms = get_the_terms( $post->ID, $taxonomy );
                if ( $terms && ! is_wp_error( $terms ) ) {
                    $term = array_shift( $terms );
                    $taxonomy_slug = '';
                    $ancestors = get_ancestors( $term->term_id, $taxonomy );
                    $ancestors = array_reverse( $ancestors );

                    foreach ( $ancestors as $ancestor_id ) {
                        $ancestor = get_term( $ancestor_id, $taxonomy );
                        $taxonomy_slug .= $ancestor->slug . '/';
                    }

                    $taxonomy_slug .= $term->slug;
                } else {
                    $taxonomy_slug = 'uncategorized';
                }

                $permalink = str_replace( '%' . $taxonomy . '%', $taxonomy_slug, $permalink );
            }
        }
    }

    return $permalink;
}
add_filter( 'post_type_link', 'custom_post_type_permalink', 10, 3 );

function custom_add_rewrite_rules() {
    $post_types = get_post_types( array( 'public' => true ), 'objects' );

    foreach ( $post_types as $post_type ) {
        if ( isset( $post_type->rewrite['slug'] ) && strpos( $post_type->rewrite['slug'], '%' ) !== false ) {
            $slug = $post_type->rewrite['slug'];
            $regex = $slug;
            preg_match_all( '/%([^%]+)%/', $slug, $matches );

            $num_taxonomies = count( $matches[1] );
            if ( $matches && isset( $matches[1] ) ) {
                foreach ( $matches[1] as $taxonomy ) {
                    $regex = str_replace( '%' . $taxonomy . '%', '(.+?)', $regex );
                }
            }

            $regex = '^' . $regex . '/([^/]+)/?$';
            $query = 'index.php?post_type=' . $post_type->name . '&name=$matches[' . ( $num_taxonomies + 1 ) . ']';

            foreach ( $matches[1] as $index => $taxonomy ) {
                $query .= '&' . $taxonomy . '=$matches[' . ( $index + 1 ) . ']';
            }

            add_rewrite_rule( $regex, $query, 'top' );
        }
    }
}
add_action( 'init', 'custom_add_rewrite_rules' );

function custom_dynamic_breadcrumbs() {
    global $post;

    if ( ! is_singular() || ! isset( $post ) ) {
        return;
    }

    $breadcrumbs = array();
    $breadcrumbs[] = '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Cleura', 'your-text-domain' ) . '</a>';

    $post_type = get_post_type( $post );
    $post_type_obj = get_post_type_object( $post_type );

    if ( $post_type_obj && $post_type_obj->has_archive ) {
        $archive_link = get_post_type_archive_link( $post_type );
        $breadcrumbs[] = '<a href="' . esc_url( $archive_link ) . '">' . esc_html( $post_type_obj->labels->name ) . '</a>';
    }

    $slug = $post_type_obj->rewrite['slug'];
    if ( strpos( $slug, '%' ) !== false ) {
        preg_match_all( '/%([^%]+)%/', $slug, $matches );

        if ( $matches && isset( $matches[1] ) ) {
            foreach ( $matches[1] as $taxonomy ) {
                $terms = get_the_terms( $post->ID, $taxonomy );
                if ( $terms && ! is_wp_error( $terms ) ) {
                    $term = array_shift( $terms );
                    $ancestors = get_ancestors( $term->term_id, $taxonomy );
                    $ancestors = array_reverse( $ancestors );

                    foreach ( $ancestors as $ancestor_id ) {
                        $ancestor = get_term( $ancestor_id, $taxonomy );
                        $breadcrumbs[] = '<a href="' . esc_url( get_term_link( $ancestor ) ) . '">' . esc_html( $ancestor->name ) . '</a>';
                    }

                    $breadcrumbs[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
                }
            }
        }
    }

    $breadcrumbs[] = esc_html( get_the_title() );
    echo implode( ' / ', $breadcrumbs );
}

if ( ! function_exists( 'x_breadcrumbs' ) ) {
    function x_breadcrumbs() {
        custom_dynamic_breadcrumbs();
    }
}