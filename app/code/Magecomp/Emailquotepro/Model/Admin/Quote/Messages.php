<?php
/**
 * Cart2Quote
 */

namespace Magecomp\Emailquotepro\Model\Admin\Quote;

use Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection;
use Magento\Backend\Model\UrlInterface;
use Magento\Backend\Model\Auth\Session;
use Magecomp\Emailquotepro\Model\ResourceModel\Emailproductquote\CollectionFactory as EmailquoteCollection;
use Magento\Framework\Notification\MessageInterface;

class Messages implements MessageInterface
{
    
    protected $backendUrl;
    private $adminSessionInfoCollection;
	protected $authSession;

    public function __construct(
        Collection $adminSessionInfoCollection,
        UrlInterface $backendUrl,
		Session $authSession,
		EmailquoteCollection $collectionFactory
    ) {
        $this->authSession = $authSession;
        $this->backendUrl = $backendUrl;
        $this->adminSessionInfoCollection = $adminSessionInfoCollection;
		$this->collectionFactory = $collectionFactory;
    }

    public function getText()
    {
        $message = __('You Have '.$this->getNewRequestForQuoteCount().' New Inquiries In Magecomp Email Quote');
        return $message;
    }
	
    public function getNewRequestForQuoteCount()
    {
        $collection = $this->collectionFactory->create();
		$collection->addFieldToFilter('status','2');
        return $collection->count();
    }
	public function getIdentity()
	{
        return md5('MAGECOMP_EMAILQUOTEPRO' . $this->authSession->getUser()->getLogdate());
    }
    public function isDisplayed()
    {
        return $this->getNewRequestForQuoteCount() > 0;
    }
	public function getSeverity()
	{
        return \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL;
    }
   

   
}
