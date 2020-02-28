<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Multipleorderemail\Model\Order\Email\Container;

class ShipmentIdentity extends \Magento\Sales\Model\Order\Email\Container\ShipmentIdentity
{
    /**
     * Admin store Id
     *
     */
    const ADMIN_STORE_ID = 0;

    /**
     * @var Session
     */
    protected $_customerSession;

    protected $_request;

    protected $_orderFactory;

    protected $_shipmentFactory;

    protected $_multipleHelper;

    protected $_customemailCollectionFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backup\Model\ResourceModel\Db $resourceDb,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Tigren\Multipleorderemail\Model\ResourceModel\Customemail\CollectionFactory $customemailCollectionFactory,
        \Tigren\Multipleorderemail\Helper\Data $multipleHelper
    )
    {
        $this->_resourceDb = $resourceDb;
        $this->_resource = $resource;
        $this->_customerSession = $customerSession;
        $this->_request = $request;
        $this->_orderFactory = $orderFactory;
        $this->_shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->_customemailCollectionFactory = $customemailCollectionFactory;
        $this->_multipleHelper = $multipleHelper;
        parent::__construct($scopeConfig, $storeManager);
    }

    /**
     * Return email copy_to list
     *
     * @return array|bool
     */
    public function getEmailCopyTo()
    {
        if ($this->getCustomEmailRule()) {
            if ($this->getCustomEmailRule()->getNotificationEmail()) {
                $data = $this->getCustomEmailRule()->getNotificationEmail();
            } elseif (!$this->getCustomEmailRule()->getNotificationEmail() && $this->_multipleHelper->getConfig('multipleorderemail/mainpage/admin_default_sender')) {
                $data = $this->_multipleHelper->getConfig('multipleorderemail/mainpage/admin_default_sender');
            } else {
                return parent::getEmailCopyTo();
            }

            if (!empty($data)) {
                return explode(',', $data);
            }
            return false;

        } else {
            return parent::getEmailCopyTo();
        }
    }

    public function getCustomEmailRule()
    {
        $storeId = $this->getStore()->getStoreId();
        $storeIds = array(self::ADMIN_STORE_ID, (int)$storeId);

        $rules = $this->_customemailCollectionFactory->create()
            ->addFieldToFilter('status', 1);
        $rules->getSelect()->joinLeft(
            array('multipleorder_store' => $this->_resource->getTableName('mb_multipleorderemail_customemail_store')),
            'main_table.id=multipleorder_store.id'
        )
            ->where('multipleorder_store.store_id IN (?)', $storeIds)
            ->group('main_table.id')
            ->order('sort_order', 'ASC');


        foreach ($rules as $key => $rule) {
            //validate payment method
            $shipmentId = $this->_getRequest()->getParam('shipment_id');

            $shipment = $this->_shipmentCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('entity_id', $shipmentId)
                ->getFirstItem();

            if ($shipment->getId()) {
                $orderId = $shipment->getOrderId();
                if ($orderId) {
                    $order = $this->_orderFactory->create()->load($orderId);
                    if ($order->getId()) {
                        $payment = $order->getPayment();
                        $paymentMethodCode = $payment->getMethodInstance()->getCode();

                        $paymentMethods = explode(',', $rule->getPaymentMethodId());
                        if (!empty($paymentMethods) && !in_array($paymentMethodCode, $paymentMethods)) {
                            $rules->removeItemByKey($key);
                            continue;
                        }

                        // validate shipping method
                        $shippingMethodCode = $order->getData('shipping_method');
                        $shippingMethods = explode(',', $rule->getShippingMethodId());
                        if (!empty($shippingMethods) && !in_array($shippingMethodCode, $shippingMethods)) {
                            $rules->removeItemByKey($key);
                            continue;
                        }

                        // validate customer group
                        $customerGroups = explode(',', $rule->getCustomerGroupIds());
                        if (!empty($customerGroups) && !in_array($order->getCustomerGroupId(), $customerGroups)) {
                            $rules->removeItemByKey($key);
                            continue;
                        }

                        $isApplicable = $this->_multipleHelper->isApplicable($order, $rule);
                        if (!$isApplicable) {
                            $rules->removeItemByKey($key);
                        }
                    }
                }
            }
        }

        if (!count($rules)) {
            return false;
        }

        return $rules->getFirstItem();
    }

    /**
     * Return guest template id
     *
     * @return mixed
     */
    public function getGuestTemplateId()
    {
        if ($this->getCustomEmailRule()) {
            return $this->getCustomEmailRule()->getTemplateShipmentId();
        } else {
            return parent::getGuestTemplateId();
        }
    }

    /*
    * Return applied rule for current order
    */

    /**
     * Return template id
     *
     * @return mixed
     */
    public function getTemplateId()
    {
        if ($this->getCustomEmailRule()) {
            return $this->getCustomEmailRule()->getTemplateShipmentId();
        } else {
            return parent::getTemplateId();
        }
    }

    protected function _getRequest()
    {
        return $this->_request;
    }
}