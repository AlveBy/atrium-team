# Atrium Team

People of an organisation for [Atrium](https://github.com/AlveBy/atrium): lawyers, doctors, teachers, designers or volunteers, grouped in departments, with a photo, a position, a short description, a biography and contacts, in every content language and per site.

## Install

```bash
composer require alveby/atrium-team
php artisan atrium:module:install alveby/atrium-team
```

Add `--demo` to fill it with two departments and three people in English, German and Russian.

`atrium:module:install` creates the tables and turns the module on; `atrium:module:remove alveby/atrium-team` turns it off and asks whether to keep the people.

Two modules appear in the admin menu:

- **Team**: a person: name, code (the page address), position, department, sort, photo, short description, biography (a `ContentField`), email and phone.
- **Departments**: name, active, sort.

Names, codes, positions and texts are written per content language; only the default language is required. Both modules belong to a site.

## On the site

With the public theme (`alveby/atrium-theme`) installed, the team has pages of its own and a "Team" item in the site menu:

| Address | Page |
|---|---|
| `/team` | everyone, under the name of their department, in the order of the departments; people without one come last |
| `/team/anna-keller` | one person, with the photo, the biography and the contacts |
| `/de/team/anna-keller` | the same in another content language |

Only active people are shown, by sort, and only active departments head a group. To move the pages, list the model in `config/atrium.php` → `public_urls` yourself (the site's rule wins over the module's):

```php
'public_urls' => [
    Atrium\Team\Models\Member::class => ['prefix' => 'lawyers'],
],
```

To call the module something else ("Lawyers", "Doctors"), override `atrium-team::team.modules.members` in your translations; [`alveby/atrium-solutions`](../solutions/README.md) does it for its sites.

## Change the look

```bash
php artisan vendor:publish --tag=atrium-team-views
php artisan vendor:publish --tag=atrium-team-lang
```

The list view lands in `resources/views/vendor/atrium-team/index.blade.php`; a person's page is the theme's record page. The admin labels are in 15 languages; English and Russian are written by hand, the others are machine translations.

## Tests

From the repository root:

```bash
vendor/bin/phpunit packages/team/tests
```

## Contributing

This package lives in `packages/team` of [AlveBy/atrium](https://github.com/AlveBy/atrium). Issues and pull requests go there.

## License

MIT
