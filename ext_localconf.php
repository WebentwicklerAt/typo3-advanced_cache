<?php

declare(strict_types=1);

defined('TYPO3') or die();

(static function (): void {
    $_EXTKEY = 'advanced_cache';

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_advancedcache_queue'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_advancedcache_queue'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_advancedcache_queue']['frontend'] =
            \WebentwicklerAt\AdvancedCache\Cache\Frontend\VariableFrontend::class;
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\DataHandling\DataHandler::class] = [
        'className' => \WebentwicklerAt\AdvancedCache\Xclass\DataHandler::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][$_EXTKEY] =
        \WebentwicklerAt\AdvancedCache\Hooks\PageRenderer::class . '->addInlineJavaScript';
})();
