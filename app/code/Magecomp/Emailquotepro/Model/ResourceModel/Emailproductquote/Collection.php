<?php

namespace Magecomp\Emailquotepro\Model\ResourceModel\Emailproductquote;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	public function _construct()
	{
		$this->_init("Magecomp\Emailquotepro\Model\Emailproductquote", "Magecomp\Emailquotepro\Model\ResourceModel\Emailproductquote");
	}
}	 