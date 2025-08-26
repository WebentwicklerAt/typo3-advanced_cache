<?php

declare(strict_types=1);

namespace WebentwicklerAt\AdvancedCache\Xclass;

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

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\PageDoktypeRegistry;
use TYPO3\CMS\Core\DataHandling\PagePermissionAssembler;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\LinkHandling\TypoLinkCodecService;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Service\OpcodeCacheService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebentwicklerAt\AdvancedCache\Service\AsyncCacheService;

class DataHandler extends \TYPO3\CMS\Core\DataHandling\DataHandler
{
    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly CacheManager $cacheManager,
        #[Autowire(service: 'cache.runtime')]
        protected readonly FrontendInterface $runtimeCache,
        protected readonly ConnectionPool $connectionPool,
        protected readonly LoggerInterface $logger,
        protected readonly PagePermissionAssembler $pagePermissionAssembler,
        protected readonly TcaSchemaFactory $tcaSchemaFactory,
        protected readonly PageDoktypeRegistry $pageDoktypeRegistry,
        protected readonly FlexFormTools $flexFormTools,
        protected readonly PasswordHashFactory $passwordHashFactory,
        protected readonly Random $randomGenerator,
        protected readonly TypoLinkCodecService $typoLinkCodecService,
        protected readonly OpcodeCacheService $opcodeCacheService,
        protected readonly FlashMessageService $flashMessageService,
    ) {
        parent::__construct(
            $this->eventDispatcher,
            $this->cacheManager,
            $this->runtimeCache,
            $this->connectionPool,
            $this->logger,
            $this->pagePermissionAssembler,
            $this->tcaSchemaFactory,
            $this->pageDoktypeRegistry,
            $this->flexFormTools,
            $this->passwordHashFactory,
            $this->randomGenerator,
            $this->typoLinkCodecService,
            $this->opcodeCacheService,
            $this->flashMessageService,
        );
    }

    protected function processClearCacheQueue(): void
    {
        $tagsToClear = [];
        $clearCacheCommands = [];

        foreach (static::$recordsToClearCacheFor as $table => $uids) {
            foreach (array_unique($uids) as $uid) {
                if ($uid <= 0 || !$this->tcaSchemaFactory->has($table)) {
                    return;
                }
                // For move commands we may get more then 1 parent.
                $pageUids = $this->getOriginalParentOfRecord($table, $uid);
                foreach ($pageUids as $originalParent) {
                    [$tagsToClearFromPrepare, $clearCacheCommandsFromPrepare]
                        = $this->prepareCacheFlush($table, $uid, $originalParent);
                    $tagsToClear = array_merge($tagsToClear, $tagsToClearFromPrepare);
                    $clearCacheCommands = array_merge($clearCacheCommands, $clearCacheCommandsFromPrepare);
                }
            }
        }

        $this->cacheManager->flushCachesInGroupByTags('pages', array_keys($tagsToClear));

        // Filter duplicate cache commands from cacheQueue
        $clearCacheCommands = array_unique($clearCacheCommands);
        // Execute collected clear cache commands from page TSconfig
        foreach ($clearCacheCommands as $command) {
            // BEGIN OF CODECHANGE
            //$this->clear_cacheCmd($command);
            if (
                ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()
                && $this->BE_USER->user['uid']
            ) {
                /** @var AsyncCacheService $asyncCacheService */
                $asyncCacheService = GeneralUtility::makeInstance(AsyncCacheService::class);
                $asyncCacheService->addCommand($command);
            } else {
                $this->clear_cacheCmd($command);
            }
            // END OF CODECHANGE
        }

        // Reset the cache clearing array
        static::$recordsToClearCacheFor = [];

        // Reset the original pid array
        static::$recordPidsForDeletedRecords = [];
    }
}
