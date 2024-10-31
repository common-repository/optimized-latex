=== Optimized LaTeX ===
Contributors: tnyholm
Tags: latex, math, equations, formulas, expressions, equations
Stable tag: trunk
Requires at least: 2.7
Tested up to: 3.0.1
Donate link: http://www.webfish.se/wp/donate

Optimized LaTeX creates images from inline $\LaTeX$ code in your posts and comments. This plugin optimizes the images with better cache and ajax-requests.

== Description ==

If you want math forumals on your website and dont want the page to load 1 second slower, this is the plugin for you.
This is an optimized version of two other latex plugins. It is easy to use and the [Latex syntax](http://www.webfish.se/wp/plugins/optimized-latex#LaTeX-Syntax "Webfish - Latex syntax") 
gives you alot of examples how to write nice latex. 

This plugin does **not** allow you to use your own latex server. (If you have your own latex server you would probaply not need this plugin anyway.) 


== Installation ==
This is some brief installation notes. For more information see [Webfish](http://www.webfish.se/wp/plugins "Wordpress plugins")

1. Upload the plugin source to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Make sure the folder `/wp-content/cache/` exists and is writable. 


== Frequently Asked Questions ==

= How do I add LaTeX to my posts? =

This plugin uses the [WordPress Shortcode Syntax](http://codex.wordpress.org/Shortcode_API).
Enter your LaTeX code inside of a `[latex]...[/latex]` shortcode.

`
[latex]e^{\i \pi} + 1 = 0[/latex]
`

You may alternatively use the following equivalent syntax reminiscent of LaTeX's inline
math mode syntax.

`
$latex e^{\i \pi} + 1 = 0$
`

That is, if you would have written `$some-code$` in a LaTeX document, just
write `$latex some-code$` in your WordPress post.

For more frequent questions visit [Webfish](http://www.webfish.se/wp/plugins/optimized-latex#FAQ "Webfish - Optimized Latex FAQ")


== ChangeLog ==

You will find the change log at [Webfish](http://www.webfish.se/wp/plugins/optimized-latex#Changelog "Webfish - Optimized Latex changelog")
