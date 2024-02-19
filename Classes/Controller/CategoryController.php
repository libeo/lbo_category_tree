<?php

declare(strict_types=1);

namespace Libeo\CategoryTree\Controller;

use Libeo\CategoryTree\Manager\QueryManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository;

/**
 * TreeController
 */
class CategoryController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * action treeList
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function treeListAction(): \Psr\Http\Message\ResponseInterface
    {
        $this->view->assign('tree', $this->buildTree());
        return $this->htmlResponse();
    }

    private function buildTree(): bool|array
    {
        $queryManager = GeneralUtility::makeInstance(QueryManager::class);

        $dbCategories[0] = ['uid' => 0];
        foreach ($queryManager->getCategories() as $cat) {
            $dbCategories[$cat['uid']] = $cat;
        }

        if (count($dbCategories)) {
            $new = [];
            foreach ($dbCategories as $category){
                if ($category['uid'] == 0) {
                    continue;
                }
                $category['depth'] = $this->calculateDepth($category, $dbCategories);
                $new[$category['parent']][] = $category;
            }
            return count($new) > 0 ? $this->createTreeNode($new, $new[0]) : [];
        } else {
            return [];
        }
    }

    private function createTreeNode(&$list, $parents): array
    {
        $tree = [];
        foreach ($parents as $key => $category){
            if(isset($list[$category['uid']])){
                $category['children'] = $this->createTreeNode($list, $list[$category['uid']]);
            }
            $tree[] = $category;
        }
        return $tree;
    }

    private function calculateDepth($category, $array): int
    {
        $depth = 0;
        while (!empty($category['parent'])) {
            $depth++;
            $category = $array[$category['parent']];
        }
        return $depth;
    }
}
