<?php
declare(strict_types=1);

namespace GhostUnicorns\FdiCategory\Model\ResourceModel;

use GhostUnicorns\FdiProduct\Model\ResourceModel\GetProductIdBySku;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;

class SetProductCategoriesByProductSkuAndCategoryIds
{
    /**
     * @var string
     */
    const CATALOG_CATEGORY_PRODUCT = 'catalog_category_product';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetProductIdBySku
     */
    private $getProductIdBySku;

    /**
     * @param ResourceConnection $resourceConnection
     * @param GetProductIdBySku $getProductIdBySku
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetProductIdBySku $getProductIdBySku
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getProductIdBySku = $getProductIdBySku;
    }

    /**
     * @param string $productSku
     * @param array $categoryIds
     * @return void
     * @throws LocalizedException
     */
    public function execute(string $productSku, array $categoryIds): void
    {
        $productId = (int)$this->getProductIdBySku->execute($productSku);

        $tableName = $this->resourceConnection->getTableName(self::CATALOG_CATEGORY_PRODUCT);
        $connection = $this->resourceConnection->getConnection();

        $qry = $connection
            ->select()
            ->from($tableName, 'category_id')
            ->where('product_id = ?', $productId);

        $currentCategoriesIds = $connection->fetchCol($qry);

        $toDelete = array_diff($currentCategoriesIds, $categoryIds);
        $toAdd = array_diff($categoryIds, $currentCategoriesIds);

        if (!empty($toDelete)) {
            $connection->delete($tableName, [
                'category_id IN (?)' => $toDelete,
                'product_id = ?' => $productId,
            ]);
        }

        $toAddData = [];
        foreach ($toAdd as $categoryId) {
            $maxPosition = $this->getCategoryProductPosition((int)$categoryId);
            if ($maxPosition !== null) {
                $maxPosition++;
            }
            $position = (int)$maxPosition;

            $toAddData[] = [
                'category_id' => $categoryId,
                'product_id' => $productId,
                'position' => $position
            ];
        }
        if (!empty($toAddData)) {
            $connection->insertMultiple($tableName, $toAddData);
        }
    }

    /**
     * @param int $categoryId
     * @return string
     */
    private function getCategoryProductPosition(int $categoryId): string
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from($connection->getTableName(self::CATALOG_CATEGORY_PRODUCT), 'MAX(position)')
            ->where('category_id = ?', $categoryId);

        return $connection->fetchOne($select);
    }
}
