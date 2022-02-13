<?php
declare(strict_types=1);

namespace WebentwicklerAt\AdvancedCache\ContextMenu;

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

use TYPO3\CMS\Backend\ContextMenu\ItemProviders\PageProvider;

class ItemProvider extends PageProvider
{
    /**
     * @var array
     */
    protected $itemsConfiguration = [
        'clearBranchCache' => [
            'label' => 'LLL:EXT:advanced_cache/Resources/Private/Language/locallang.xlf:clearcache.branch',
            'iconIdentifier' => 'actions-system-cache-clear',
            'callbackAction' => 'clearBranchCache',
        ],
    ];

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 45;
    }

    /**
     * @param string $itemName
     * @param string $type
     * @return bool
     */
    public function canRender(string $itemName, string $type): bool
    {
        if (in_array($itemName, $this->disabledItems, true)) {
            return false;
        }
        $canRender = $this->canClearBranchCache();
        return $canRender;
    }

    /**
     * @return bool
     */
    protected function canClearBranchCache(): bool
    {
        return !$this->isRoot()
            && ($this->backendUser->isAdmin() || $this->backendUser->getTSConfig()['options.']['clearCache.']['branch'] ?? false);
    }

    /**
     * @param array $items
     * @return array
     */
    public function addItems(array $items): array
    {
        $this->initialize();

        $localItems = $this->prepareItems($this->itemsConfiguration);
        if (isset($items['clearCache'])) {
            $position = array_search('clearCache', array_keys($items), true);

            $beginning = array_slice($items, 0, $position + 1, true);
            $end = array_slice($items, $position + 1, null, true);

            $items = $beginning + $localItems + $end;
        } else {
            $items += $localItems;
        }
        return $items;
    }

    /**
     * @param string $itemName
     * @return array
     */
    public function getAdditionalAttributes(string $itemName): array
    {
        $additionalAttributes = [];
        if ($itemName === 'clearBranchCache') {
            $additionalAttributes['data-callback-module'] = 'TYPO3/CMS/AdvancedCache/ContextMenuActions';
        }
        return $additionalAttributes;
    }
}
