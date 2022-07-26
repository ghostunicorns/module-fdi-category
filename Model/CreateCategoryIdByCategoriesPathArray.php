<?php
/*
 * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\FdiCategory\Model;

use GhostUnicorns\FdiCategory\Model\ResourceModel\CreateCategoryWithoutId;
use GhostUnicorns\FdiCategory\Model\ResourceModel\GetCategoryParentIdByCategoryPathArray;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;

class CreateCategoryIdByCategoriesPathArray
{
    /**
     * @var CreateCategoryWithoutId
     */
    private $createCategoryWithoutId;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var GetCategoryParentIdByCategoryPathArray
     */
    private $getCategoryParentIdByCategoryPathArray;

    /**
     * @var UpdateCategory
     */
    private $updateCategory;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @param CreateCategoryWithoutId $createCategoryWithoutId
     * @param CategoryRepositoryInterface $categoryRepository
     * @param GetCategoryParentIdByCategoryPathArray $getCategoryParentIdByCategoryPathArray
     * @param UpdateCategory $updateCategory
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        CreateCategoryWithoutId $createCategoryWithoutId,
        CategoryRepositoryInterface $categoryRepository,
        GetCategoryParentIdByCategoryPathArray $getCategoryParentIdByCategoryPathArray,
        UpdateCategory $updateCategory,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->createCategoryWithoutId = $createCategoryWithoutId;
        $this->categoryRepository = $categoryRepository;
        $this->getCategoryParentIdByCategoryPathArray = $getCategoryParentIdByCategoryPathArray;
        $this->updateCategory = $updateCategory;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @param array $categoriesPathArray
     * @param int $rootCategory
     * @param array $categoryData
     * @param array $attributesToIgnore
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(
        array $categoriesPathArray,
        int $rootCategory,
        array $categoryData = [],
        array $attributesToIgnore = []
    ): int {
        $parentId = $this->getCategoryParentIdByCategoryPathArray->execute($categoriesPathArray, $rootCategory);

        $categoryId = $this->createCategoryWithoutId->execute();

        $category = $this->categoryRepository->get($categoryId);

        foreach ($categoryData as $storeCode => $data) {
            $storeId = $this->storeRepository->getActiveStoreByCode($storeCode)->getId();
            $data['parent_id'] = $parentId;
            $data['store_id'] = $storeId;
            $this->updateCategory->execute($category, $data, $attributesToIgnore);
        }

        return $parentId;
    }
}
