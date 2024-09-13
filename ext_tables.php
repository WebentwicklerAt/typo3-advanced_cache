<?php

declare(strict_types=1);

defined('TYPO3') or die();

(static function (): void {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['ContextMenu']['ItemProviders']['advanced_cache'] =
        \WebentwicklerAt\AdvancedCache\ContextMenu\ItemProvider::class;
})();
