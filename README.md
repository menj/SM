# SM — Suara Malaysia theme

A block child theme of **Twenty Twenty-Five** built for [suaramalaysia.online](https://suaramalaysia.online/). It implements the Suara Malaysia design: serif masthead, red category bar, yellow category tags, featured-story hero, per-section story grids, rose newsletter panel and a minimalist footer.

## Requirements

- WordPress 6.7 or newer, PHP 7.4 or newer
- The parent theme **Twenty Twenty-Five** installed (it ships with WordPress)

## Install

1. Upload this folder as `wp-content/themes/SM` (or zip it and upload under Appearance → Themes).
2. Activate **SM**. On first activation the theme imports `seed/content.json`: categories, the nine archived articles with their original dates and featured images, the About, Contact, Donate, Privacy and Editorial pages, and the four navigation menus.
3. Open **Appearance → SM Theme** to review the tabbed settings.

## Settings (Appearance → SM Theme)

| Tab | What it controls |
| --- | --- |
| General | Phone, email, copyright line, back-to-top button, homepage meta description, seed import |
| Colours | Preset schemes (Suara Red, Navy, Forest, Monochrome) or custom colours for primary, tag, newsletter, links, text and surfaces |
| Homepage | Hero source, which categories appear as sections and in what order, stories per section |
| Newsletter | Heading, success message, optional external action URL (Mailchimp, Brevo…), admin notification |
| Contact form | Recipient, intro text, field list, success message, storage toggle |
| Donate | Checkout URL, currency, preset amounts, donation types |
| Reading | Reading progress bar (position, height, colour, post types) and image lightbox |
| Search & Login | Pretty search permalinks (`/search/term/`) and the hidden login URL |
| Social | Icon style and size, plus a URL field for each of the 44 supported networks |

## Features built into the theme

These replace plugins the site previously relied on. No plugin is required.

| Feature | Where |
| --- | --- |
| Contact form with stored entries and CSV export | `inc/forms.php`, Appearance → Form entries |
| Newsletter sign-up with subscriber list and CSV export | `inc/newsletter.php`, Appearance → Subscribers |
| Image lightbox (Lightbox2, MIT) | `inc/features/lightbox.php`, `assets/vendor/lightbox2` |
| Reading progress bar | `inc/features/progress-bar.php` |
| Unlisted posts and pages | `inc/features/unlist.php` — toggle in the editor sidebar |
| Pretty search permalinks | `inc/features/search-permalinks.php` |
| Hidden login URL | `inc/features/hide-login.php` |
| Minimalist social icons (44 networks) | `inc/features/social.php`, `assets/icons/social` |
| SEO title, meta description, Open Graph and NewsArticle schema | `inc/seo.php` — disabled automatically when Yoast, Rank Math, SEOPress or AIOSEO is active |

## Blocks

Available under the **Theme** category in the editor: Featured stories, Homepage sections, Section heading, You may be interested, Newsletter panel, Contact form, Donate form, Social icons. Shortcodes `[sm_newsletter]`, `[sm_contact_form]` and `[sm_social]` render the same output.

## Menus and hierarchy

The primary menu supports two levels. Add child categories (Posts → Categories → choose a parent) and nest them under their parent in Appearance → Editor → Navigation. On desktop (1101px and wider) children open as a dropdown on hover, focus or Enter; below that the hamburger overlay shows them as an indented list. Homepage sections, the footer sitemap and Active Topics list top-level categories only, and a parent's archive includes its children's stories. The seed ships three examples: Religion and Education under Society, History under Special Reports.

## Structure

```
SM/
├── assets/
│   ├── css/        theme.css, editor.css, admin.css
│   ├── js/         theme.js, editor.js, admin.js
│   ├── fonts/lora  Lora variable fonts (SIL OFL)
│   ├── icons/social  SVG glyphs
│   └── vendor/lightbox2
├── inc/            PHP modules (settings, blocks, forms, newsletter, seo, seed, features/)
├── parts/          header.html, footer.html
├── patterns/       header, footer, newsletter, query grid, page layouts
├── seed/           content.json + images (imported on activation)
├── templates/      front-page, home, index, archive, category, single, page, page-*, search, 404
├── functions.php
├── style.css
└── theme.json
```

## Seed content

`seed/content.json` is plain JSON. Posts, pages and menus are matched by slug, so re-importing from Appearance → SM Theme → General never creates duplicates. The "Re-import and overwrite" button pushes the JSON versions over edited copies. Article bodies in the source archive were summaries only; each seeded post carries that summary as its lead paragraph and can be extended in the editor.

## Licences

Theme code: GPL v2 or later. Lora: SIL Open Font License 1.1. Lightbox2: MIT. Social icons: GPL / CC0, except LinkedIn and Scribd which are Font Awesome CC BY 4.0 (credit is emitted in the page footer when either is shown).
