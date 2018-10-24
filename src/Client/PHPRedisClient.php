<?php

declare(strict_types = 1);

namespace chabior\Lock\Redis\Client;

use chabior\Lock\ValueObject\LockName;
use chabior\Lock\ValueObject\LockTimeout;
use chabior\Lock\ValueObject\LockValue;

class PHPRedisClient implements RedisClientInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var bool
     */
    private $isConnected;

    /**
     * @var \Redis
     */
    private $redis;

    public function __construct(Config $config, \Redis $redis)
    {
        $this->config = $config;
        $this->redis = $redis;
    }

    public function lock(LockName $lockName, ?LockTimeout $lockTimeout, LockValue $lockValue): void
    {
        $this->connect();

        $options = [
            'nx'
        ];
        if ($lockTimeout) {
            $options['px'] = $lockTimeout->asMiliSeconds();
        }

        $this->redis->set(
            $lockName->getName(),
            $lockValue->getValue(),
            $options
        );
    }

    public function release(LockName $lockName, LockValue $lockValue): void
    {
        $this->connect();

        $val = $this->redis->evaluate(
            "
            if redis.call(\"get\",KEYS[1]) == ARGV[1] then
                return redis.call(\"del\",KEYS[1])
            else
                return 0
            end
            ",
            [
                $lockName->getName(),
                $lockValue->getValue()
            ],
            1
        );
    }

    public function isLocked(LockName $lockName, LockValue $lockValue): bool
    {
        $this->connect();

        $value = $this->redis->get($lockName->getName());

        return $value === $lockValue->getValue();
    }

    private function connect(): void
    {
        if (!$this->isConnected) {
            $this->redis->connect(
                $this->config->getHost(),
                $this->config->getPort()
            );
            $this->isConnected = true;
        }
    }

    public function __destruct()
    {
        $this->isConnected = false;
        unset($this->redis);
    }
}
