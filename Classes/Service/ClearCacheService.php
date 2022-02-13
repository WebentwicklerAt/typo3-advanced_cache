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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\Query\Restriction\DocumentTypeExclusionRestriction;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\CacheService;

class ClearCacheService
{
    /**
     * @var CacheService
     */
    protected $cacheService;

    public function __construct()
    {
        $this->cacheService = GeneralUtility::makeInstance(CacheService::class);
    }

    /**
     * @param int $pageUid
     * @return void
     */
    public function clearPageCache(int $pageUid): void
    {
        $permissionClause = $this->getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW);
        $pageRow = BackendUtility::readPageAccess($pageUid, $permissionClause);
        if ($this->getBackendUser()->doesUserHaveAccess($pageRow, Permission::PAGE_SHOW)) {
            $this->cacheService->clearPageCache($pageUid);
        }
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
        $this->cacheService->clearPageCache($pageIdsToClear);
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
