<?php

declare(strict_types = 1);

namespace chabior\Lock\Redis;

use chabior\Lock\StorageInterface;
use chabior\Lock\Redis\Client\RedisClientInterface;
use chabior\Lock\ValueObject\LockName;
use chabior\Lock\ValueObject\LockTimeout;
use chabior\Lock\ValueObject\LockValue;

class RedisStorage implements StorageInterface
{
    /**
     * @var RedisClientInterface
     */
    private $client;

    public function __construct(RedisClientInterface $client)
    {
        $this->client = $client;
    }

    public function acquire(LockName $lockName, ?LockTimeout $lockTimeout, LockValue $lockValue): void
    {
        $this->client->lock($lockName, $lockTimeout, $lockValue);
    }

    public function release(LockName $lockName, LockValue $lockValue): void
    {
        $this->client->release($lockName, $lockValue);
    }

    public function isLocked(LockName $lockName, LockValue $lockValue): bool
    {
        return $this->client->isLocked($lockName, $lockValue);
    }
}
