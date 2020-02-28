<?php
namespace Magecomp\Emailquotepro\Plugin;
use Magento\Framework\ObjectManagerInterface;
use Magento\Backend\Block\Widget\Context;
use Magecomp\Emailquotepro\Helper\Data as EmailquoteHelper;
use Magento\Store\Model\StoreManagerInterface;

class PluginBeforeView
{
	protected $object_manager;
	protected $emailquoteHelper;
	protected $storeManagerInterface;
	
	public function __construct(
		ObjectManagerInterface $om,
		EmailquoteHelper $emailquoteHelper,
		StoreManagerInterface $storeManagerInterface
	) {
         $this->object_manager = $om;
		 $this->emailquoteHelper = $emailquoteHelper;
		 $this->storeManagerInterface = $storeManagerInterface;
    }
    
	public function afterGetButtonList(
        Context $subject,
        $buttonList
    ) {
		if($this->emailquoteHelper->IsAdminActive()){
			if($subject->getRequest()->getFullActionName() == 'sales_order_create_index'){
				$store = $this->storeManagerInterface->getStore();
				$productImageUrl = $store->getUrl('emailquotepro/emailquote/create');				
				$buttonList->add(
					'custom_button',
					[
						'label' => __('Email Quote'),
						'onclick' => "setLocation('".$productImageUrl."')",
						'class' => 'ship primary'
					]
				);
			}
		}
        return $buttonList;
    }
}
