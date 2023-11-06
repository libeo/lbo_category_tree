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
    // /**
    //  * categoryRepository
    //  *
    //  * @var CategoryRepository
    //  */
    // protected $categoryRepository = null;

    // /**
    //  * @param \TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository $categoryRepository
    //  */
    // public function injectCategoryRepository(\TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository $categoryRepository)
    // {
    //     $this->categoryRepository = $categoryRepository;
    // }

    // public function __construct()
    // {
    //     $this->initializeObject();
    // }

    // public function initializeObject()
    // {
    //     // $this->categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
    // }

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

    /**
     * action detail
     *
     * @param Category $category
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function detailAction(Category $category): \Psr\Http\Message\ResponseInterface
    {
        $categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
        $categories = [0 => 'Aucune'];
        foreach ($categoryRepository->findAll() as $cat) {
            if ($cat->getUid() != $category->getUid() && (!$cat->getParent() || $cat->getParent()->getUid() != $category->getUid())) {
                $categories[$cat->getUid()] = $cat->getTitle();
            }
        }

        $this->view->assignMultiple([
            'category' => $category,
            'categories' => $categories
        ]);
        return $this->htmlResponse();
    }

    /**
     * action edit
     *
     * @param Category $category
     * @param string $title
     * @param string $description
     * @param Category $parent
     * @param int $pid
     * @return null
     */
    public function editAction(Category $category, string $title, string $description, ?Category $parent, int $pid)
    {
        $categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
        $category->setTitle($title);
        $category->setDescription($description);
        $category->setParent($parent);
        $category->setPid($pid);
        $categoryRepository->update($category);
        $this->redirect('treeList', 'Category', NULL, []);
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
            return $this->createTreeNode($new, $new[0]);
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
