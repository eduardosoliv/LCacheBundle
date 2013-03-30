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

If you are going to inject the service in your class's you have ESO\LCacheBundle\Cache\CacheInterface to make easily mockable.

**The service is singleton so you have to namespace/prefix well your keys to avoid conflicts.**

Probably the more flexible approach is to extend ESO\LCacheBundle\Cache\Cache so you can add particular features and override methods. If you want to have more than one instance you can have multiple Symfony 2 services.

