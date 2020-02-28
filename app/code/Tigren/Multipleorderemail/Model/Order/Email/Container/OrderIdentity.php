<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Multipleorderemail\Model\Order\Email\Container;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class OrderIdentity extends \Magento\Sales\Model\Order\Email\Container\OrderIdentity
{

    /**
     * Admin store Id
     *
     */
    const ADMIN_STORE_ID = 0;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Tigren\Multipleorderemail\Helper\Data
     */
    protected $_multipleHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Tigren\Multipleorderemail\Model\ResourceModel\Customemail\CollectionFactory
     */
    protected $_customemailCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_saleCollection;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Tigren\Multipleorderemail\Helper\Data $multipleHelper,
        \Tigren\Multipleorderemail\Model\ResourceModel\Customemail\CollectionFactory $customemailCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $saleCollection,
        \Magento\Sales\Model\OrderFactory $orderFactory
    )
    {
        $this->_orderFactory = $orderFactory;
        $this->_saleCollection = $saleCollection;
        $this->_customemailCollectionFactory = $customemailCollectionFactory;
        $this->_resource = $resource;
        $this->_multipleHelper = $multipleHelper;
        $this->_checkoutSession = $checkoutSession;
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

        $rules = $this->_customemailCollectionFactory->create()
            ->addFieldToFilter('status', 1);
        $rules->getSelect()->joinLeft(
            array('multipleorder_store' => $this->_resource->getTableName('mb_multipleorderemail_customemail_store')),
            'main_table.id=multipleorder_store.id'
        )
            ->where('multipleorder_store.store_id IN (?)', $storeId)
            ->group('main_table.id')
            ->order('sort_order', 'ASC');
        
        $order = $this->_saleCollection->create()->getLastItem();

        foreach ($rules as $key => $rule) {
            $customerGroups = explode(',', $rule->getCustomerGroupIds());
            if (!empty($customerGroups) && !in_array($order->getCustomerGroupId(), $customerGroups)) {
                $rules->removeItemByKey($key);
                continue;
            }

            //validate payment method
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

            $isApplicable = $this->_multipleHelper->isApplicable($order, $rule);
            if (!$isApplicable) {
                $rules->removeItemByKey($key);
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
            return $this->getCustomEmailRule()->getTemplateId();
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

            return $this->getCustomEmailRule()->getTemplateId();
        } else {
            return parent::getTemplateId();
        }
    }
}