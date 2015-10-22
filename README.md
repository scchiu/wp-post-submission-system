=== WP Post Submission System ===
Contributors: steve.scchiu
Tags: WordPress, post submission, submission, submission system
Requires at least: 4.0
Tested up to: 4.1
Stable tage: 

It is a submission system for WordPress.

== Description ==

Use shortcode [pss_page_shortcode] to create a submission form. 
It contains three inputs: username (default), email (default), 
and submitted file (PDF only)
In admin page, the submitted items will be shown in a new Menu, called SPapers.

== Installation ==

1. Unzip into your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create a page e.g. "Submission Page" and insert [pss_page_shortcode]
text into content section.
4. In admin page, the submitted items will be shown in a new Menu, called SPapers.
5. That's it :)

== Screenshots ==

== Changelog ==
= 0.1 (2015-10-22) = 
* Commited the first version. 
 
= 0.0.7 (2015-10-18) = 
* Create post-submission-page-shortcode. 
* Check errors for submission at the same page, instead of wp_die(). 

= 0.0.6 (2015-10-09) = 
* Begin to add submission to new post type. 

= 0.0.5 (2015-10-02) = 
* Add new post type. 

= 0.0.4 (2015-09-30) = 
* Modify icon (https://developer.wordpress.org/resource/dashicons/#format-aside) 
* Adjust the position in admin menu. 

= 0.0.3 (2015-09-03) = 
* Create post-submission-system.php. 

= 0.0.2 (2015-09-01) =
* Submission post can be shown in admin page, only sort function works. 
* consider add new submenu in edit.php using its functions. 

= 0.0.1 (2015-08-28) =
* Use wp_media_upload() to design the pdf upload function. 
