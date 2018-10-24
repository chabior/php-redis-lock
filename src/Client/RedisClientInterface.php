<?php

declare(strict_types = 1);

namespace chabior\Lock\Redis\Client;

use chabior\Lock\ValueObject\LockName;
use chabior\Lock\ValueObject\LockTimeout;
use chabior\Lock\ValueObject\LockValue;

interface RedisClientInterface
{
    public function lock(LockName $lockName, ?LockTimeout $lockTimeout, LockValue $lockValue): void;

    public function release(LockName $lockName, LockValue $lockValue): void;

    public function isLocked(LockName $lockName, LockValue $lockValue): bool;
}
