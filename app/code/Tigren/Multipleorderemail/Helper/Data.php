<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */
namespace Tigren\Multipleorderemail\Helper;

use Magento\Customer\Model\Session as CustomerSession;

/**
 * Catalog data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $_shippingModelConfig;

    /**
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentModelConfig;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */

    protected $_mbRuleFactory;

    public function __construct(
        \Magento\Payment\Model\Config $paymentModelConfig,
        \Magento\Shipping\Model\Config $shippingModelConfig,
        \Magento\Framework\App\Helper\Context $context,
        \Tigren\Multipleorderemail\Model\RuleFactory $mbRule
    )
    {
        $this->_mbRuleFactory = $mbRule;
        $this->_paymentModelConfig = $paymentModelConfig;
        $this->_shippingModelConfig = $shippingModelConfig;
        parent::__construct($context);
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getActiveShippingMethods()
    {
        $activeCarriers = $this->_shippingModelConfig->getActiveCarriers();
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            $options = array();
            if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                    $code = $carrierCode . '_' . $methodCode;
                    $options[] = array('value' => $code, 'label' => $method);
                }
                $carrierTitle = $this->scopeConfig->getValue('carriers/' . $carrierCode . '/title');
            }
            if ($options) {
                $methods[] = array('value' => $options, 'label' => $carrierTitle);
            }
        }
        return $methods;
    }

    public function getActivePaymentMethods()
    {
        $payments = $this->_paymentModelConfig->getActiveMethods();
        $methods = array();
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->scopeConfig->getValue('payment/' . $paymentCode . '/title');
            $methods[$paymentCode] = array(
                'label' => $paymentTitle,
                'value' => $paymentCode
            );
        }
        return $methods;
    }

    public function getAllPaymentOptions()
    {
        $payments = $this->_paymentModelConfig->getActiveMethods();
        $options = array();
        foreach ($payments as $paymentCode => $paymentModel) {
            $options[] = $paymentCode;
        }
        return $options;
    }

    public function isApplicable(\Magento\Sales\Model\Order $order, \Tigren\Multipleorderemail\Model\Customemail $rule)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $products = $order->getAllItems();
        foreach ($products as $product) {

            // individual products logic
            if ("" != $rule->getData('cond_serialize')) {
                /** @var \Tigren\Multipleorderemail\Model\Rule $ruleModel */
                $ruleModel = $objectManager->create('Tigren\Multipleorderemail\Model\Rule');
                $ruleModel->setConditions([]);
                /*$ruleModel->setStores($rule->getData('stores'));*/
                $ruleModel->setConditionsSerialized($rule->getData('cond_serialize'));
                $ruleModel->setProduct($product);

                $productIds = $ruleModel->getMatchingProductIds();
                $inArray = array_key_exists($product->getProductId(), $productIds) && in_array($product->getStore()->getId(), $productIds[$product->getProductId()]);
                
            }

            if(!$inArray) {
                return false;
            }
        }

        return $inArray;
    }
}
