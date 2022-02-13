<?php
declare(strict_types=1);

namespace WebentwicklerAt\AdvancedCache\Backend;

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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use WebentwicklerAt\AdvancedCache\Service\AsyncCacheService;
use WebentwicklerAt\AdvancedCache\Service\ClearCacheService;

class AjaxRequest
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function clearBranchCacheAction(ServerRequestInterface $request): ResponseInterface
    {
        $pageUid = (int)($request->getQueryParams()['id'] ?? 0);
        $success = false;
        $message = LocalizationUtility::translate(
            'clearcache.branch.message.error',
            'advanced_cache'
        );

        if ($pageUid) {
            /** @var ClearCacheService $clearCacheService */
            $clearCacheService = GeneralUtility::makeInstance(ClearCacheService::class);
            $clearCacheService->clearBranchCache($pageUid);
            $success = true;
            $message = LocalizationUtility::translate(
                'clearcache.branch.message.success',
                'advanced_cache'
            );
        }

        return new JsonResponse([
            'success' => $success,
            'title' => LocalizationUtility::translate(
                'clearcache.branch.title',
                'advanced_cache'
            ),
            'message' => $message,
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function executeAction(ServerRequestInterface $request): ResponseInterface
    {
        /** @var AsyncCacheService $asyncCacheService */
        $asyncCacheService = GeneralUtility::makeInstance(AsyncCacheService::class);
        $asyncCacheService->flush();

        $response = new Response();
        $title = LocalizationUtility::translate(
            'clearcache.async.title',
            'advanced_cache'
        );
        $message = LocalizationUtility::translate(
            'clearcache.async.message.success',
            'advanced_cache'
        );
        $response->getBody()->write('top.TYPO3.Notification.success(\'' . $title . '\',\'' . $message . '\',4);');

        return $response;
    }
}
