=== Snippets ===
Contributors: businessxpand
Tags: text, replacement, themes, theme
Requires at least: 2.6.5
Tested up to: 2.8.4
Stable tag: 0.9.4

Replace areas of your themes or content with user defined text or HTML.

== Description ==

Do you need a placeholder for a fixed text, like the name of the current band at number one, your affiliate tracking codes, or just the name of your favourite fruit fly?

Snippets allows you to create fixed areas within your theme for this text to be displayed, and can easily be updated through your WordPress admin in the appearance menu. The snippet content can also be used in your posts and pages with a simple tag.

For theme developers we have included a functions.php file that allows you to create hardcoded snippet areas in your themes that your users can update with their own information.

If you find any bugs then please tell us, if we are not told then they will not be fixed.

**New features for version 0.9.2**

* Select input types for your snippet fields, such as text, textarea or checkbox.

**Bug fixes for 0.9.4**

* Slashes stripped from values.

**Bug fixes for 0.9.3**

* Invalid argument error if no snippets have been setup.

**Bug fixes for 0.9.2**

* Blank field values not getting saved.

== Installation ==

1. Upload the `snippets` directory to your `/wp-content/plugins/` directory.
1. Activate the plugin through the `Plugins` menu in WordPress.
1. Navigate to new `Snippets Setup` menu item under the `Settings` menu.
1. Add some new fields, and select the input type.
1. Navigate to the `Snippets` menu under the `Appearance` menu.
1. Add some values to your fields.
1. Add the following function call to your theme: <?php snippets_value( 'field name' ); ?> where `field name` is the exact name of the field you want to reference.
1. Alternatively you can reference the snippet in your content with the follow: [snippets:field name] where `field name` is the exact name of the field you want to reference.
1. The functions.php file can be copied to your own theme development to allow you to distribute snippet enabled areas within your themes, please read the comments in the source code of this file for more information on how to use it.