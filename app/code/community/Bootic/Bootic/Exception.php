<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Exception extends Zend_Exception
{
    /** @var boolean */
    protected $_isWarning;

    public function __construct($msg = '', $code = 0, Exception $previous = null, $isWarning = false)
    {
        $this->_isWarning = $isWarning;
        parent::__construct($msg, $code, $previous);
    }

    public function isWarning()
    {
        return $this->_isWarning;
    }
}
