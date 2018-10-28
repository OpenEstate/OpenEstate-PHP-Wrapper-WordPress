=== OpenEstate PHP-Wrapper ===
Contributors: OpenEstate
Donate link: https://openestate.org/openestate/sponsors
Tags: homes, listings, openestate, promote, properties, property, real estate, realestate, real-estate, realty, wrapper
Requires PHP: 5.0
Requires at least: 3.0.0
Tested up to: 4.9.8
Stable tag: 0.3-SNAPSHOT
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin integrates real estates from OpenEstate-ImmoTool into your WordPress blog.


== Description ==

= English =

The [*OpenEstate* project](https://openestate.org) provides a freeware software - called *OpenEstate-ImmoTool* - for small and medium sized real estate agencies all over the world.

As one certain feature of this software, the managed properties can be exported to any website that supports *PHP*. Together with this plugin the exported properties can be easily integrated into a *WordPress* based website without any frames.

**Please notice:** This plugin version does only work with PHP exports in version 1.x!

= Deutsch =

Im Rahmen des [*OpenEstate*-Projekts](https://openestate.org)) wird unter anderem eine kostenlose Immobiliensoftware unter dem Namen *OpenEstate-ImmoTool* entwickelt. Dies ist eine Softwarelösung für kleine bis mittelgroße Immobilienunternehmen.

Unter anderem können die im *OpenEstate-ImmoTool* verwalteten Immobilien als *PHP*-Skripte auf die eigene Webseite exportiert werden. Mit Hilfe dieses Plugins kann der *PHP*-Export unkompliziert in eine auf *WordPress* basierende Webseite integriert werden.

**Bitte beachten:** Diese Version des Plugins kann nur mit PHP-Exporten in Version 1.x genutzt werden!


== Installation ==

= English =

1.  Create a new export interface inside *OpenEstate-ImmoTool*.
2.  Select **FTP** as transport method and enter the FTP settings of your webspace. You should create a separate directory on your FTP webspace, that is accessible with a web browser.
3.  Select **Website (PHP)** as export format.
4.  Execute the PHP export and the currently available properties are exported to your website.
5.  Install and activate this plugin in your *WordPress* blog.
6.  Configure the path and URL of the export folder in the plugin settings.
7.  After path and URL is correctly configured, a generator is displayed to create certain shortcodes.
8.  Put the generated shortcode anywhere inside your *WordPress* articles or pages.

= Deutsch =

1.  Erzeugen Sie eine neue Export-Schnittstelle im *OpenEstate-ImmoTool*.
2.  Wählen Sie die Transportart **FTP** aus und tragen Sie die Verbindungsdaten des Webspaces ein. Für den Export sollte ein separates Verzeichnis auf dem Webspace angelegt werden, das über den Web-Browser erreichbar ist.
3.  Wählen das Exportformat **Website (PHP)** aus.
4.  Starten Sie den PHP-Export und die aktuell vorhandenen Immobilien werden zur Webseite exportiert.
5.  Installieren und aktivieren Sie dieses Plugin in Ihrem *WordPress* Blog.
6.  Registrieren Sie Pfad und URL des Exportverzeichnisses in den Einstellungen des Plugins.
7.  Nachdem Pfad und URL korrekt konfiguriert wurden, können mit Hilfe eines Generators beliebige Shortcodes erzeugt werden.
8.  Ein Shortcode kann an beliebiger Stelle in einem Artikel oder einer Seite von *WordPress* eingefügt werden.


== Frequently Asked Questions ==

= Who may need this plugin? =

This plugin is focused on users of the freeware real-estate software [*OpenEstate-ImmoTool*](https://openestate.org/immotool).


= Where can I get help, when I have problems with this plugin? =

Register an account at [OpenEstate.org](https://openestate.org/) and [open a ticket](https://openestate.org/support/tickets) with your question.


== Screenshots ==

1.  Setup script path & URL
2.  Generate a shortcode for a property listing.
3.  Generate a shortcode for a property detailled view.
4.  Integrate the shortcode into your articles / pages.


== Changelog ==

= 0.4.0 =

*not released yet*

= 0.3.1 =

-   minor improvements and fixes
-   reworked translation
-   switched license to GPLv2 or later
-   tested against the latest version of *WordPress* (4.9.8)

= 0.3.0 =

-   Bugfix: Make use of the [WordPress Shortcode API](http://codex.wordpress.org/Shortcode_API) in order to fix a compatibility issue with *WordPress* 4.0.1.
-   Bugfix: Don't shutdown the whole website, if the plugin is improperly configured.
-   Bugfix: Show correct home path of the WordPress installation on the plugins admin page.
-   Bugfix: Show correct plugin version number on the plugins admin page.
-   made some syntax fixes
-   translated any source code comments into English

= 0.2.7 =

-   Fixed possible PHP notice message
-   Fixed session initialization

= 0.2.6 =

-   Write wrapped CSS into page header
-   Fixed session initialization

= 0.2.5 =

-   Some smaller fixes

= 0.2.4 =

-   Predefined filters / orderings are handled incorrectly under certain circumstances.
-   Show all available ordering-options within administration dashboard.

= 0.2.3 =

-   Filters are not correctly cleared, if the user switches between different property pages.

= 0.2.2 =

-   Show an information message, if the properties are currently updated via OpenEstate-ImmoTool.

= 0.2.1 =

-   Integration into Wordpress plugin repository

= 0.2 =

-   Some smaller fixes

= 0.1 =

-   First public release


== Upgrade Notice ==

= 0.2.6 =

-   This version requires at least *OpenEstate-ImmoTool* 0.9.22 / 1.0-beta20

= 0.2 =

-   This version requires at least *OpenEstate-ImmoTool* 0.9.13.3
