Installation
=============================

Requirements
------------

* PHP >= 5.4

Installation
------------

### Composer

Add the following line to your composer.json file.

```js
//composer.json
{
    //...
    "require": {
        //...
        "eso/lcache-bundle": "dev-master"
    }
    //...
}
```

If you haven't allready done so, get Composer:

```bash
curl -sS https://getcomposer.org/installer | php
```

And install the new bundle

```bash
php composer.phar update eso/lcache-bundle
```

Add to your AppKernel.php

```php
// app/AppKernel.php
<?php
    // ...
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new ESO\LCacheBundle\ESOLCacheBundle(),
        );
    }
```
