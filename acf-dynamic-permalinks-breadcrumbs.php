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
<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Modify permalinks to include nested taxonomy terms.
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
                    // Use the first term
                    $term = array_shift( $terms );

                    // Build the term hierarchy (ancestors and term)
                    $taxonomy_slug = '';
                    $ancestors = get_ancestors( $term->term_id, $taxonomy );
                    $ancestors = array_reverse( $ancestors ); // Get ancestors in correct order

                    foreach ( $ancestors as $ancestor_id ) {
                        $ancestor = get_term( $ancestor_id, $taxonomy );
                        $taxonomy_slug .= $ancestor->slug . '/';
                    }

                    // Add the term's slug
                    $taxonomy_slug .= $term->slug;

                } else {
                    $taxonomy_slug = 'uncategorized';
                }

                // Replace the placeholder with the full term hierarchy
                $permalink = str_replace( '%' . $taxonomy . '%', $taxonomy_slug, $permalink );
            }
        }
    }

    return $permalink;
}
add_filter( 'post_type_link', 'custom_post_type_permalink', 10, 3 );

// Add custom rewrite rules to handle nested taxonomy terms in permalinks.
function custom_add_rewrite_rules() {
    $post_types = get_post_types( array( 'public' => true ), 'objects' );

    foreach ( $post_types as $post_type ) {
        if ( isset( $post_type->rewrite['slug'] ) && strpos( $post_type->rewrite['slug'], '%' ) !== false ) {
            $slug = $post_type->rewrite['slug'];

            // Replace placeholders with regex patterns.
            $regex = $slug;
            preg_match_all( '/%([^%]+)%/', $slug, $matches );

            $num_taxonomies = count( $matches[1] );

            if ( $matches && isset( $matches[1] ) ) {
                foreach ( $matches[1] as $taxonomy ) {
                    // Use '(.+?)' to match the full hierarchy non-greedily
                    $regex = str_replace( '%' . $taxonomy . '%', '(.+?)', $regex );
                }
            }

            // Match the post name at the end
            $regex = '^' . $regex . '/([^/]+)/?$';

            // Build the query variables
            $query = 'index.php?post_type=' . $post_type->name . '&name=$matches[' . ( $num_taxonomies + 1 ) . ']';

            // Include taxonomy terms in query vars
            foreach ( $matches[1] as $index => $taxonomy ) {
                $query .= '&' . $taxonomy . '=$matches[' . ( $index + 1 ) . ']';
            }

            // Add rewrite rule
            add_rewrite_rule( $regex, $query, 'top' );
        }
    }
}
add_action( 'init', 'custom_add_rewrite_rules' );

// Custom breadcrumbs function to display nested taxonomy terms.
function custom_dynamic_breadcrumbs() {
    global $post;

    if ( ! is_singular() || ! isset( $post ) ) {
        return;
    }

    $breadcrumbs = array();

    // Home link
    $breadcrumbs[] = '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Cleura', 'your-text-domain' ) . '</a>';

    $post_type      = get_post_type( $post );
    $post_type_obj  = get_post_type_object( $post_type );

    // Post type archive link
    if ( $post_type_obj && $post_type_obj->has_archive ) {
        $archive_link = get_post_type_archive_link( $post_type );
        $breadcrumbs[] = '<a href="' . esc_url( $archive_link ) . '">' . esc_html( $post_type_obj->labels->name ) . '</a>';
    }

    // Handle taxonomies in the permalink structure
    $slug = $post_type_obj->rewrite['slug'];
    if ( strpos( $slug, '%' ) !== false ) {
        preg_match_all( '/%([^%]+)%/', $slug, $matches );

        if ( $matches && isset( $matches[1] ) ) {
            foreach ( $matches[1] as $taxonomy ) {
                $terms = get_the_terms( $post->ID, $taxonomy );
                if ( $terms && ! is_wp_error( $terms ) ) {
                    // Use the first term
                    $term = array_shift( $terms );

                    // Get ancestors
                    $ancestors = get_ancestors( $term->term_id, $taxonomy );
                    $ancestors = array_reverse( $ancestors );

                    // Build term hierarchy
                    foreach ( $ancestors as $ancestor_id ) {
                        $ancestor = get_term( $ancestor_id, $taxonomy );
                        $breadcrumbs[] = '<a href="' . esc_url( get_term_link( $ancestor ) ) . '">' . esc_html( $ancestor->name ) . '</a>';
                    }

                    // Current term
                    $breadcrumbs[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
                }
            }
        }
    }

    // Current post title
    $breadcrumbs[] = esc_html( get_the_title() );

    // Output the breadcrumbs
    echo implode( ' / ', $breadcrumbs );
}

// Replace the theme's breadcrumbs function with the custom one.
if ( ! function_exists( 'x_breadcrumbs' ) ) {
    function x_breadcrumbs() {
        custom_dynamic_breadcrumbs();
    }
}

// Alternatively, if the theme uses action hooks to display breadcrumbs, replace them.
// Uncomment the lines below if needed.

// remove_action( 'x_before_view_global__index', 'x_breadcrumbs' );
// add_action( 'x_before_view_global__index', 'custom_dynamic_breadcrumbs' );