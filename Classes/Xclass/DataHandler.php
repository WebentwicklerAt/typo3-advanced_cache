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

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebentwicklerAt\AdvancedCache\Service\AsyncCacheService;

class DataHandler extends \TYPO3\CMS\Core\DataHandling\DataHandler
{
    /**
     * @inheritdoc
     */
    protected function processClearCacheQueue()
    {
        $tagsToClear = [];
        $clearCacheCommands = [];

        foreach (static::$recordsToClearCacheFor as $table => $uids) {
            foreach (array_unique($uids) as $uid) {
                if (!isset($GLOBALS['TCA'][$table]) || $uid <= 0) {
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

        /** @var CacheManager $cacheManager */
        $cacheManager = $this->getCacheManager();
        $cacheManager->flushCachesInGroupByTags('pages', array_keys($tagsToClear));

        // Filter duplicate cache commands from cacheQueue
        $clearCacheCommands = array_unique($clearCacheCommands);
        // Execute collected clear cache commands from page TSConfig
        foreach ($clearCacheCommands as $command) {
            // BEGIN OF CODECHANGE
            if (TYPO3_MODE === 'BE' && is_object($this->BE_USER) && $this->BE_USER->user['uid']) {
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
