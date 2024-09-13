**ACF Dynamic Permalinks and Breadcrumbs** enhances the functionality of custom post types and taxonomies registered via the Advanced Custom Fields (ACF) plugin.

This plugin automatically:

- Replaces taxonomy placeholders in permalinks with the actual term slugs, supporting nested categories.
- Adjusts breadcrumbs to reflect the correct hierarchy for custom post types and taxonomies.
- Supports multiple custom post types and taxonomies without additional configuration.

**Features:**

- **Dynamic Permalinks:** Use placeholders like `%taxonomy%` in your custom post type rewrite slugs, and the plugin will replace them with the appropriate term slugs.
- **Nested Categories:** Handles nested taxonomies, ensuring permalinks and breadcrumbs include parent terms.
- **ACF Integration:** Specifically built to work with custom post types and taxonomies registered via ACF.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/acf-dynamic-permalinks-breadcrumbs` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Flush rewrite rules by visiting **Settings > Permalinks** and clicking **Save Changes**.
4. Ensure your custom post types and taxonomies are configured with the appropriate rewrite slugs using placeholders (e.g., `resource/%resource-category%`).

== Usage ==

**Configuring Custom Post Types and Taxonomies in ACF:**

- **Custom Post Types:**
  - In ACF, go to **Custom Fields > Post Types** and edit your custom post type.
  - In the **Permalink Rewrite** section, choose **Custom Permalink**.
  - Set the slug to include taxonomy placeholders, e.g., `resource/%resource-category%`.
  - Save the post type.

- **Taxonomies:**
  - In ACF, go to **Custom Fields > Taxonomies** and ensure your taxonomies are hierarchical if using nested categories.
  - Save the taxonomy.

- **Flush Rewrite Rules:**
  - After making changes, go to **Settings > Permalinks** and click **Save Changes**.

**Displaying Breadcrumbs:**

- **Using Shortcode:**
  - Add `[adpb_breadcrumbs]` to your content where you want the breadcrumbs to appear.

- **Using PHP Function:**
  - Add the following code to your theme templates (e.g., `single.php`):

    ```php
    if ( function_exists( 'adpb_dynamic_breadcrumbs' ) ) {
        adpb_dynamic_breadcrumbs();
    }
    ```

== Frequently Asked Questions ==

= Do I need to use ACF to use this plugin? =

Yes, this plugin is specifically designed to work with custom post types and taxonomies registered via the Advanced Custom Fields (ACF) plugin.

= How do I configure permalinks with taxonomy placeholders? =

When registering your custom post type in ACF, set the **Permalink Rewrite** to **Custom Permalink** and include taxonomy placeholders in the slug (e.g., `resource/%resource-category%`).

= Does this plugin support nested taxonomies? =

Yes, it supports nested taxonomies. The plugin will include parent term slugs in permalinks and breadcrumbs.

= How do I flush rewrite rules? =

Go to **Settings > Permalinks** in your WordPress admin dashboard and click **Save Changes** without altering any settings.

== Screenshots ==

1. **Custom Post Type Permalink Settings in ACF.**
2. **Example of a Permalink with Nested Categories.**
3. **Breadcrumbs Displaying Hierarchical Terms.**

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.

== License ==

This plugin is licensed under the GPL v2 or later.

== Contact ==

For issues or feature requests, please visit the [GitHub repository](https://github.com/thesaadmirza/acf-dynamic-permalinks-breadcrumbs).