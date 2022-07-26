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

class CreateCategoryWithoutId
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
     * @return int
     */
    public function execute(): int
    {
        if ($this->productMetadata->getEdition() !== ProductMetadata::EDITION_NAME) {
            $id = $this->insertCategorySequence();
            $this->insertCategoryEntity($id);
        } else {
            $id = $this->insertCategoryEntity();
        }

        return (int)$id;
    }

    /**
     * @return int
     */
    private function insertCategorySequence(): int
    {
        $tableName = $this->resourceConnection->getTablePrefix()
            . $this->resourceConnection->getTableName(self::SEQUENCE_TABLE_NAME);
        $connection = $this->resourceConnection->getConnection();
        $connection->insert($tableName, []);
        return (int)$connection->lastInsertId();
    }

    /**
     * @param int|null $entityId
     * @return void
     */
    private function insertCategoryEntity(int $entityId = null): int
    {
        $tableName = $this->resourceConnection->getTablePrefix()
            . $this->resourceConnection->getTableName(self::TABLE_NAME);

        $connection = $this->resourceConnection->getConnection();
        $data = [
            'attribute_set_id' => 3,
            'parent_id' => 0,
            'path' => '0/99999',
            'position' => 0,
            'level' => 0,
            'children_count' => 0,
        ];

        if ($entityId) {
            $data['path'] = '0/' . $entityId;
            $data['entity_id'] = $entityId;
            if ($this->productMetadata->getEdition() !== ProductMetadata::EDITION_NAME) {
                $data['row_id'] = $entityId;
            }
        }
        $connection->insert($tableName, $data);

        return $entityId ?: (int)$connection->lastInsertId();
    }
}
