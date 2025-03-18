import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import Notification from '@typo3/backend/notification.js';

/**
 * @see https://github.com/TYPO3/typo3/blob/main/Build/Sources/TypeScript/backend/context-menu.ts
 */
class ClearCacheAsync {
    constructor() {
        (new AjaxRequest(TYPO3.settings.ajaxUrls.tx_advancedcache_execute)).get({cache: 'no-cache'}).then(
            async (response) => {
                const data = await response.resolve();
                if (data.success === true) {
                    Notification.success(data.title, data.message);
                } else {
                    Notification.error(data.title, data.message);
                }
            },
            () => {
                Notification.error(
                    'Clearing caches went wrong on the server side.',
                );
            }
        );
    }
}

export default new ClearCacheAsync();
