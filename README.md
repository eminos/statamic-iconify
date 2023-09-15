# Statamic Iconify

A Statamic plugin for the fantastic [Iconify](https://iconify.design/) framework.

This plugin gives you an Iconify fieldtype in Statamic where you can search and pick an icon from the huge icon library that Iconify provides.

## Installation

Install this plugin using composer.

```cli
composer require eminos/statamic-iconify
```

## Usage

You can use any of Iconifys methods/components to display the icon in the frontend.
Here is an example with their web component that fetches the icon on demand through their API.

```html
<iconify-icon icon="{{ iconify_field }}"></iconify-icon>
```

Read more about how you can use the icons in the [Iconify usage documentation](https://iconify.design/docs/usage/).

## TODO

- Save the SVG when you pick an icon.
- Statamic Tag to render the icon (on demand or saved SVG).
- More filtering options. For example limit to specific icon vendors or licences.
- Make the search modal nicer.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.