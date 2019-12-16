### Changelog

#### 0.5.3 (12/16/19)
* Changed: Open website links in new tab.
* Changed: Update the updater.

#### 0.5.2
* Added: Add 'page-attributes' support to post type so it's easier to change menu order. Now works with Simple Page Ordering plugin out of the box.

#### 0.5.1
* Changed: Only run updater in the admin.
* Fixed: Check [grid] 'content' attribute isset before checking if it's a testimonial.
* Fixed: Remove testimonials from search results.

#### 0.5.0
* Changed: Updater script to latest version. Load styles via wp_add_inline_style intead of loading a full CSS file just for a few lines of code.
* Changed: Updater point to new repo location.
* Changed: Reference Mai Theme instead of Mai Pro.

#### 0.4.0
* Fixed: Constant name referencing Mai Favorites.

#### 0.3.0
* Added: Testimonial Categories to allow displaying testimonials in a specific category via [grid content="testimonial" taxonomy="testimonial_cat" terms="123"].
