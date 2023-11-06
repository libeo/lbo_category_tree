<?php
defined('TYPO3') || die();

(static function() {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'CategoryTree',
        'web',
        'categories',
        '',
        [
            \Libeo\CategoryTree\Controller\CategoryController::class => 'treeList,detail,edit',
        ],
        [
            'access' => 'user,group',
            'icon'   => 'EXT:category_tree/Resources/Public/Icons/user_mod_categories.svg',
            'labels' => 'LLL:EXT:category_tree/Resources/Private/Language/locallang_categories.xlf',
        ]
    );

})();
