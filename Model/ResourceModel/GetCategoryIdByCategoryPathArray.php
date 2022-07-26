<?php
/*
 * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\FdiCategory\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

class GetCategoryIdByCategoryPathArray
{
    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @param CollectionFactory $categoryCollectionFactory
     */
    public function __construct(CollectionFactory $categoryCollectionFactory)
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @param array $categoriesPathArray
     * @param int $rootCategory
     * @return int
     * @throws LocalizedException
     */
    public function execute(array $categoriesPathArray, int $rootCategory): int
    {
        $categoryId = $rootCategory;
        $level = 1;

        foreach ($categoriesPathArray as $categoryName) {
            $level++;
            $collection = $this->categoryCollectionFactory->create()
                ->addAttributeToFilter('parent_id', $categoryId)
                ->addAttributeToFilter('name', $categoryName)
                ->addAttributeToFilter('level', $level)
                ->setPageSize(1);

            if ($collection->getSize()) {
                $categoryId = (int)$collection->getFirstItem()->getId();
                continue;
            }

            throw new LocalizedException(__('Category not found with name: %1', $categoryName));
        }

        return $categoryId;
    }
}
