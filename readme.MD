# Custom Permalinks and Breadcrumbs Fix

**Version:** 1.0.0  
**Author:** [Saad Mirza](https://github.com/thesaadmirza)  
**License:** GPL v2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

---

## Description

Custom Permalinks and Breadcrumbs Fix is a WordPress plugin that modifies permalinks of custom post types to include nested taxonomy terms (categories and subcategories) in the URL structure. It also updates breadcrumbs to reflect the full hierarchy of taxonomy terms, ensuring both URLs and breadcrumbs accurately represent the content hierarchy on your website.

**Features:**

- Replace `%taxonomy%` placeholders in custom post type permalinks with the full hierarchy of term slugs.
- Add custom rewrite rules to handle nested taxonomy terms in permalinks, preventing 404 errors.
- Generate breadcrumbs that display the full hierarchy of taxonomy terms.
- Optionally replace your theme's breadcrumbs function with the custom breadcrumbs for seamless integration.

---

## Installation

1. **Upload the Plugin Files:**

   - Download the plugin ZIP file.
   - Upload the extracted folder to the `/wp-content/plugins/` directory, or upload the ZIP file through the WordPress admin panel under **Plugins > Add New > Upload Plugin**.

2. **Activate the Plugin:**

   - Navigate to **Plugins > Installed Plugins** in your WordPress admin panel.
   - Locate **Custom Permalinks and Breadcrumbs Fix** and click **Activate**.

3. **Flush Rewrite Rules:**

   - After activating the plugin, go to **Settings > Permalinks**.
   - Click **Save Changes** without altering any settings. This step flushes the rewrite rules to ensure the new permalink structures work correctly.

---
