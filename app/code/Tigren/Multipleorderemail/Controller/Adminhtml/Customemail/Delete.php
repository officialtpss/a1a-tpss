<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */
namespace Tigren\Multipleorderemail\Controller\Adminhtml\Customemail;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var \Tigren\Multipleorderemail\Model\Customemail $model */
                $model = $this->_objectManager->create('Tigren\Multipleorderemail\Model\Customemail');
                $model->load($id);
                $model->delete();
                $this->_redirect('multipleorderemail/*/');
                $this->messageManager->addSuccess(__('Delete successfull.'));
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete this item right now. Please review the log and try again.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('multipleorderemail/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a rule to delete.'));
        $this->_redirect('multipleorderemail/*/');
    }
}
