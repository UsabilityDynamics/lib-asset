## Concepts

 - Requires will only produce a single script tag per page request, but multiple may be supported in the future.

## Size

  17.7 KB minified
  69.8 KB unminified.

## Usage

### Initialize Require Client

data-id
data-base-url
data-model
data-main

### Initialize WordPress Handler

```php
// Initialize.
$_requires = new \UsabilityDynamics\Requires;

// Configure..
$_requires->set(array(
  'paths' => '/scripts/app.state.js',
  'scopes' => 'public',
  'debug' => true
)));

// Define Libraries.
$_requires->add( 'udx.knockout' );
$_requires->add( 'wpp.ui.supermap' );
$_requires->add( 'wpp.ui.admin.settings' );

// Render HTML tag.
$_requires->render_tag();
```

### In Header

```html
<script data-debug="true" src="//cdn.udx.io/requires.js"></script>
```

### In Body

```html
<div data-requires="udx.wp-property.supermap"></div>
<div data-requires="udx.elastic-filter"></div>
<div data-requires="crowdfavorite.carrington-build.slider"></div>
<div data-requires="bootstrap.carousel"></div>
```

## License

(The MIT License)

Copyright (c) 2013 Usability Dynamics, Inc. &lt;info@usabilitydynamics.com&gt;

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
'Software'), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.