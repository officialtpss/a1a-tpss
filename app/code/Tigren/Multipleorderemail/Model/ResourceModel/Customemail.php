<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Multipleorderemail\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * Multipleorderemail mysql resource
 */
class Customemail extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Email template store table
     *
     * @var string
     */
    protected $_templateStoreTable;

    /**
     * Core model store manager interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $resourcePrefix = null
    )
    {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mb_multipleorderemail', 'id');
        $this->_templateStoreTable = $this->getTable('mb_multipleorderemail_customemail_store');
    }

    /**
     * Actions after load
     *
     * @param \Magento\Framework\Model\AbstractModel|\Tigren\Productattachment\Model\Attachment $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);
        if (!$object->getId()) {
            return $this;
        }
        // load available in store, cusomer group, shipping method, payment method
        $object->setStores($this->getStores((int)$object->getId()));
        $object->setCustomerGroupIds(explode(',', $object->getCustomerGroupIds()));
        $object->setShippingMethodId(explode(',', $object->getShippingMethodId()));
        $object->setPaymentMethodId(explode(',', $object->getPaymentMethodId()));

        return $this;
    }

    /**
     * Retrieve store IDs related to given rating
     *
     * @param  int $id
     * @return array
     */
    public function getStores($id)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable($this->_templateStoreTable),
            'store_id'
        )->where(
            'id = ?',
            $id
        );
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Perform actions before object save
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->hasData('stores') && is_array($object->getStores())) {
            $stores = $object->getStores();
            $stores[] = 0;
            $object->setStores($stores);
        } elseif ($object->hasData('stores')) {
            $object->setStores([$object->getStores(), 0]);
        }

        if ($object->hasData('customer_group_ids') && is_array($object->getCustomerGroupIds())) {
            $object->setCustomerGroupIds(implode(',', $object->getCustomerGroupIds()));
        } elseif ($object->hasData('customer_group_ids')) {
            $object->setCustomerGroupIds($object->getCustomerGroupIds());
        }

        if ($object->hasData('shipping_method_id') && is_array($object->getShippingMethodId())) {
            $object->setShippingMethodId(implode(',', $object->getShippingMethodId()));
        } elseif ($object->hasData('shipping_method_id')) {
            $object->setShippingMethodId($object->getShippingMethodId());
        } else {
            $object->setShippingMethodId(NULL);
        }

        if ($object->hasData('payment_method_id') && is_array($object->getPaymentMethodId())) {
            $object->setPaymentMethodId(implode(',', $object->getPaymentMethodId()));
        } elseif ($object->hasData('payment_method_id')) {
            $object->setPaymentMethodId($object->getPaymentMethodId());
        }

        return $this;
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $connection = $this->getConnection();
        /**
         * save stores
         */
        $stores = $object->getStores();
        if (!empty($stores)) {
            $condition = ['id = ?' => $object->getId()];
            $connection->delete($this->_templateStoreTable, $condition);

            $insertedStoreIds = [];
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedStoreIds)) {
                    continue;
                }

                $insertedStoreIds[] = $storeId;
                $storeInsert = ['store_id' => $storeId, 'id' => $object->getId()];
                $connection->insert($this->_templateStoreTable, $storeInsert);
            }
        }

        return $this;
    }
}
