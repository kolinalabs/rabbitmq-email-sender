<?php

/**
 * This file is part of the SicesSolar package.
 *
 * (c) SicesSolar <http://sicesbrasil.com.br/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Consumer;

use Symfony\Component\Cache\Adapter\MemcachedAdapter;

/**
 * MinuteRateController
 *
 * @author Jonadabe de Souza Nascimento <jhonndabi.s.n@gmail.com>
 */
final class MinuteRateController
{
    private const LIMIT_PER_MINUTE = 7;

    private const KEY_NAMESPACE = 'rabbitmq_email_sender_usage';

    private const CACHE_TTL = 60; // in seconds

    /**
     * @return bool
     */
    public function checkCanSend(): bool
    {
        return $this->getCurrentUsage() < self::LIMIT_PER_MINUTE;
    }

    /**
     * @return int
     */
    private function getCurrentUsage(): int
    {
        $cacheClient = $this->getCacheClient();

        $currentUsage = $cacheClient->increment(
            $this->getCacheKey(),
            1,
            0,
            self::CACHE_TTL
        );

        if ($currentUsage === false) {
            throw new \RuntimeException('Invalid cache limit value');
        }

        var_dump($currentUsage);

        return $currentUsage;
    }

    /**
     * @return string
     */
    private function getCacheKey(): string
    {
        return \sprintf(
            '%s_%s',
            self::KEY_NAMESPACE,
            \date('i')
        );
    }

    /**
     * @return \Memcached
     */
    private function getCacheClient(): \Memcached
    {
        return MemcachedAdapter::createConnection('memcached://localhost');
    }
}
