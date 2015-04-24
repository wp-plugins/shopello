=== Plugin Name ===
Contributors: Shopello AB
Donate link: http://shopello.se/
Tags: affiliate, shopping, e-commerce, shopping comparison, shopping search, fashion search engine
Requires at least: 3.8.0
Tested up to: 4.2
Stable tag: 2.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A neat plugin that gives you two shortcodes and enables you to make money through affiliate by providing the whole product list of shopello.se!


== Description ==
This plugin enables your wordpress website to make use of two main shortcodes - one search form and one product listing. Mainly focused on enabling You to display one or more selections of products from the products available at Shopello.se, Shopello.no and Shopello.dk. Using you API key you get access to a simplified admin interface to compose lists of products which are fetched directly from Shopello's JSON API.

Read more at https://www.shopelloapi.com/.

If you'd like to extend or suggest functionality - please notify us at partner@shopello.se! To get your unique API-key start earn money contact same email adress.


== Installation ==
How to install and configure the plugin:

1. Extract the folder "shopello" in your wordpress installations plugin folder.
2. Login to your site and go to the Plugins section.
3. Enable the plugin "Shopello API".
4. Go to "Shopello" and fill in the API-key and API-endpoint.
5. Synchronize the categories before you start using the plugin.

If you do not have an API key, please visit https://www.shopelloapi.com/ to request one.


== Frequently Asked Questions ==

= Why cannot I use any of the shortcodes? =
Make sure you have entered a correct API-key and API-endpoint. If you have problems with this, please contact partner@shopello.se




== Changelog ==

= 2.0.4 =
* Updated to support Wordpress 4.2

= 2.0.3 =
* Minor change of the anchor-links for the product item, you can click anywhere on the product in the listing now.

= 2.0.2 =
* Category filtering fix

= 2.0.1 =
* Small CSS fix for product listing
* Color filtering fix

= 2.0.0 =
* Rewrite of the entire productlisting and filter handling
* Major code improvements of AJAX related backend
* Updated translations (still only English and Swedish available).

= 1.10.2 =
* Improvements in fetching of products

= 1.10.1 =
* Improved handling of 404 errors from API, now it won't destroy the entire page.
* Major code improvements
* Migrated from LESSCSS to SCSS

= 1.10.0 =
* Internationalization and Localization support, we currently have English and Swedish.

= 1.9.8 =
* Major code improvements, no new features.

= 1.9.7 =
* Added initial support for autoloading with composer

= 1.9.6 =
* Added adminpage to check for CURL as dependency and to Ping the API.

= 1.9.5 =
* Minor changes to readme, just layouting of text.

= 1.9.4 =
* Added Screenshots to readme

= 1.9.3 =
* Readme and cosmetic fixes, no code or feature changes.

= 1.9.2 =
* More small fixes. We're progressing to get to version 2.0.0 which will be the first public stable version.

= 1.9.1 =
* Small fixes.

= 1.9.0 =
* Major cleanup.

= 1.0.0 =
* First stable version released.

= 0.9 =
* First version released for Beta testing


== Upgrade Notice ==
When you update your plugin, please test your listings to make sure everything still works.


== Usage Documentation ==
Shopello API Plugin Dokumentation

= Index =
1. Sökfält
  1. Beskrivning
  2. Shortcode-syntax
  3. Shortcode-parametrar
2. Listning
  1. Beskrivning
  2. Shortcode-syntax
  3. Shortcode-parametrar


= 1. Sökfält =
**1.1. Beskrivning**

Sökfältet består av en omslutande div med html attribut för att särskilja elementet. Innehållet är en form-tagg med en label, ett text-input och en submit-knapp - alla med variabla texter.


**1.2 Shortcode**

[shopello_search]
[shopello_search target="/sok" placeholder="Vad letar du efter?" class="min_sok_ruta" label="Sök på min sajt!" search_label="Hitta nu!"]


**1.3. Shortcode-parametrar**

target [string: ex: /sok målsida där shopello_result shortcode används för att visa sökträffar. Uteslutet = postar till samma sida.]
placeholder [string: frivillig: text som syns inuti fältet innan man börjar skriva]
class [string: frivillig: ytterligare css-klasser som läggs på wrappern, för att styla]
label [string: frivillig: textlabel som syns ovanför text-sökfältet]
search_label [string: frivillig (default "Sök"): Ersätter text på sökknapp)


= 2. Resultatlistning =

**2.1 Beskrivning**

Placerar ut en sökresultatlistning baserat på något utav följande:
Fördefinierad lista ifrån admin
Parameterlista och postdata ifrån shopello_search formulär
Parameterlista i shortcode (med eller utan definierat sökord)
Använder fluid layout med klasser för att utöka och kunna justera layout.
Listningen ska kunna pagineras om träfflistans längd överträffar sidstorleken, detta sker med GET-parametrar.


**2.2 Shortcode**

[shopello_result]
[shopello_result title="Hittade %antal träffar för ordet %ord" pagesize=12 categories="1,2,42,44" keyword="nike" class="my-css-class" sort="DESC" sortby="relevance" filters="false"]


**2.3 Shortcode-parametrar**

categories - Komma-separerad lista med kategori-ID'n. Genereras enklast i wp-admin under Inställningar/Shopello API. Ex: 244,1324,15132,623
keyword - Ett bas-sökord som resultaten i listan alltid utgår ifrån. T.ex. "Nike" kommer alltid att välja produkter som matchar sökkriteriet "Nike"
class - CSS-klass som läggs på, så att en CSS-programmerare kan anpassa listningens utseende till Din hemsidas utseende.
pagesize - Hur många resultat som visas på varje sida
title - Skriv en text för att presentera antalet resultat. Standard är: Hittade %antal produkter för sökordet %ord. Det går bra att använd HTML, %antal och %ord är reserverade.
sort - För stigande ordning, skriv ASC. För fallande ordning, skriv DESC
sortby - Sortera på något utav följande: relevance, price
filters - Slår på eller av Filters / Kategori-sektionen. filters="off" stänger av, men som standard är filtren påslagna.

= 3. Produktlistning =

Konceptet Listor går ut på att du skapar produktlistor i Shopello-delen av WP Admin. Du kan sedan applicera
en utav dessa listor per sida eller post. Om du sedan - i PHP eller i WP Editor fältet - använder
shortcoden [shopello_products] så kommer listningen att dyka upp där.

= 4. Visa Filter =

För att visa filter för en sökning så måste du i första hand ha valt några filter (kryssrutor) när du skapade
listningen. För att visa filtren så använder du shortcoden [shopello_filters]
Filtren renderas i kolumn-format (uppifrån och nedåt) och lämpar sig bäst i en sidebar eller egen kolumn.
Det finns en Widget inkluderad i pluginet som förenklar detta och automatiskt
visar filtren när det finns en listning på sidan / posten.


== Screenshots ==
1. Example Frontend
2. Creating Searches
3. API Connection Settings
4. System Test
