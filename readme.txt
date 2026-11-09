=== SM ===
Contributors: suaramalaysia
Requires at least: 6.7
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: news, block-patterns, full-site-editing, custom-colors, custom-logo, featured-images, wide-blocks, three-columns, accessibility-ready

A block child theme of Twenty Twenty-Five for the Suara Malaysia news site: red category bar, featured hero, category sections, newsletter, contact form, donate page and Malaysian design motifs.

== Description ==

SM is the Suara Malaysia news theme. It needs Twenty Twenty-Five (the parent theme) to be installed. It ships with the site's original articles, pages, categories and menus so a new install looks finished immediately, and everything is editable from Appearance > Theme Options.

= Features =

* Compact masthead, red category bar with dropdown menus, and a search panel.
* Homepage: intro, featured hero with four side stories, per-category sections, editor's picks, most read, active topics and a newsletter panel. No story repeats across these blocks.
* Colour schemes (six presets plus custom) from Appearance > Theme Options > Colours.
* Contact form with stored entries and CSV export, newsletter with stored subscribers and CSV export, donate form that hands off to your payment page.
* Reading progress bar, image lightbox (Lightbox2), drop caps, social icons, pretty search URLs, optional hidden login page and unlisted posts.
* AVIF image output for new uploads (needs AVIF support in GD or Imagick).
* Seeded demo content in `seed/content.json`, removable with one click (or automatically when you switch themes).
* Malaysian identity: tumpal (pucuk rebung) bands, songket lattice and a KL skyline footer, drawn as SVG.
* Accessibility: skip link, visible focus on every control, labelled forms, keyboard-operable menus, 24px minimum touch targets (44px on touch screens), checked against WCAG 2.2 AA.

== Installation ==

1. Install and keep Twenty Twenty-Five (Appearance > Themes > Add New).
2. Upload this theme as a folder and activate it. The demo content imports on activation.
3. Open Appearance > Theme Options. Work through the tabs from left to right: General, Colours, Homepage, Newsletter, Contact form, Donate, Reading, Search & Login, Social.
4. Set your donation checkout URL (Donate tab) and social profile URLs (Social tab).
5. Open Settings > Permalinks and click Save once if search or login URLs do not work straight away.

== Usage ==

Each Theme Options tab starts with step-by-step instructions. In short:

* General: contact details, copyright line (`{year}` is replaced), search description, and whether demo content is deleted when you switch themes.
* Colours: click a preset or edit single colours. Editing a colour switches the preset to Custom.
* Homepage: choose the hero source, tick and drag categories to order the sections, set stories per section.
* Newsletter: edit the panel text; paste an external form URL to send subscribers to Mailchimp or Brevo instead of WordPress.
* Contact form: set the recipient and the fields (`type|name|label|required`). Entries appear under Appearance > Form entries.
* Donate: checkout URL, currency, preset amounts and donation types.
* Reading: toggle the progress bar, lightbox, drop caps and image format.
* Search & Login: change the search URL base. Bookmark the new address before hiding the login page.
* Social: icon style and size, then one URL per profile. Place icons with the Social icons block or the `[sm_social]` shortcode.

Blocks and shortcodes provided by the theme: Hero, Sections, Related, Newsletter, Contact form, Donate form, Social icons, Share, Reading time, Editor's picks, Active topics, Most read; shortcodes `[sm_contact_form]`, `[sm_newsletter]`, `[sm_social]`.

== Frequently Asked Questions ==

= Will switching themes delete my content? =
Only the demo content this theme imported (and only when "Delete demo content on deactivation" is on). Anything you wrote or uploaded yourself is never touched.

= Why is the footer skyline not an image I can change? =
It is an SVG in `assets/images/motifs/`. Replace the file or override `--sm-skyline` in your own CSS.

= Images do not become AVIF. =
Your server needs AVIF support in GD (PHP 8.1+ with libavif) or Imagick. Theme Options shows a notice when it is missing; choose "Keep original format" until your host enables it.

= Why do the contact and newsletter forms have no nonce? =
Pages are cached, and a nonce frozen into cached HTML makes the form fail for visitors. The forms use a honeypot field and a rate limit (5 attempts per 10 minutes per visitor) instead. Everything in the admin uses capability and nonce checks.

== Privacy ==

The theme sets no cookies and loads no analytics, fonts, scripts or images from other websites. All assets are bundled. Share buttons are plain links that only contact the social network when a reader clicks them.

Data the theme stores in your database:

* Contact form entries (if "Store submissions" is on): name, email, message, page, IP address, browser string. Appearance > Form entries.
* Newsletter subscribers (when no external form URL is set): name, email, IP address. Subscribers list.
* Article view counts: a number per article, not linked to a person.

Suggested wording is added to Settings > Privacy > Policy Guide. Delete entries from the lists above to erase them.

== Changelog ==

= 1.1.0 =
* Donations through PayPal.Me, with an optional monthly gift link.
* Official Lightbox2 2.12.0 restored (loads jQuery from WordPress core on articles); Kagnue Outline font removed; view counts work behind page caches; homepage blocks cached for ten minutes.
* Save as PDF button (html2pdf.js), featured images in RSS feeds, optional security headers and Mastodon verification tag.

= 1.0.0 =
* Initial release: design system, seeded content, integrated features (contact form, newsletter, lightbox, progress bar, unlisted posts, search permalinks, hidden login, social icons, drop caps, view counts).
* Theme Options screen redesigned: cards, toggle switches, visual selectors, range sliders with live values, per-tab instructions, keyboard-accessible tabs. Requires the edit_theme_options capability.
* Malaysian identity (tumpal, songket, skyline, CC0 photo).
* WCAG 2.2 AA pass: visible form labels, focus indicators, skip link focus, hero text growth, 44px touch targets.

== Upgrade Notice ==

= 1.1.0 =
Security headers are now on by default (Theme Options > Search & Login). Turn them off if your host already sets them.

= 1.0.0 =
First release. Theme Options now needs the edit_theme_options capability (administrators have it by default).

== Copyright ==

SM WordPress Theme, (C) 2025 Suara Malaysia. SM is distributed under the terms of the GNU General Public License v2 or later (see LICENSE).

Bundled resources and their licences:

* Twenty Twenty-Five (parent theme), GPLv2 or later, WordPress contributors. Not bundled: install separately.
* html2pdf.js 0.14.0, MIT, (C) 2026 Erik Koopmans (bundles html2canvas 1.4.1 MIT and jsPDF, plus further MIT/BSD-licensed parts listed in assets/vendor/html2pdf/LICENSE-third-party.txt). https://github.com/eKoopmans/html2pdf.js . Minified file loaded only when a reader clicks Save as PDF; unminified bundle in assets/vendor/html2pdf/src.
* Feed images, security headers and the Mastodon tag (inc/features/feed.php) are adapted from the "ChrisWiegman.com" plugin by Chris Wiegman, GPLv2.
* Lightbox2 2.12.0, MIT, (C) Lokesh Dhakar. https://lokeshdhakar.com/projects/lightbox2/ . Minified files in assets/vendor/lightbox2/js and /css, unminified sources in assets/vendor/lightbox2/src. Licence: assets/vendor/lightbox2/LICENSE. Needs jQuery from WordPress core.
* Social icons (assets/icons/social): GPL pack of 24x24 single-path icons, mostly from CC0 sources. LinkedIn and Scribd icons by Font Awesome, CC BY 4.0. https://fontawesome.com . Details in assets/icons/social/LICENSE.txt.
* Fonts (assets/fonts, see assets/fonts/README.md):
  * Special Elite, Apache License 2.0, Astigmatic.
  * Manrope, SIL Open Font License 1.1, Mikhail Sharanda. https://fonts.google.com/specimen/Manrope
  * Dropcaps, SIL Open Font License 1.1 (letter outlines derived from Almendra). Licence: assets/fonts/dropcaps/LICENSE-OFL.txt.
  * Sabon Next LT (Monotype), Kagnue (Youthlabs Studio) and Ferments (Haksen Studio): commercial web licences held by the Suara Malaysia site owner. These three are NOT GPL and must be removed (the theme falls back to Georgia and system fonts) before the theme is redistributed to anyone else.
* Photograph "Sultan Abdul Samad Building in 2023", Wikimedia Commons, CC0 1.0. assets/images/malaysia/. See CREDITS.md there.
* Motifs (assets/images/motifs): tumpal, songket, skyline. Drawn for this theme, GPLv2 or later.
* Demo content and images (seed/): articles, pages and photographs from suaramalaysia.online, supplied by the site owner. Not licensed for reuse by others.
