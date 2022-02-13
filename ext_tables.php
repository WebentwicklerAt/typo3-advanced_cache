<?php
defined('TYPO3_MODE') or die();

(function () {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['ContextMenu']['ItemProviders']['advanced_cache'] =
        \WebentwicklerAt\AdvancedCache\ContextMenu\ItemProvider::class;
})();
