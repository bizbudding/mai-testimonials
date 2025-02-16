# Changelog

## 2.7.3 (12/5/24)
* Changed: Updated the updater.
* Changed: [Performance] Only run ACF filters in back end.
* Changed: [Performance] Defer scripts that don't need to run in head.

## 2.7.2 (4/9/24)
* Changed: Updated the updater.
* Fixed: Dynamic property is deprecated warning.

## 2.7.1 (11/27/23)
* Changed: Updated the updater.

## 2.7.0 (6/20/23)
* Changed: Updated the updater.
* Fixed: Added gitignore file for repo tidiness.

## 2.6.3 (4/4/23)
* Fixed: Wrong args number in Mai Post Grid post types filter.

## 2.6.2 (2/23/23)
* Changed: Added aria-label to testimonial slider previous/next buttons.

## 2.6.1 (1/17/23)
* Fixed: Mai Post Grid disable entry links setting not working.

## 2.6.0 (11/29/22)
* Added: Mai Testimonials sliders now handle left/right swipe motion for previous/next slides.
* Changed: Slider height changes for each slide, so dots and arrows remain close to the bottom of the testimonials content.
* Fixed: Slide not changing correctly when using arrow keys with multiple levels of nested elements.

## 2.5.0 (11/18/22)
* Changed: Uses Mai Theme v2 config for arrow icons when using Mai Testimonials slider block.
* Fixed: Mai Testimonials slider block now works with nested elements like SVGs.
* Fixed: [Developers] Wrong filter name for previous/next arrow.

## 2.4.2 (10/27/22)
* Added: New loading icon when viewing next slide in Mai Testimonials slider block.
* Fixed: Error if Mai Engine is deactivated or deleted while Mai Testimonials is still active.
* Changed: Moved updater to later hook per package recommendations.

## 2.4.1 (5/5/22)
* Fixed: Assets not using current version for cache busting.

## 2.4.0 (5/5/22)
* Added: Details Alignment setting on Mai Testimonials block.
* Added: Margin settings on Mai Testimonials block.
* Added: Support for FacetWP and SearchWP without needing any custom code.
* Changed: Post type is now public so it works out of the box with SearchWP and FacetWP and similar plugins. Singular views are still not enabled because publicly_queryable is still false.
* Changed: Now using cloned fields registered in Mai Engine for performance and consistency in the UI.
* Fixed: Dots were dislaying dark still when nested in a dark background block.
* Fixed: PHP error when trying to activate without Mai Engine plugin active.

## 2.3.1 (3/2/22)
* Fixed: Allow slider dots to wrap if showing more than the container can fit.

## 2.3.0 (1/26/22)
* Added: New Mai Testimonials block!

## 2.2.0 (5/11/21)
* Changed: Post type args now explicitely force no archive or single view. If you need either view you need to use `mai_testimonial_args` filter to change everything how you want it.

## 2.1.0 (3/2/21)
* Added: Testimonials now use the block editor for content.
* Changed: Testimonials now output full content, including blocks and shortcodes (requires Mai Engine 2.11).

## 2.0.3 (2/13/21)
* Added: Mai logo icon to updater.

## 2.0.2 (1/5/21)
* Fixed: Mai Post Grid block still linking testimonials when post type is private.

## 2.0.1 (12/11/20)
* Changd: Plugin header consistency.

## 2.0.0 (12/1/20)
* Added: Support for Mai Theme v2.

## 0.5.3 (12/16/19)
* Changed: Open website links in new tab.
* Changed: Update the updater.

## 0.5.2
* Added: Add 'page-attributes' support to post type so it's easier to change menu order. Now works with Simple Page Ordering plugin out of the box.

## 0.5.1
* Changed: Only run updater in the admin.
* Fixed: Check [grid] 'content' attribute isset before checking if it's a testimonial.
* Fixed: Remove testimonials from search results.

## 0.5.0
* Changed: Updater script to latest version. Load styles via wp_add_inline_style intead of loading a full CSS file just for a few lines of code.
* Changed: Updater point to new repo location.
* Changed: Reference Mai Theme instead of Mai Pro.

## 0.4.0
* Fixed: Constant name referencing Mai Favorites.

## 0.3.0
* Added: Testimonial Categories to allow displaying testimonials in a specific category via [grid content="testimonial" taxonomy="testimonial_cat" terms="123"].
