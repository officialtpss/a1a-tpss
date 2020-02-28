<?php
/**
 * Copyright © 2015 Magecomp. All rights reserved.
 */

namespace Magecomp\Emailquotepro\Model;

use Magento\Framework\Exception\RequestException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

class Emailproductquote extends AbstractModel
{
    public function __construct(
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        $data = array()
    )
	{
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
	public function _construct()
	{
        $this->_init('Magecomp\Emailquotepro\Model\ResourceModel\Emailproductquote');
    }
}