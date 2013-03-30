How to use
=============================

You can use local cache as a service:

```yaml
# services.yml

services:
    local.cache:
        class: ESO\LCacheBundle\Cache\Cache
```

```php
/* @var $localCache \ESO\LCacheBundle\Cache\Cache */
$localCache = $this->container->get('local.cache');

$localCache->set('key', 'value');

echo $localCache->get('key'); // will print value
```

The service is singleton so you have to namespace/prefix well your keys to avoid conflicts.

Or you can extend ESO\LCacheBundle\Cache\Cache and expose that class as service, if you want...
