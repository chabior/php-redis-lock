<?php

declare(strict_types = 1);

namespace chabior\Lock\Redis\Tests;

use chabior\Lock\Handler\CallbackHandler;
use chabior\Lock\Handler\FailCallbackHandler;
use chabior\Lock\Lock;
use chabior\Lock\Redis\Client\Config;
use chabior\Lock\Redis\Client\PHPRedisClient;
use chabior\Lock\Redis\RedisStorage;
use chabior\Lock\ValueObject\LockName;
use chabior\Lock\ValueObject\LockTimeout;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

class LockTest extends TestCase
{
    public function testAcquireLock(): void
    {
        $client = new PHPRedisClient(
            Config::fromEnv(),
            new \Redis()
        );

        $storage = new RedisStorage($client);
        $name = new LockName(sha1(microtime()));
        $lock = (new Lock($storage))
            ->success(new CallbackHandler(function (Lock $lock) use($name) {
                $this::assertTrue($lock->isLocked($name));
            }))
            ->fail(new FailCallbackHandler(function (Lock $lock, \Throwable $exception) {
                throw new AssertionFailedError($exception->getMessage());
            }))
        ;

        $lock->acquire($name);
    }

    public function testReleaseLock(): void
    {
        $client = new PHPRedisClient(
            Config::fromEnv(),
            new \Redis()
        );

        $storage = new RedisStorage($client);
        $name = new LockName(sha1(microtime()));
        $lock = (new Lock($storage, LockTimeout::fromSeconds(2)))
            ->success(new CallbackHandler(function (Lock $lock) use($name) {
                $this::assertTrue($lock->isLocked($name));
            }))
            ->fail(new FailCallbackHandler(function (Lock $lock, \Throwable $exception) {
                throw new AssertionFailedError($exception->getMessage());
            }))
        ;

        $lock->acquire($name);
        $lock->release($name);
        $this::assertFalse($lock->isLocked($name));
    }
}
