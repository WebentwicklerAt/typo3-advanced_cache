<?php

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

return [
    'tx_advancedcache_clearbranchcache' => [
        'path' => '/advanced_cache/clearbranchcache',
        'target' => \WebentwicklerAt\AdvancedCache\Backend\AjaxRequest::class . '::clearBranchCacheAction',
    ],
    'tx_advancedcache_execute' => [
        'path' => '/advanced_cache/execute',
        'target' => \WebentwicklerAt\AdvancedCache\Backend\AjaxRequest::class . '::executeAction',
    ],
];
