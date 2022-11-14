<?php
declare(strict_types=1);

namespace WebentwicklerAt\AdvancedCache\Hooks;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebentwicklerAt\AdvancedCache\Service\AsyncCacheService;

class PageRenderer
{
    /**
     * @param array $params
     * @return void
     */
    public function addInlineJavaScript(array &$params): void
    {
        /** @var AsyncCacheService $asyncCacheService */
        $asyncCacheService = GeneralUtility::makeInstance(AsyncCacheService::class);
        if (!$asyncCacheService->isFlushed()) {
            $params['jsInline']['advanced_cache'] = [
                'code' => 'top.jQuery.getScript(TYPO3.settings.ajaxUrls[\'tx_advancedcache_execute\']);',
                'section' => 1,
                'compress' => 'true',
                'forceOnTop' => 'false',
            ];
        }
    }
}
