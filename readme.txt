=== YD Search Functions ===
Contributors: ydubois
Donate link: http://www.yann.com/
Tags: Wordpress, WP, plugin, search, snippet, snippets, Google, result, results, hit-highlighting, highlighting, hit, search engine, find, function, functions, template function, template, search template, abstract, indexing, text, full-text, full-text indexing, options, settings, French, English, hit, result, log, logging, statistics, search log
Requires at least: 2.9.1
Tested up to: 2.9.2
Stable tag: trunk

Improved search tools and template functions including Google-like search result snippets (on-the-fly contextual abstract), search statistics and hit-highlighting.

== Description ==

= Display Wordpress search results like Google! =

This Wordpress plugin installs a set of **new template functions** that lets you customize the way search results are displayed in your Wordpress search page.
You can use on-the-fly generated Google-style text **snippets** to help make search result abstracts more useful.
You can do on-the-fly hit-highlighting (in-line xml-based, not through a cumbersome asynchronous Javascript).
Your customized search result snippets being included in the delivered page content, they can be indexed by third-party web search engines.

The CSS styling of snippet results is compatible with Google custom search engine stylesheets for complete integration.

The design of the search result display is highly customizable through the plugin's settings page: you can set-up if and how the snippet's date is displayed (localized date display is supported).
You can choose the abstract/snippet length and the way highlighted search hits are rendered.

For an example of what it can look like, look here: http://www.nogent-citoyen.com/recherche/tour+eiffel (in this example, the left columns displays Wordpress-generated results using this plugin, while the right column displays asynchronous Google custom search engine results)

= Check-out what your visitors are searching for! =

The plugin installs a new dashboard (admin panel widget) which shows you in real time what your users are searching for on your blog (top-search listing, basic search statistics).
It implements search logging, search statistics, and gives you a **most-searched widget** and template function to display the **list of top-searches**.
The **most frequently searched listing** is manageable in your admin panel: you can choose to reset counters for any search expression, or ban that expression from being listed.
Most spiders and spam-robots are filtered-out of the search counter to prevent search link-spamming.

The plugin has its own admin options/settings page.

It is **fully internationalized**. The search functions are UTF-8 multibyte compatible and do take care of accentuated text.
The date display supports localization issues. 

Base package includes .pot file for translation of the interface, and English and French versions.

**NB: although already fully functional, this plugin is still in active development stage, and new features are added on a regular basis to help further improve search-result customization and display.**

= Available template functions =

* Use `<?php echo yd_search_snippet() ?>` in your search results page template loop (`search.php`).
For example, in your search template theme file (`search.php`), find the place where it says `the_content()` and replace it like this:
`
    <?php if( function_exists( 'yd_search_snippet' ) ) yd_search_snippet() ?>
`

* Use `<?php echo yd_most_searched() ?>` anywhere in any page template.
For example, in your home page template theme file (`index.php`), insert something like this:
`
    <?php if( function_exists( 'yd_most_searched' ) ) yd_most_searched() ?>
`

= Active support =

Drop me a line on my [YD Search Functions plugin support site](http://www.yann.com/en/wp-plugins/yd-search-functions "Yann Dubois' Advanced Search Functions plugin for Wordpress") to report bugs, ask for a specific feature or improvement, or just tell me how you're using the plugin.

= Description en Français : =

Ce plug-in Wordpress installe des fonctions avancées d'affichage des résultats de recherche.
Vous pouvez notamment l'utiliser pour afficher des extraits de texte intelligents dans vos résultats ("snippets" à la Google).
Il n'extraira que les passages du texte contenant les mots-clés et expressions recherchés.
Il pourra également surligner dans le texte les expressions et mots recherchés.
La fonction de surlignage se fait en ligne dans le flux XML et non en mode asynchrone avec un Javascript lent et lourd.
Les snippets et les mots surlignés faisant partie du texte de vos pages de résultats de recherche, ils peuvent eux-même être indéxés par les moteurs de recherche externes du web, donnant de la valeur originale à vos pages de résultats.

Par ailleurs, ce plugin active l'enregistrement des recherches effectuées par les visiteurs sur votre site, 
et vous remonte au niveau de votre interface d'administration la liste des recherches les plus fréquentes.

Une fonction de template et/ou un widget vous permettent d'afficher la liste des recherches les plus courantes pour vos visiteurs.

Le style CSS généré pour les résultats de recherche est compatible avec les feuilles de styles utilisées par le moteur de recherche Google custom pour une intégration parfaite (voir exemple ci-dessous).

Le plugin a sa propre page d'option dans l'administration.
Il est entièrement internationalisé.

La distribution standard inclut le fichier de traduction .pot et les versions française et anglaise.

Le plugin peut fonctionner avec n'importe quelle langue ou jeu de caractères.
Les fonctions de recherche et de surlignage gèrent les caractères français accentués de façon intelligente.
Elles sont compatibles utf-8 multibyte.

Pour voir un exemple du fonctionnement de ce plug-in en grandeur nature, regardez ici : http://www.nogent-citoyen.com/recherche/pavillon+baltard
(dans cet exemple, la colonne de gauche affiche des résultats Wordpress générés avec ce plug-in alors que la colonne de droite affiche des résultats de l'API du moteur de recherche Google custom)

Pour toute aide ou information en français, laissez-moi un commentaire sur le [site de support du plugin YD Search Functions](http://www.yann.com/en/wp-plugins/yd-search-functions "Yann Dubois' Advanced Search Functions for Wordpress").

= Funding Credits =

Original development of this plugin has been paid for by [www.Nogent-Citoyen.com](http://www.nogent-citoyen.com "Nogent Citoyen"). Please visit their site!

Le développement d'origine de ce plugin a été financé par [www.Nogent-Citoyen.com](http://www.nogent-citoyen.com "Nogent Citoyen"). Allez visiter leur site !

= Translation =

If you want to contribute to a translation of this plugin or its documentation, please drop me a line by e-mail or leave a comment on the plugin's page.
You will get credit for your translation in the plugin file and this documentation, as well as a link on this page and on my developers' blog.

== Installation ==

1. Unzip yd-search-functions.zip
1. Upload the `yd-search-functions` directory and all its contents into the `/wp-content/plugins/` directory of your WP site
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the option admin page to customize the settings if necessary.

For specific installations, some more information might be found on the [YD Search Functions plugin support page](http://www.yann.com/en/wp-plugins/yd-search-functions "Yann Dubois' Advanced Search Functions plugin for Wordpress")

== Frequently Asked Questions ==

= Where should I ask questions? =

http://www.yann.com/en/wp-plugins/yd-search-functions

Use comments.

I will answer only on that page so that all users can benefit from the answer. 
So please come back to see the answer or subscribe to that page's post comments.

= Puis-je poser des questions et avoir des docs en français ? =

Oui, l'auteur est français.
("but alors... you are French?")

== Screenshots ==

1. TODO
For an example of what it can look like, look here: http://www.nogent-citoyen.com/recherche/tour+eiffel (in this example, the left columns displays Wordpress-generated results using this plugin, while the right column displays asynchronous Google custom search engine results)


== Plugin options/settings page ==

Use the plugin's own options/settings page to customize settings if necessary.


== Revisions ==

* 0.1.0 Original beta version.
* 0.2.0 First improvements (function interface, options/settings, no debug code)
* 0.2.1 Bugfix: caps search string (uppercase) / thanks to Inky for reporting
* 0.3.0 Complete settings, multi-word highlighting fixed, improved settings page design
* 0.4.0 Added search logging, search statistics, most searched listings, top-search widget and lots of new customization options

== Changelog ==

= 0.1.0 =
* Initial release
= 0.2.0 =
* Improvements (2010/04/25)
* Better function interface (function call parameters have become optional. Parameter order has changed.)
* Simplified function implementation
* No more debug messages visible
* Option set for customization
= 0.2.1 =
* Bugfix: caps search string (uppercase) / thanks to Inky for reporting
= 0.3.0 =
* Don't put ellipsis at beginning if not extracted or cut at beginning
* Default locale  = option + default from blog config
* Default time format = option
* Display date = option
* Link to options/settings page in main plugin list text
* Make tokenized abstract & highlight work again
* Make plural form (ending only) work again
* Plural form = option
* Improved options/settings page design
* Linkbackware (instead of silent backlinks...)
* Translations of new features
= 0.4.0 =
* Implement search logging (option)
* Implement most searched list
* Search list management (admin pannel)
* Most-searched listing function
* Most-searched listing-related settings
* Display number of different searches today / week / month / year
* Display total number of different searches (since first date)
* Widget for displaying most searched listing
* Widget options panel
* Safeguard to automatically count and close <b> tags
* Hit-highlighting optional
* Accent-aware highlighting optional
* Case-sensitive highlighting optional
* Advanced / multi-word highlighting optional
* Settings update bug
* Robot filter
* Translations of new features

== Upgrade Notice ==

= 0.1.0 =
Initial release.
= 0.2.0 =
Upgrade the usual way. Beware the function call parameters have changed. See changelog for details.
= 0.2.1 =
Upgrade the usual way. See changelog for details.
= 0.3.0 =
Upgrade the usual way. See changelog for details.
= 0.4.0 =
Upgrade the usual way. Reset widget options to create new search log database table and give default values to new options. See changelog for details.

== To Do ==

Test. Final release.
Look at comments on top of main plugin file to get a general idea of what is coming ahead in future versions.

== Did you like it? ==

Drop me a line on http://www.yann.com/en/wp-plugins/yd-search-functions

And... *please* rate this plugin --&gt;