<?php
/*
 * Copyright © Ghost Unicorns snc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace GhostUnicorns\FdiCategory\Model\ResourceModel;

use Magento\Framework\App\ProductMetadata;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;

class CreateCategoryWithId
{
    /** @var string */
    const SEQUENCE_TABLE_NAME = 'sequence_catalog_category';

    /** @var string */
    const TABLE_NAME = 'catalog_category_entity';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductMetadataInterface $productMetadata
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param int $id
     */
    public function execute(int $id)
    {
        if ($this->productMetadata->getEdition() !== ProductMetadata::EDITION_NAME) {
            $this->insertCategorySequence($id);
        }
        $this->insertCategoryEntity($id);
    }

    /**
     * @param int $id
     */
    private function insertCategorySequence(int $id)
    {
        $tableName = $this->resourceConnection->getTablePrefix()
            . $this->resourceConnection->getTableName(self::SEQUENCE_TABLE_NAME);
        $connection = $this->resourceConnection->getConnection();
        $connection->insert($tableName, [
            'sequence_value' => $id
        ]);
    }

    /**
     * @param int $entityId
     */
    private function insertCategoryEntity(int $entityId)
    {
        $tableName = $this->resourceConnection->getTablePrefix()
            . $this->resourceConnection->getTableName(self::TABLE_NAME);
        $connection = $this->resourceConnection->getConnection();
        $connection->insert($tableName, [
            'entity_id' => $entityId,
            'row_id' => $entityId,
            'attribute_set_id' => 3,
            'parent_id' => 2,
            'path' => '1/2/' . $entityId,
            'position' => 1,
            'level' => 3,
            'children_count' => 0,
        ]);
    }

    /**
     * @param int $entityId
     */
    private function updateCategoryEntity(int $entityId)
    {
        $tableName = $this->resourceConnection->getTablePrefix() .
            $this->resourceConnection->getTableName(self::TABLE_NAME);
        $connection = $this->resourceConnection->getConnection();
        $connection->insert($tableName, [
            'entity_id' => $entityId,
            'attribute_set_id' => 3,
            'parent_id' => 1,
            'path' => 1,
            'position' => 1,
            'level' => 1,
            'children_count' => 0,
        ]);
    }
}
