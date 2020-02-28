<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Multipleorderemail\Block\Adminhtml;

class Customemail extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_customemail';
        $this->_blockGroup = 'Tigren_Multipleorderemail';
        $this->_headerText = __('Order Email Template Manager');

        parent::_construct();

        if ($this->_isAllowedAction('Tigren_Multipleorderemail::save')) {
            $this->buttonList->update('add', 'label', __('Add New Order Template'));
        } else {
            $this->buttonList->remove('add');
        }
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
