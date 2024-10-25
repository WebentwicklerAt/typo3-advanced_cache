import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import Notification from '@typo3/backend/notification.js';

/**
 * @see https://github.com/TYPO3/typo3/blob/main/Build/Sources/TypeScript/backend/context-menu-actions.ts
 */
class ContextMenuActions {
    clearBranchCache(table, uid) {
        (new AjaxRequest(TYPO3.settings.ajaxUrls.tx_advancedcache_clearbranchcache)).withQueryArguments({id: uid}).get({cache: 'no-cache'}).then(
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
                    'Clearing page branch caches went wrong on the server side.',
                );
            }
        );
    }
}

export default new ContextMenuActions();
