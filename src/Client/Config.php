<?php

declare(strict_types = 1);

namespace chabior\Lock\Redis\Client;

use Symfony\Component\Dotenv\Dotenv;

class Config
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * Config constructor.
     * @param string $host
     * @param int $port
     */
    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public static function fromDefaults(): Config
    {
        return new self('127.0.0.1', 6379);
    }

    public static function fromEnv(): Config
    {
        return new self(getenv('REDIS_HOST'), (int)getenv('REDIS_PORT'));
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }
}
