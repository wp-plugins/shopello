=== Plugin Name ===
Contributors: 203creative
Donate link: http://203creative.se/
Tags: productivity, sales, affiliate, shopello
Requires at least: 3.8.0
Tested up to: 3.9
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A neat plugin that gives you two shortcodes and enables you to make money through affiliate by providing the whole product list of shopello.se!


== Description ==
This plugin enables your wordpress website to make use of two main shortcodes - one search form and one product listing. Mainly focused on enabling You to display one or more selections of products from the products available at Shopello.se. Using you API key you get access to a simplified admin interface to compose lists of products which are fetched directly from Shopello.se's JSON API.

If you'd like to extend or suggest functionality - please notify us at info@shopello.se !


== Installation ==
This section describes how to install the plugin and get it working.

1 - Extract the folder "shopello_wp_plugin" in your wordpress installations plugin folder
2 - Login to your site and go to the Plugins section
3 - Enable the plugin "Shopello WP Plugin"
4 - Go to Settings -> Shopello API and fill in the API-key and API-endpoint

PS: You can contact info@shopello.se to get your set of API keys, which identifies your site and ultimately your profits.


== Frequently Asked Questions ==
I cannot use any of the shortcodes?

Make sure you have entered a correct API-key and API-endpoint. If you have problems with this, please contact info@shopello.se


== Changelog ==

= 1.0.0 =
* First stable version released.

= 0.9 =
* First version released for Beta testing


== Upgrade Notice ==
When (if) you update your plugin, please test your listings to make sure everything is still alright


== Usage Documentation ==
Shopello API Plugin Dokumentation

******************
Index
******************
1. Sökfält
1.1 Beskrivning
1.2 Shortcode-syntax
1.3 Shortcode-parametrar
2. Listning
2.1 Beskrivning
2.2 Shortcode-syntax
2.3 Shortcode-parametrar


******************
0. Sökfält
******************

1.1. Beskrivning

Sökfältet består av en omslutande div med html attribut för att särskilja elementet. Innehållet är en form-tagg med en label, ett text-input och en submit-knapp - alla med variabla texter.
--------------------------------------------------------------------------


1.2. Shortcode

[shopello_sok]
[shopello_sok target="/sok" placeholder="Vad letar du efter?" class="min_sok_ruta" label="Sök på min sajt!" search_label="Hitta nu!"]
--------------------------------------------------------------------------

1.3. Shortcode-parametrar

target [string: ex: /sok målsida där shopello_result shortcode används för att visa sökträffar. Uteslutet = postar till samma sida.]
placeholder [string: frivillig: text som syns inuti fältet innan man börjar skriva]
class [string: frivillig: ytterligare css-klasser som läggs på wrappern, för att styla]
label [string: frivillig: textlabel som syns ovanför text-sökfältet]
search_label [string: frivillig (default "Sök"): Ersätter text på sökknapp)
--------------------------------------------------------------------------


******************
2. Resultatlistning
******************

2.1 Beskrivning

Placerar ut en sökresultatlistning baserat på något utav följande:
Fördefinierad lista ifrån admin
Parameterlista och postdata ifrån shopello_sok formulär
Parameterlista i shortcode (med eller utan definierat sökord)
Använder fluid layout med klasser för att utöka och kunna justera layout.
Listningen ska kunna pagineras om träfflistans längd överträffar sidstorleken, detta sker med GET-parametrar.
--------------------------------------------------------------------------


2.2 Shortcode

[shopello_resultat]
[shopello_resultat title="Hittade %antal träffar för ordet %ord" pagesize=12 categories="1,2,42,44" keyword="nike" class="my-css-class" sort="DESC" sortby="relevance" filters="false"]
--------------------------------------------------------------------------


2.3 Shortcode-parametrar

categories - Komma-separerad lista med kategori-ID'n. Genereras enklast i wp-admin under Inställningar/Shopello API. Ex: 244,1324,15132,623
keyword - Ett bas-sökord som resultaten i listan alltid utgår ifrån. T.ex. "Nike" kommer alltid att välja produkter som matchar sökkriteriet "Nike"
class - CSS-klass som läggs på, så att en CSS-programmerare kan anpassa listningens utseende till Din hemsidas utseende.
pagesize - Hur många resultat som visas på varje sida
title - Skriv en text för att presentera antalet resultat. Standard är: Hittade %antal produkter för sökordet %ord. Det går bra att använd HTML, %antal och %ord är reserverade.
sort - För stigande ordning, skriv ASC. För fallande ordning, skriv DESC
sortby - Sortera på något utav följande: relevance, price
filters - Slår på eller av Filters / Kategori-sektionen. filters="off" stänger av, men som standard är filtren påslagna.
