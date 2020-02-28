<?php
/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Multipleorderemail\Model\Customemail\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Tigren\Multipleorderemail\Model\Customemail
     */
    protected $_customemail;

    /**
     * Constructor
     *
     * @param \Tigren\Multipleorderemail\Model\Customemail $customemail
     */
    public function __construct(\Tigren\Multipleorderemail\Model\Customemail $customemail)
    {
        $this->_customemail = $customemail;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->_customemail->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
