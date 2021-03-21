- slug:php-cache-practical-reliable-multi-driver-multilevel-chainable-cache
- date:Aug 31, 2018 16:18
# PHP Cache - practical, reliable, multi driver, multilevel chainable cache
## The need for a cache
Well, caching is usually known as an optimization practice. It is known for both being very handy and being very [dangerous](https://martinfowler.com/bliki/TwoHardThings.html):
>There are only two hard things in Computer Science: cache invalidation and naming things.
-- Phil Karlton

You don't want to mess with caching on the early stages of your app development. Maybe you should first take a closer look at your actual code? Anthony Ferrara had some ideas on the matter:

<iframe width="560" height="315" src="https://www.youtube.com/embed/qjYyC47rdVs" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Well, if you are sure then you are sure. At least let's see how to make the caching right.

## The perfect cache
We use cache to reduce the cost of heavy tasks: math, calling to external services (DB, APIs, filesystem). Depending on your app load profile and the type of tasks you want to lighten you can choose different caching strategies. Cache is somewhat non-reliable, the data could be there but your app must never assume that cache is always full. It is generally advised that disabling the cache must not stop your application from working.

### Cache storages
To store your data in a cache you have a plenty of options to choose from:

- **local process memory**
  This kind of cache is the closest to your code. You just use the memory of the process who handles your request. Only you have access to such cache and it is wiped out once the request is handled. This storage is particularly useful for reusing fetched data throughout the request.

- **local server memory**
  This storage is almost as fast as the previous one but it remains between your code executions. This could be an opcache, APCu or a local Memcached service. They differ a bit in terms of a protocol but the idea is generally the same as well as performance gains, both use the memory on the same server where your code runs. Some people [extremely love this](https://medium.com/@dylanwenzlau/500x-faster-caching-than-redis-memcache-apc-in-php-hhvm-dcd26e8447ad).

- **local filesystem**
  It is generally not advisable to use local filesystem cache. Cache is usually implemented for a high-load system and it serves a significant load of requests. Disk IO is slower compared to a memory based IO. But there are valid cases for this type of cache.

- **external service**
  You could dedicate a set of servers to run Memcached in a cluster. Network latencies and bandwidth are usually (but not always) slower than working with a local service. Dedicating a special server for caching makes a clean deployment pattern which helps in scaling resources.

### Multilevel Cache with fallbacks
It is handy to set a few layers of caching storages, though you may not always want this. It works like that:

- you set a pool of cache providers, sorted by the speed of access
- it will read sequentially from each cache storage until data is found. In this case, all previous storages will be populated with the data
- next time you request the data it will be served from the fastest storage available.

Usual cache levels:

- process in-memory cache (array cache)
- APCu cache
- memcached cache

### Taggable Cache
As mentioned above, the Cache Invalidation is a problem. Tags are here to help us to invalidate cache easier. You can attach any tags and then you can delete all cache entries having the given tags. This is just handy.

A general notice for storing tags - use only suitable storages for that. While cached data may expire or be pushed by LRU algorithm, tags must remain under any circumstances to enforce data integrity. So whatever storage you use for storing tags, make it durable enough to keep it safe.

Other ways of invalidating items in the cache are by setting explicit TTL settings or just directly removing data by a key.

### PSR-6 / PSR-16
There are many caching implementations out there. [The interoperability group](https://php-fig.org) took it seriously and accepted at least two PSRs for caching implementations.

#### PSR-6
[PSR-6](https://www.php-fig.org/psr/psr-6/) is a set of terms and interfaces which allow reusable cache implementations. Now developers can apply those interfaces to their implementations and you can use any of those interchangeably.

Main terms here are:

- **Item** represents a single key-value pair within the Pool. You only ever deal with cache through this class. If you want something saved: `$pool->getItem('newKey')->set('newValue')`. As you see you never create an Item from scratch but ask a pool to make one for you. Then you can alter and save it back to the pool.
- **Pool** represents a collection of items. A pool is an a-la repository for cache Items. To persist an item: `$pool->save($item)`. The pool also supports deferring of persisting. It means that you can say that you are going to put multiple items to the cache but you don't force it to persist each piece independently, instead you tell the pool to do it when you know no more data is going in the cache.

It is worth noting that this PSR offers no tagging/namespacing techniques.

#### PSR-16
[PSR-16](https://www.php-fig.org/psr/psr-16/) is an independent set of Interfaces to use in simple caching scenarios.  It does not use object but rather deals with scalar types for keys. It also supports default values in case cache has no data for a given key. It also built with performance in mind so you can pass multiple keys in one call and save on round-trip time per request.

It was designed after PSR-6 was approved, so it has built-in adapters for wrapping PSR-6 implementations in PSR-16 compatible ones.

For example, to get a value: `$cache->get('key', 'defaultValue')`.

### Implementations

#### Doctrine Cache
[Doctrine cache](https://github.com/doctrine/cache) is a popular choice for developers nowadays. It supports plenty of drivers: Array, APC, APCu, Filesystem, Memcached, Predis and more. It also supports multi-level caching pools (as explained above).
It does not implement PSR-6/16 but offers its own proven and simple API for developers to use. As we can see, it is hard to agree on things around the developer community.

[Read Doctrine Cache docs](https://www.doctrine-project.org/projects/doctrine-cache/en/latest/index.html).

#### Symfony Cache
[Symfony cache](https://symfony.com/doc/current/components/cache.html) is a component from Symfony family.
It supports both PSR-6 and PSR-16 interfaces as well as a bunch of drivers: Array, APCu, DoctrineCache(nice!), Filesystem, PDO etc. It supports multi-level caching pools.
What is really nice is that it [supports tagging](https://symfony.com/doc/current/components/cache/cache_invalidation.html).

It is worth mentioning that tagging is implemented on the app level. Internally it uses versioning for each tag. This leads to certain inefficiencies because actual invalidation is performed after data is read from the storage. The source code is chaotic and commentless, not to mention regular bug reports on tagging matter. I leave this subject is for further examination.

#### PHP-Cache
[PHP Cache](http://www.php-cache.com/en/latest/) is another popular choice for caching. It supports PSR-6/16. The list of adapters is long: array, APCu, Memcached, MongoDB, Predis and more.
It supports multi-level caching pools. It also supports namespaces and tagging. Tagging is also something hard to implement. As I see this is still [a work-in-progress](https://github.com/php-cache/cache/pull/32) but implementation [looks good](https://github.com/php-cache/taggable-cache/blob/master/TaggablePSR6PoolAdapter.php).

### Cache slamming
This is something you will eventually face when using a cache. It is about a point in time when the cache is empty, and multiple workers started the same heavy task to actually calculate the data.

To prevent this racing we could:

- do nothing and just let concurrent work be done
- warm up our cache regularly
- lock resource while performing a heavy task (so anyone else will wait until it is put back to the cache)

### Serialization
Caching libraries (as suggested by PSRs 6/16) must work with PHP types. To store data in storages libs serialize it. On reading the opposite is performed.
Serialisation is a big subject itself. We can go further and improve cache performance by improving the speed and size of serialized data. For example, [igbinary/igbinary](https://github.com/igbinary/igbinary) PHP extension claims to reduce storage requirements up to 50% of usual by using their optimized serializers. Worth trying!

### P.s. And a practical talk from Eli White
<iframe width="560" height="315" src="https://www.youtube.com/embed/bsZQcbBcXuQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
