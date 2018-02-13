> This library will allow you to easily create custom taxonomies

## @TODO - Installation

The easiest way to install this package is by using composer from your
terminal:

```bash
composer require moxie-lean/wp-taxonomy
```

Or by adding the following lines on your `composer.json` file

```json
"require": {
  "moxie-lean/wp-taxonomy": "dev-master"
}
```

This will download the file from the [packagist site](https://packagist.org/packages/moxie-lean/wp-cpt) 
and the latest version located on master branch of the repository. 

After that you can need to include the `autoload.php` file in order to
be able to autoload the class during the object creation.

```php
include '/vendor/autoload.php';
```

## @TODO - Using wp-taxonomy
Will be modeled after [WP-CPT](https://github.com/wearenolte/wp-cpt)
