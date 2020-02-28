<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */
namespace Tigren\Multipleorderemail\Block\Adminhtml\Customemail\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Template extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;

    protected $_helper;

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $_emailConfig;

    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    protected $_templatesFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        \Magento\Store\Model\System\Store $systemStore,
        \Tigren\Multipleorderemail\Helper\Data $helper,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        array $data = []
    )
    {
        $this->_systemStore = $systemStore;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_objectConverter = $objectConverter;
        $this->_helper = $helper;
        $this->_templatesFactory = $templatesFactory;
        $this->_emailConfig = $emailConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare content for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Add Order Email Template');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Add Order Email Template');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Form
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Tigren\Multipleorderemail\Model\Customemail $model */
        $model = $this->_coreRegistry->registry('multipleorderemail_customemail');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('template_');
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Email Template'), 'class' => 'fieldset-wide']
        );

        $templateInfo = array(
            '' => __('Select Order Email Template')
        );
        $templateInvoiceInfo = array(
            '' => __('Select Invoice Email Template')
        );
        $templateShipmentInfo = array(
            '' => __('Select Shipment Email Template')
        );

        if (!($collection = $this->_coreRegistry->registry('config_system_email_template'))) {
            $collection = $this->_templatesFactory->create();
            $collection->load();
            foreach ($collection as $template) {
                $templateInfo[$template->getId()] = $template->getTemplateCode();
                $templateInvoiceInfo[$template->getId()] = $template->getTemplateCode();
                $templateShipmentInfo[$template->getId()] = $template->getTemplateCode();
            }
        }

        $fieldset->addField('template_id', 'select', array(
            'label' => __('Order Email Template'),
            'class' => 'required-entry',
            'required' => TRUE,
            'name' => 'template_id',
            'options' => $templateInfo,
        ));
        $fieldset->addField('template_invoice_id', 'select', array(
            'label' => __('Invoice Email Template'),
            'required' => FALSE,
            'name' => 'template_invoice_id',
            'options' => $templateInvoiceInfo,
        ));
        $fieldset->addField('template_shipment_id', 'select', array(
            'label' => __('Shipment Email Template'),
            'required' => FALSE,
            'name' => 'template_shipment_id',
            'options' => $templateShipmentInfo,
        ));

        $fieldset->addField('notification_email', 'text', array(
            'label' => __('Admin email address received notification'),
            'name' => 'notification_email'
        ));


        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
