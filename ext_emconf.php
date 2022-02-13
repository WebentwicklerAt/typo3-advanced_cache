<?php

/*
 * This file is part of the mitteilungsblatt extension for TYPO3 CMS.
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

$EM_CONF[$_EXTKEY] = [
    'title' => 'Advanced cache',
    'description' => 'Adds advanced clear cache functionality.',
    'version' => '0.0.0',
    'category' => 'misc',
    'constraints' => [
        'depends' => [],
        'conflicts' => [],
        'suggests' => [],
    ],
    'state' => 'stable',
    'uploadfolder' => false,
    'clearCacheOnLoad' => true,
    'author' => 'Gernot Leitgab',
    'author_company' => 'Webentwickler.at',
];
