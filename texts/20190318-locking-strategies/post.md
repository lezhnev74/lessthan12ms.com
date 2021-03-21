- date:Mar 18, 2019 13:11
- slug:data-locking-strategies-in-php-apps-practical-approach
# Data locking strategies in PHP apps. Practical approach
Locking is a method of synchronization of data access. We need a locking mechanism to make sure no one else is working with the data we are about to read or change. The purpose of locking can be various:
- **Data integrity**. Make sure that no one else is reading or changing the data we are working with.
- **Expensive operations**. Make sure that we limit "expensive" operations concurrency. This effectively implements throttling.
- and probably more.

Notes: 
- Here I write about "advisory locking", which means all processes agreed upon to NOT doing something until the lock is acquired. 
- Also, I am not paying much attention to versions of certain locking storages, just describing general practical abilities of such systems.

## Things to consider when choosing a locking strategy:
- **What is your app's deployment pattern (single server / multiserver)**. The most important one. If you run just a single server, the locking is much easier to implement. In case of distributed locking, you need also to keep in ming network outages and external services failures and mitigate these risks.
- **How many concurrent requests you anticipate**. In other words, how many processes will wait for the lock if it has been acquired? This number can affect overall performance, for example, one process can hold one connection to the database while it is waiting.
- **How long the lock usually hold**. Again how many waiters are in the queue and for how long. If "waiting" is expensive then the more there are processes waiting the more performance is suffering.
- **How important the lock is**. If the lock is acquired or the locking storage is not available (for any reason) can you proceed without acquiring the lock or you need to wait for the lock no matter what?

## 1. Local locking
This is the best case. If your application resides on the single server, then local locking is the most reliable option. PHP offers at least two ways to use local locking:

### 1.1. **File locks**
This locking based on the filesystem API - [`flock()`](http://php.net/manual/en/function.flock.php) (part of the PHP core). Pick a shared file and allow just a single process to access it. 
```php
<?php

$fp = fopen("/tmp/lock.txt", "r+");

# this will wait until lock is acquired
# for "no waiting" use: LOCK_EX | LOCK_NB
if (flock($fp, LOCK_EX)) {  // acquire an exclusive lock

    #... SAFE TO WORK HERE
    flock($fp, LOCK_UN);    // release the lock
} else {
    # only reached in case of a lock is nonblocking or general error
    echo "Couldn't get the lock!";
}

fclose($fp);
```

### 1.2. **Semaphores**
This type is in-memory only so this one is the fastest one but the implementation is [not good in PHP](https://compwright.com/2012-12-08/what-is-wrong-with-phps-semaphore-extension/). This functionality is not part of the core and requires [installing an extension](http://php.net/manual/en/sem.installation.php). The extension indeed takes some space.
```php
<?php

$key = 1; # pick an integer, should be unique on this machine, so better use ftok() here
$semaphoreId = sem_get($key); # get a semaphore resource

# will wait here untill lock can be acquired
# for no waiting use: sem_acquire($semaphoreId, true)
if(sem_acquire($semaphoreId)){  
    
    #... SAFE TO WORK HERE
    sem_release($semaphoreId);
} else {
    # only reached in case of a lock is not acquired or general error
    echo "Couldn't get the lock!";
}

sem_remove($semaphoreId);
```

## 2. Distributed Locking
When your application is spread across a number of servers you need to make sure that concurrent processes on each server has the single locking storage available. The storage and locking strategy must anticipate network issues and unexpected locking storage failures (like if Redis instance has shut down).

**Important note.** Distributed locks used for data integrity is a quite hard topic. Because if you use it for 100% correctness you need to deal with network partitioning, delays in delivery of packets, node failures and such. There are special tools which offer such complex guaranteed locks over the network but these are out of the scope of this post. The best locking strategy considered here is transactional locking offered by relational DBMS like MySQL, Postgres etc.

### 2.1. Relational database
Relational databases offer a good locking strategy - locking inside of a transaction. One needs to begin a new transaction, acquire a lock and release it upon when the transaction is committed. DB hides all the complexity of the concurrent access, you just set if you want to wait until the lock is acquired or you want to continue execution if the lock is taken.

#### 2.1.1. PostgreSQL
PostgreSQL has a neat feature which allows locking of an [arbitrary lock](https://www.postgresql.org/docs/9.5/functions-admin.html#FUNCTIONS-ADVISORY-LOCKS-TABLE) (not related to a certain table or row). Which is quite handy for acquiring application-level locks:
> PostgreSQL provides a means for creating locks that have application-defined meanings. These are called advisory locks because the system does not enforce their use — it is up to the application to use them correctly. Advisory locks can be useful for locking strategies that are an awkward fit for the MVCC model. For example, common use of advisory locks is to emulate pessimistic locking strategies typical of so-called "flat file" data management systems. While a flag stored in a table could be used for the same purpose, advisory locks are faster, avoid table bloat, and are automatically cleaned up by the server at the end of the session.

```php
<?php

$sharedInteger = 100;
\DB::statement('Begin');
\DB::statement('select pg_advisory_xact_lock(?);', $sharedInteger); # here we wait untill the lock is available

# ... SAFE TO WORK HERE

\DB::statement('Commit'); # release the lock automatically
```


#### 2.1.2. MySQL
Again MySQL offers [app-level non-transactional locks](https://dev.mysql.com/doc/refman/5.7/en/locking-functions.html), which work within a session (Postgres, on the other hand, offers both variants).
The lock is automatically released upon session termination or manually.

```php
$sharedLockName = 'lock_name';
$timeoutSeconds = 60;
\DB::statement('SELECT GET_LOCK(?,?);', [$sharedLockName, $timeoutSeconds]); # here we wait untill the lock is available

# ... SAFE TO WORK HERE

\DB::statement('RELEASE_LOCK(?);', [$sahredLockName]);
```


### 2.2. Redis storage
Redis has a famous [Redlock](https://redis.io/topics/distlock) algorithm which indeed is [not reliably](https://martin.kleppmann.com/2016/02/08/how-to-do-distributed-locking.html) for data integrity (see the notice above). Because of this, the best strategy here is just to use a single Redis node and [set](https://redis.io/commands/set) command. If the node fails - it fails for everyone.

Example of usage such locking strategy:
```php
<?php

# connect to the node
$redis = new \Redis();
$redis->connect($host, $port, $timeout);

# acquire a lock for $ttl miliseconds
$ttl = 1000 * 60; # 1 minute
$sharedKeyName = 'lock_key';
if($instance->set($sharedKeyName, 1, ['NX', 'PX' => $ttl])) {
    # .. LOCK ACQUIRED, do the job
    
    $redis->del($sharedKeyName);
};

```

### 2.3. Memcached storage
Just like in the case of Redis, Memcache offers "sometimes reliable" [advisory locking mechanism](https://github.com/memcached/memcached/wiki/ProgrammingTricks#ghetto-central-locking). Again we rely on `add` command which fails if a name has been set already.

```php
$sharedLockName = 'lock_name';
$lockTimeout = 60;
if($lock = $memcache->add($sharedLockName, 1, $lockTimeout)) {
    
    # ... LOCK ACQUIRED, do the job
    
    $memcache->delete($sharedLockName);
}

```


## 3. Case: queue workers process the same job concurrently
When you deal with a queue what actually happens:
1. you put your job into the queue
2. the next available worker locks the job in the queue for a limited (configurable) time period
3. the worker process the job
4. when the work is done the job is removed from the queue

This is a normal way any queue works. You have to anticipate an edge case when a worker takes more time to process a job that the configured timeout. If you don't handle such cases you have a good chance to see concurrent workers process the same job. It looks like this:

1. you put your job into the queue
2. the next available workerA locks the job in the queue for a limited (configurable) time period
3. the worker process the job for a long time
4. the queue automatically release the lock and puts the job back
5. **a new workerB locks the same job and starts processing it simultaneously with the first one**
6. workerA finishes the job and deletes it from the queue
7. **workerB finishes the same job and also attempts to delete it from the queue but the job has been deleted already**.

In the last case you may see similar errors in the log:
```
Pheanstalk\Exception\ServerException
Job 205890 NOT_FOUND: does not exist or is not reserved by the client
```
This is a real error report I caught in one of the projects I was working on. This is a clear signal that double execution happened.

Solutions to consider:
1. Kill the worker just before the timeout elapses and raise a warning to the developer. This at least will prevent multiple executions.
2. Greatly increase the timeouts and assume that any long-running processes will still be within this time range. 



## 4. References:
- [Prevent multiple PHP scripts at the same time / Exakat](https://www.exakat.io/prevent-multiple-php-scripts-at-the-same-time)
- [Distributed locks with Redis](https://redis.io/topics/distlock)
- [ProgrammingTricks / Ghetto central locking](https://github.com/memcached/memcached/wiki/ProgrammingTricks#ghetto-central-locking)
- [Wiki / Lock (computer science)](https://en.wikipedia.org/wiki/Lock_(computer_science))
- [How to do distributed locking / Martin Kleppmann](https://martin.kleppmann.com/2016/02/08/how-to-do-distributed-locking.html)
- [php-lock/lock](https://github.com/php-lock/lock) - good implementation for the locking system in PHP