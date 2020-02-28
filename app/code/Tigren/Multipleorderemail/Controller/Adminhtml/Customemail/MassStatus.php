<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */
namespace Tigren\Multipleorderemail\Controller\Adminhtml\Customemail;

use Tigren\Multipleorderemail\Controller\Adminhtml\AbstractMassAction;
use Tigren\Multipleorderemail\Model\CustomemailFactory;
use Tigren\Multipleorderemail\Model\ResourceModel\Customemail\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 */
class MassStatus extends AbstractMassAction
{
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CustomemailFactory $manageFactory
    )
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->model = $manageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction($collection)
    {
        $itemsSelected = 0;
        foreach ($collection->getAllIds() as $itemId) {
            $model = $this->model->create()->load($itemId);
            $status = $this->model->create()->load($itemId)->getStatus();
            if ($status) {
                $model->setStatus(0);
            } else {
                $model->setStatus(1);
            }
            $model->save();
            $itemsSelected++;
        }

        if ($itemsSelected) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were changed status.', $itemsSelected));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}