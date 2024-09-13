<?php
/*
Plugin Name:       ACF Dynamic Permalinks and Breadcrumbs
Plugin URI:        https://github.com/thesaadmirza/acf-dynamic-permalinks-breadcrumbs
Description:       Replaces taxonomy placeholders in permalinks and adjusts breadcrumbs for ACF-registered custom post types and taxonomies, including nested categories.
Version:           1.0.0
Requires at least: 5.0
Tested up to:      6.3
Requires PHP:      7.0
Author:            Saad Mirza
Author URI:        https://github.com/thesaadmirza
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:       acf-dynamic-permalinks-breadcrumbs
Domain Path:       /languages
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load plugin textdomain for translations.
function adpb_load_textdomain() {
    load_plugin_textdomain( 'acf-dynamic-permalinks-breadcrumbs', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'adpb_load_textdomain' );

/**
 * Replace taxonomy placeholders in permalinks for ACF-registered custom post types.
 *
 * @param string  $permalink The post's permalink.
 * @param WP_Post $post      The post in question.
 * @param bool    $leavename Whether to keep the post name.
 * @return string The modified permalink.
 */
function adpb_custom_post_type_permalink( $permalink, $post, $leavename ) {
    if ( ! is_object( $post ) || 'publish' !== $post->post_status ) {
        return $permalink;
    }

    $post_type = get_post_type( $post );
    $post_type_object = get_post_type_object( $post_type );

    if ( ! $post_type_object || ! isset( $post_type_object->rewrite['slug'] ) ) {
        return $permalink;
    }

    $slug = $post_type_object->rewrite['slug'];

    // Check if the slug contains placeholders.
    if ( strpos( $slug, '%' ) !== false ) {
        // Find all placeholders in the slug.
        preg_match_all( '/%([^%]+)%/', $slug, $matches );

        if ( $matches && isset( $matches[1] ) ) {
            foreach ( $matches[1] as $taxonomy ) {
                // Get the terms for this taxonomy.
                $terms = get_the_terms( $post->ID, $taxonomy );

                if ( $terms && ! is_wp_error( $terms ) ) {
                    // Sort terms by parent to handle nested taxonomies.
                    $terms = wp_list_sort( $terms, 'term_id', 'ASC' );

                    $taxonomy_slug = '';
                    foreach ( $terms as $term ) {
                        $ancestors = get_ancestors( $term->term_id, $taxonomy );
                        $ancestors = array_reverse( $ancestors );

                        foreach ( $ancestors as $ancestor_id ) {
                            $ancestor = get_term( $ancestor_id, $taxonomy );
                            $taxonomy_slug .= $ancestor->slug . '/';
                        }
                        $taxonomy_slug .= $term->slug;
                        break; // Use the first term with its ancestors.
                    }
                } else {
                    $taxonomy_slug = 'uncategorized';
                }

                // Replace the placeholder with the actual term slug(s).
                $permalink = str_replace( '%' . $taxonomy . '%', $taxonomy_slug, $permalink );
            }
        }
    }

    return $permalink;
}
add_filter( 'post_type_link', 'adpb_custom_post_type_permalink', 10, 3 );

/**
 * Add custom rewrite rules for permalinks with taxonomy placeholders.
 */
function adpb_add_custom_rewrite_rules() {
    $post_types = get_post_types( array( 'public' => true ), 'objects' );

    foreach ( $post_types as $post_type ) {
        if ( isset( $post_type->rewrite['slug'] ) && strpos( $post_type->rewrite['slug'], '%' ) !== false ) {
            $slug = $post_type->rewrite['slug'];

            // Replace placeholders with regex patterns.
            $regex = $slug;
            preg_match_all( '/%([^%]+)%/', $slug, $matches );

            if ( $matches && isset( $matches[1] ) ) {
                foreach ( $matches[1] as $taxonomy ) {
                    $regex = str_replace( '%' . $taxonomy . '%', '(.+)', $regex );
                }
            }

            $regex = '^' . $regex . '/([^/]+)/?$';

            add_rewrite_rule( $regex, 'index.php?post_type=' . $post_type->name . '&name=$matches[2]', 'top' );
        }
    }
}
add_action( 'init', 'adpb_add_custom_rewrite_rules' );

/**
 * Generate dynamic breadcrumbs for ACF-registered custom post types and taxonomies.
 */
function adpb_dynamic_breadcrumbs() {
    global $post;

    if ( ! is_singular() || ! isset( $post ) ) {
        return;
    }

    $breadcrumbs = array();

    // Home link.
    $breadcrumbs[] = '<a href="' . esc_url( home_url() ) . '">' . esc_html__( 'Home', 'acf-dynamic-permalinks-breadcrumbs' ) . '</a>';

    $post_type = get_post_type( $post );
    $post_type_object = get_post_type_object( $post_type );

    // Post type archive link.
    if ( $post_type_object && $post_type_object->has_archive ) {
        $archive_link = get_post_type_archive_link( $post_type );
        $breadcrumbs[] = '<a href="' . esc_url( $archive_link ) . '">' . esc_html( $post_type_object->labels->name ) . '</a>';
    }

    // Handle taxonomies in the permalink structure.
    $slug = $post_type_object->rewrite['slug'];
    if ( strpos( $slug, '%' ) !== false ) {
        preg_match_all( '/%([^%]+)%/', $slug, $matches );

        if ( $matches && isset( $matches[1] ) ) {
            foreach ( $matches[1] as $taxonomy ) {
                $terms = get_the_terms( $post->ID, $taxonomy );
                if ( $terms && ! is_wp_error( $terms ) ) {
                    // Use the first term.
                    $term = array_shift( $terms );

                    // Get ancestors.
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

    // Current post title.
    $breadcrumbs[] = esc_html( get_the_title() );

    // Output the breadcrumbs.
    echo implode( ' / ', $breadcrumbs );
}

// Add a shortcode to display the breadcrumbs.
add_shortcode( 'adpb_breadcrumbs', 'adpb_dynamic_breadcrumbs' );