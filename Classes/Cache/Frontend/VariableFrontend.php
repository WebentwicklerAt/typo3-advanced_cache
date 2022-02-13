<?php
declare(strict_types=1);

namespace WebentwicklerAt\AdvancedCache\Cache\Frontend;

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

use TYPO3\CMS\Core\Cache\Backend\TransientBackendInterface;

class VariableFrontend extends \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend
{
    /**
     * Finds and returns all cache entries which are tagged by the specified tag.
     *
     * @param string $tag The tag to search for
     *
     * @return array An array with the content of all matching entries. An empty array if no entries matched
     * @throws \InvalidArgumentException if the tag is not valid
     */
    public function getByTag($tag)
    {
        if (!$this->isValidTag($tag)) {
            throw new \InvalidArgumentException('"' . $tag . '" is not a valid tag for a cache entry.', 1233058312);
        }
        $entries = [];
        $identifiers = $this->backend->findIdentifiersByTag($tag);
        foreach ($identifiers as $identifier) {
            $rawResult = $this->backend->get($identifier);
            if ($rawResult !== false) {
                $entries[] = $this->backend instanceof TransientBackendInterface ? $rawResult : unserialize($rawResult);
            }
        }
        return $entries;
    }
}
