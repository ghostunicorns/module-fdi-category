<?php
/*
 * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\FdiCategory\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

class GetCategoryParentIdByCategoryPathArray
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
        $parentId = $rootCategory;

        foreach ($categoriesPathArray as $index => $categoryName) {
            $level = $index + 2;

            if (count($categoriesPathArray) === ($index + 1)) {
                return $parentId;
            }

            $collection = $this->categoryCollectionFactory
                ->create()
                ->addAttributeToFilter('parent_id', $parentId)
                ->addAttributeToFilter('level', $level)
                ->addAttributeToFilter('name', $categoryName)
                ->setPageSize(1);

            if (!$collection->getSize()) {
                throw new LocalizedException(__(
                    'Category not found with parent_id: %1 - level: %2 - name: %3',
                    $parentId,
                    $level,
                    $categoryName
                ));
            }

            $parentId = (int)$collection->getFirstItem()->getId();
        }

        return $parentId;
    }
}
