<?php

declare(strict_types=1);

namespace WebentwicklerAt\AdvancedCache\Service;

/*
 * This file is part of the advanced_cache extension for TYPO3 CMS.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebentwicklerAt\AdvancedCache\Cache\Frontend\VariableFrontend;

class AsyncCacheService implements SingletonInterface
{
    protected ?BackendUserAuthentication $backendUser = null;

    protected FrontendInterface $cache;

    protected string $tag = '';

    public function __construct()
    {
        $this->backendUser = $GLOBALS['BE_USER'];
        $this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('tx_advancedcache_queue');
        $this->tag = is_object($this->backendUser) ? 'be_user_' . $this->backendUser->user['uid'] : 'all';
    }

    public function isFlushed(): bool
    {
        $commands = $this->getCommands();
        return count($commands) ? false : true;
    }

    public function flush(): void
    {
        $commands = $this->getCommands();
        $this->flushCommands();
        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([], []);
        foreach ($commands as $command) {
            $dataHandler->clear_cacheCmd($command);
        }
    }

    public function addCommand(string|int $command): void
    {
        $cacheIdentifier = sha1($command);
        if (!$this->cache->get($cacheIdentifier)) {
            $tags = [
                'all',
                $this->tag,
            ];
            $this->cache->set($cacheIdentifier, $command, $tags);
        }
    }

    protected function getCommands(): array
    {
        $commands = [];
        if ($this->cache instanceof VariableFrontend) {
            $commands = $this->cache->getByTag($this->tag);
        }
        return $commands;
    }

    protected function flushCommands(): void
    {
        $this->cache->flushByTag($this->tag);
    }
}
