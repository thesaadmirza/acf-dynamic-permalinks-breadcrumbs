<?php
/*
Plugin Name:       Custom Permalinks and Breadcrumbs Fix
Plugin URI:        https://github.com/thesaadmirza/custom-permalinks-breadcrumbs-fix
Description:       Fixes permalinks and breadcrumbs for custom post types with taxonomy placeholders.
Version:           1.0.0
Requires at least: 5.0
Tested up to:      6.3
Requires PHP:      7.0
Author:            Saad Mirza
Author URI:        https://github.com/thesaadmirza
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:       custom-permalinks-breadcrumbs-fix
Domain Path:       /languages
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function custom_post_type_permalink( $permalink, $post, $leavename ) {
    $post_type = get_post_type( $post );

    // Get the post type object
    $post_type_object = get_post_type_object( $post_type );

    // Ensure we have a valid post type object and rewrite settings
    if ( ! $post_type_object || ! isset( $post_type_object->rewrite['slug'] ) ) {
        return $permalink;
    }

    $slug = $post_type_object->rewrite['slug'];

    // Check if the slug contains placeholders
    if ( strpos( $slug, '%' ) !== false ) {
        // Find all placeholders in the slug
        preg_match_all( '/%([^%]+)%/', $slug, $matches );

        if ( $matches && isset( $matches[1] ) ) {
            foreach ( $matches[1] as $taxonomy ) {
                // Get the terms for this taxonomy
                $terms = get_the_terms( $post->ID, $taxonomy );

                if ( $terms && ! is_wp_error( $terms ) ) {
                    // Use the first term's slug
                    $taxonomy_slug = array_shift( $terms )->slug;
                } else {
                    $taxonomy_slug = 'uncategorized';
                }

                // Replace the placeholder with the actual term slug
                $permalink = str_replace( '%' . $taxonomy . '%', $taxonomy_slug, $permalink );
            }
        }
    }

    return $permalink;
}
add_filter( 'post_type_link', 'custom_post_type_permalink', 10, 3 );

/**
 * Add custom rewrite rules for permalinks with taxonomy placeholders.
 */
function custom_add_rewrite_rules() {
    $post_types = get_post_types( array( 'public' => true ), 'objects' );

    foreach ( $post_types as $post_type ) {
        if ( isset( $post_type->rewrite['slug'] ) && strpos( $post_type->rewrite['slug'], '%' ) !== false ) {
            $slug = $post_type->rewrite['slug'];

            // Replace placeholders with regex patterns.
            $regex = $slug;
            preg_match_all( '/%([^%]+)%/', $slug, $matches );

            if ( $matches && isset( $matches[1] ) ) {
                foreach ( $matches[1] as $taxonomy ) {
                    $regex = str_replace( '%' . $taxonomy . '%', '([^/]+)', $regex );
                }
            }

            $regex = '^' . $regex . '/([^/]+)/?$';

            add_rewrite_rule( $regex, 'index.php?post_type=' . $post_type->name . '&name=$matches[2]', 'top' );
        }
    }
}
add_action( 'init', 'custom_add_rewrite_rules' );