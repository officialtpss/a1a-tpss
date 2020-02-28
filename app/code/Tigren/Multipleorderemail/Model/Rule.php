<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Multipleorderemail\Model;

use Magento\Catalog\Model\Product;

class Rule extends \Magento\CatalogRule\Model\Rule
{
    protected $_product;

    public function getMatchingProductIds() //skip afterGetMatchingProductIds plugin
    {
        if ($this->_productIds === null) {

            $this->_productIds = [];
            $this->setCollectedAttributes([]);
            
            /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
            $productCollection = $this->_productCollectionFactory->create();
            if ($this->_productsFilter) {
                $productCollection->addIdFilter($this->_productsFilter);
            }

            $this->getConditions()->collectValidatedAttributes($productCollection);

            $this->_resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => $this->_productFactory->create()
                ]
            );

        }

        return $this->_productIds;
    }

    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        $stores = $this->_storeManager->getStores();
        /*$stores = explode(',', $stores);*/

        $results = [];

        foreach ($stores as $store) {
            $storeId = $store->getId();
            $product->setStoreId($storeId);
            $validate = $this->getConditions()->validate($product);
            if ($validate) {
                $results[$storeId] = $validate;
                $this->_productIds[$product->getId()] = $results;
            }
        }
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Tigren\Multipleorderemail\Model\ResourceModel\Customemail');
        $this->setIdFieldName('entity_id');
    }
}