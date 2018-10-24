<?php

declare(strict_types = 1);

namespace chabior\Lock\Redis\Tests;

use chabior\Lock\Redis\Client\Config;
use chabior\Lock\Redis\Client\PHPRedisClient;
use chabior\Lock\Redis\RedisStorage;
use chabior\Lock\ValueObject\LockName;
use chabior\Lock\ValueObject\LockTimeout;
use chabior\Lock\ValueObject\LockValue;
use PHPUnit\Framework\TestCase;

class RedisStorageTest extends TestCase
{
    public function testCreate(): void
    {
        $client = new PHPRedisClient(
            Config::fromEnv(),
            new \Redis()
        );

        $storage = new RedisStorage($client);
        $lockName = new LockName(sha1(microtime()));
        $lockValue = LockValue::fromRandomValue();
        $storage->acquire(
            $lockName,
            LockTimeout::fromSeconds(2),
            $lockValue
        );
        $this::assertTrue($storage->isLocked($lockName, $lockValue));
    }

    public function testIsLockedWithExpired(): void
    {
        $client = new PHPRedisClient(
            Config::fromEnv(),
            new \Redis()
        );

        $storage = new RedisStorage($client);
        $lockName = new LockName(sha1(microtime()));
        $lockValue = LockValue::fromRandomValue();
        $storage->acquire(
            $lockName,
            LockTimeout::fromMiliSeconds(1),
            $lockValue
        );
        usleep(2000);
        $this::assertFalse($storage->isLocked($lockName, $lockValue));
    }

    public function testIsLockedWithDifferentName(): void
    {
        $client = new PHPRedisClient(
            Config::fromEnv(),
            new \Redis()
        );

        $storage = new RedisStorage($client);
        $lockName = new LockName(sha1(microtime()));
        $lockValue = LockValue::fromRandomValue();
        $storage->acquire(
            $lockName,
            LockTimeout::fromSeconds(2),
            $lockValue
        );
        $this::assertFalse($storage->isLocked(new LockName(sha1(microtime())), $lockValue));
    }

    public function testRelease(): void
    {
        $client = new PHPRedisClient(
            Config::fromEnv(),
            new \Redis()
        );

        $storage = new RedisStorage($client);
        $lockName = new LockName(sha1(microtime()));
        $lockValue = LockValue::fromRandomValue();
        $storage->acquire(
            $lockName,
            LockTimeout::fromSeconds(2),
            $lockValue
        );

        $this::assertTrue($storage->isLocked($lockName, $lockValue));
        $storage->release($lockName, $lockValue);
        $this::assertFalse($storage->isLocked($lockName, $lockValue));
    }

    public function testReleaseFailWithDifferentName(): void
    {
        $client = new PHPRedisClient(
            Config::fromEnv(),
            new \Redis()
        );

        $storage = new RedisStorage($client);
        $lockName = new LockName(sha1(microtime()));
        $lockValue = LockValue::fromRandomValue();
        $storage->acquire(
            $lockName,
            LockTimeout::fromSeconds(2),
            $lockValue
        );

        $this::assertTrue($storage->isLocked($lockName, $lockValue));
        $storage->release(new LockName(sha1(microtime())), $lockValue);
        $this::assertTrue($storage->isLocked($lockName, $lockValue));
    }

    public function testReleaseFailWithDifferentValue(): void
    {
        $client = new PHPRedisClient(
            Config::fromEnv(),
            new \Redis()
        );

        $storage = new RedisStorage($client);
        $lockName = new LockName(sha1(microtime()));
        $lockValue = LockValue::fromRandomValue();
        $storage->acquire(
            $lockName,
            LockTimeout::fromSeconds(2),
            $lockValue
        );

        $this::assertTrue($storage->isLocked($lockName, $lockValue));
        $storage->release($lockName, LockValue::fromRandomValue());
        $this::assertTrue($storage->isLocked($lockName, $lockValue));
    }

    public function testAcquireLockWithoutTimeout(): void
    {
        $client = new PHPRedisClient(
            Config::fromEnv(),
            new \Redis()
        );

        $storage = new RedisStorage($client);
        $lockName = new LockName(sha1(microtime()));
        $lockValue = LockValue::fromRandomValue();
        $storage->acquire(
            $lockName,
            null,
            $lockValue
        );
        $this::assertTrue($storage->isLocked($lockName, $lockValue));
        sleep(1);
        $this::assertTrue($storage->isLocked($lockName, $lockValue));
    }
}
