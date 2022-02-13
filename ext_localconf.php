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

call_user_func(function ($_EXTKEY) {
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_advancedcache_queue'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_advancedcache_queue'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_advancedcache_queue']['frontend'] =
            \WebentwicklerAt\AdvancedCache\Cache\Frontend\VariableFrontend::class;
    }

    if (TYPO3_MODE === 'BE') {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\DataHandling\DataHandler::class] = [
            'className' => \WebentwicklerAt\AdvancedCache\Xclass\DataHandler::class,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][$_EXTKEY] =
            \WebentwicklerAt\AdvancedCache\Hooks\PageRenderer::class . '->addInlineJavaScript';
    }
}, 'advanced_cache');
