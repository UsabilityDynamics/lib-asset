## Concepts

 - Typically only one requires.js tag is expected per HTML page, but multiple may be supported in the future.

## Size

  17.7 KB minified
  69.8 KB unminified.

## Usage

### In Header

```html
  <script data-main="/js/main" src="//cdn.udx.io/requires.js"></script>
  <script data-main="/js/data" src="//cdn.udx.io/requires.js"></script>
```

### In Body

```html
  <div data-requires="udx.wp-property.supermap"></div>
  <div data-requires="udx.elastic-filter"></div>
  <div data-requires="crowdfavorite.carrington-build.slider"></div>
  <div data-requires="bootstrap.carousel"></div>
```

### Initialize WordPress Handler

```php
  // Enable JavaScript Library Loading.
  $_requires = new \UsabilityDynamics\Requires;

  // Update Settings.
  $_requires->set( 'paths', '/scripts/app.state.js' );
  $_requires->set( 'scopes', 'public' );
  $_requires->set( 'debug', true );

  // Add Libraries.
  $_requires->add( 'ui.wpp.supermap' );
  $_requires->add( 'ui.elastic-filter' );
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