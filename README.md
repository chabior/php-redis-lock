1. Installation:    
`composer require chabior/php-redis-lock`

2. Config redis from env:    
Library uses two different env variables for redis configuration: `REDIS_HOST` and `REDIS_PORT`.
They had to be manually set before usage of `Config::fromEnv()`.