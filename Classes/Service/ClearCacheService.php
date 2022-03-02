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

use TYPO3\CMS\Backend\Tree\Repository\PageTreeRepository;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\Query\Restriction\DocumentTypeExclusionRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ClearCacheService
{
    /**
     * @var DataHandler
     */
    protected $dataHandler;

    public function __construct()
    {
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $this->dataHandler->start([], []);
    }

    /**
     * @param int $pageUid
     * @return void
     */
    public function clearPageCache(int $pageUid): void
    {
        $this->dataHandler->clear_cacheCmd($pageUid);
    }

    /**
     * @param int $pageUid
     * @return void
     */
    public function clearBranchCache(int $pageUid): void
    {
        $page = $this->getPageTreeRepository()->getTree(
            $pageUid,
            null,
            [],
            true
        );
        $pageIdsToClear = $this->pagesToFlatArray($page);
        foreach ($pageIdsToClear as $pageUid) {
            $this->dataHandler->clear_cacheCmd($pageUid);
        }
    }

    /**
     * @param array $page
     * @return array
     */
    protected function pagesToFlatArray(array $page): array
    {
        $flatArray = [
            $page['uid'],
        ];
        if (
            array_key_exists('_children', $page)
            && is_array($page['_children'])
        ) {
            foreach ($page['_children'] as $childPage) {
                $flatArray = array_merge(
                    $flatArray,
                    $this->pagesToFlatArray($childPage)
                );
            }
        }
        return $flatArray;
    }

    /**
     * @return PageTreeRepository
     */
    protected function getPageTreeRepository(): PageTreeRepository
    {
        $backendUser = $this->getBackendUser();
        $userTsConfig = $backendUser->getTSConfig();
        $excludedDocumentTypes = GeneralUtility::intExplode(
            ',',
            $userTsConfig['options.']['pageTree.']['excludeDoktypes'] ?? '',
            true
        );

        $additionalQueryRestrictions = [];
        if (!empty($excludedDocumentTypes)) {
            $additionalQueryRestrictions[] = GeneralUtility::makeInstance(
                DocumentTypeExclusionRestriction::class,
                $excludedDocumentTypes
            );
        }

        return GeneralUtility::makeInstance(
            PageTreeRepository::class,
            (int)$backendUser->workspace,
            [],
            $additionalQueryRestrictions
        );
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
