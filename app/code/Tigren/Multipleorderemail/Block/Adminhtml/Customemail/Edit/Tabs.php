<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Multipleorderemail\Block\Adminhtml\Customemail\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('tigren_multipleorderemail_customemail_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Order Email Template Information'));
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        if ($this->_isAllowedAction('Tigren_Multipleorderemail::full_edit')) {
            $this->addTab(
                'tigren_multipleorderemail_customemail_edit_tab_main',
                [
                    'label' => __('General'),
                    'title' => __('General'),
                    'content' => $this->getLayout()->createBlock(
                        'Tigren\Multipleorderemail\Block\Adminhtml\Customemail\Edit\Tab\Main'
                    )->toHtml()
                ]
            );
        }


        $this->addTab(
            'tigren_multipleorderemail_customemail_edit_tab_conditions',
            [
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'content' => $this->getLayout()->createBlock(
                    'Tigren\Multipleorderemail\Block\Adminhtml\Customemail\Edit\Tab\Conditions'
                )->toHtml()
            ]
        );

        $this->addTab(
            'tigren_multipleorderemail_customemail_edit_tab_email_template',
            [
                'label' => __('Email Template'),
                'title' => __('Email Template'),
                'content' => $this->getLayout()->createBlock(
                    'Tigren\Multipleorderemail\Block\Adminhtml\Customemail\Edit\Tab\Template'
                )->toHtml()
            ]
        );

        return parent::_prepareLayout();
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
