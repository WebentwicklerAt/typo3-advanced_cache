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

/**
 * Module: TYPO3/CMS/AdvancedCache/ContextMenuActions
 *
 * JavaScript to handle advanced_cache related actions for ContextMenu
 * @exports TYPO3/CMS/AdvancedCache/ContextMenuActions
 */
define(
    [
        'jquery',
        'TYPO3/CMS/Core/Ajax/AjaxRequest',
        'TYPO3/CMS/Backend/Notification'
    ],
    function (
        $,
        AjaxRequest,
        Notification
    ) {
        'use strict';

        /**
         * @exports TYPO3/CMS/AdvancedCache/ContextMenuActions
         */
        var ContextMenuActions = {};

        /**
         * @param {string} table
         * @param {int} uid of the page
         */
        ContextMenuActions.clearBranchCache = function (table, uid) {
            (new AjaxRequest(TYPO3.settings.ajaxUrls['tx_advancedcache_clearbranchcache'])).withQueryArguments({id: uid}).get({cache: 'no-cache'}).then(
                async (response) => {
                    const data = await response.resolve();
                    if (data.success === true) {
                        Notification.success(data.title, data.message, 1);
                    } else {
                        Notification.error(data.title, data.message, 1);
                    }
                },
                () => {
                    Notification.error(
                        'Clearing page caches went wrong on the server side.',
                    );
                }
            );
        };

        return ContextMenuActions;
    }
);
