<?php
/*
 * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\FdiCategory\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\Exception\NoSuchEntityException;

class UpdateCategory
{
    const SEQUENCE_TABLE_NAME = 'sequence_catalog_category';
    const TABLE_NAME = 'catalog_category_entity';

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var CategoryResource
     */
    private $categoryResource;

    /**
     * @param CategoryRepository $categoryRepository
     * @param CategoryResource $categoryResource
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        CategoryResource $categoryResource
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryResource = $categoryResource;
    }

    /**
     * @param Category $category
     * @param array $data
     * @param array $attributesToIgnore
     * @throws NoSuchEntityException
     */
    public function execute(Category $category, array $data, array $attributesToIgnore = [])
    {
        $storeId = $data['store_id'];
        $parentCategoryId = $data['parent_id'];
        $categoryId = $data['id'];

        $category->setStoreId($storeId);

        foreach ($data as $attributeKey => $value) {
            if (in_array($attributeKey, $attributesToIgnore)) {
                continue;
            }
            if ($value === '') {
                continue;
            }
            if ($value === '___EMPTY___') {
                $value = null;
            }
            $category->setData($attributeKey, $value);
        }

        $parentCategory = $this->categoryRepository->get($parentCategoryId);
        $category->setPath($parentCategory->getPath() . '/' . $categoryId);
        $category->setParentId($parentCategoryId);
        $category->setLevel($parentCategory->getLevel() + 1);

        $this->categoryResource->save($category);
    }
}
