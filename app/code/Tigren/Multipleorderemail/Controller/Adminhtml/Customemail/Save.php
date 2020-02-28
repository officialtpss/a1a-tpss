<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Multipleorderemail\Controller\Adminhtml\Customemail;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     * @param Action\Context $context
     * @param \Magento\Backend\Helper\Js $jsHelper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Backend\Helper\Js $jsHelper
    )
    {
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context);
        $this->jsHelper = $jsHelper;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Tigren\Multipleorderemail\Model\Customemail $model */
            $model = $this->_objectManager->create('Tigren\Multipleorderemail\Model\Customemail');
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }
            if (isset($data['rule']) && isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];

                unset($data['rule']);

                $rule = $this->_objectManager->create('Tigren\Multipleorderemail\Model\Rule');
                $rule->loadPost($data);

                $data['cond_serialize'] = json_encode($rule->getConditions()->asArray());

                unset($data['conditions']);
            }

            if (isset($data['payment_method_id'])) {
                $paymentMethodId = $data['payment_method_id'];
            } else {
                $paymentMethodId = $model->getPaymentMethodId();
            }

            $defaultPayment = $this->_objectManager->create('Tigren\Multipleorderemail\Helper\Data')->getAllPaymentOptions();
            if (!$paymentMethodId) {
                $data['payment_method_id'] = $defaultPayment;
            }

            $model->setData($data);

            $this->_eventManager->dispatch(
                'multipleorderemail_multipleord_prepare_save',
                ['customemail' => $model, 'request' => $this->getRequest()]
            );

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this Customemail.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                $this->_session->setPageData($model->getData());
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Multipleorderemail.'));
            }
            $this->_getSession()->getFormData();
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Tigren_Multipleorderemail::save');
    }
}
