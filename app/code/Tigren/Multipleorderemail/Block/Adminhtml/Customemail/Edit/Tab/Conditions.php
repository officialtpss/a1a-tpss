<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Multipleorderemail\Block\Adminhtml\Customemail\Edit\Tab;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Model\System\Store;

/**
 * Cart Price Rule General Information Tab
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Conditions extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $_salesRule;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    protected $_rendererFieldset;

    protected $_objectManager;

    protected $_conditions;

    protected $_groupRepository;

    protected $_helper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param RuleFactory $salesRule
     * @param ObjectConverter $objectConverter
     * @param Store $systemStore
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        RuleFactory $salesRule,
        ObjectConverter $objectConverter,
        Store $systemStore,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Tigren\Multipleorderemail\Helper\Data $helper,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_objectConverter = $objectConverter;
        $this->_salesRule = $salesRule;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_conditions = $conditions;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Tigren\Multipleorderemail\Model\Customemail $model */
        $model = $this->_coreRegistry->registry('multipleorderemail_customemail');

        $ruleModel  = $this->_objectManager->create('Tigren\Multipleorderemail\Model\Rule');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        /* start apply rule block*/

        if($model->getData('cond_serialize') != "") {
            $modelData = $model->getData();
            if (isset($modelData['cond_serialize'])) {
                $ruleModel->setConditions([]);
                $ruleModel->setConditionsSerialized($modelData['cond_serialize']);

                $ruleModel->getConditions()->setJsFormObject('rule_applyrule_fieldset');
            }
        }
        
        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('multipleorderemail/customemail/newConditionHtml/form/rule_applyrule_fieldset')
        );

        $fieldset = $form->addFieldset(
            'applyrule_fieldset',
            ['legend' => __('Apply the rule only to cart items matching the following conditions')]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name' => 'conditions',
                'label' => __('Product Conditions'),
                'title' => __('Product Conditions'),
                'required' => true
            ]
        )->setRule(
            $ruleModel
        )->setRenderer(
            $this->_conditions
        );
        /* end apply rule block*/
        /* start conditions block*/
        $conditionField = $form->addFieldset('conditions_fieldset', array('legend'=> __('Conditions')));


        $customerGroups = $this->_groupRepository->getList($this->_searchCriteriaBuilder->create())->getItems();
        $conditionField->addField('customer_group_ids', 'multiselect', array(
            'label'  => __('Customer Groups'),
            'title'  => __('Customer Groups'),
            'name'   => 'customer_group_ids[]',
            'required' => true,
            'values' => $this->_objectConverter->toOptionArray($customerGroups, 'id', 'code'),
        ));

        $conditionField->addField('shipping_method_id', 'multiselect', array(
            'label'  => __('Shipping Methods'),
            'title'  => __('Shipping Methods'),
            'name'   => 'shipping_method_id',
            'values' => $this->_helper->getActiveShippingMethods(),
            'after_element_html' => "<p class='note'>Not choosing any one means working for no shipping method order</p>",
        ));

        $conditionField->addField('payment_method_id', 'multiselect', array(
            'label'  => __('Payment Methods'),
            'title'  => __('Payment Methods'),
            'name'   => 'payment_method_id',
            'values' => $this->_helper->getActivePaymentMethods(),
            'after_element_html' => "<p class='note'>Not choosing any one means working for all</p>",
        ));
        /* end conditions block*/


        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
