<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Api_Result
{
    /** @var boolean */
    protected $_success;

    /** @var string */
    protected $_errorMessage;

    /** @var string */
    protected $_hasWarning;

    /** @var array */
    protected $_warningMessages = array();

    /** @var array|mixed */
    protected $_data = array();

    /** @var string */
    protected $_rawData;

    /**
     * Constructor
     *
     * @param string $rawData
     * @param string $dataType
     */
    public function __construct($rawData, $dataType = 'json')
    {
        $this->_rawData = $rawData;
        $this->parseData($dataType);
    }

    /**
     * Parses the raw data, extracts status and messages,
     * and returns an array representation of the actual data
     *
     * @param string $dataType
     *
     * @throws Bootic_Api_Exception_UnsupportedDataType
     */
    public function parseData($dataType)
    {
        if (empty($this->_rawData)) {
            return;
        }

        if (!$this->_supportDataType($dataType)) {
            throw new Bootic_Api_Exception_UnsupportedDataType(
                sprintf('"%s" data type is not supported'), $dataType
            );
        }

        $data = Zend_Serializer::unserialize($this->_rawData, array('adapter' => $dataType));

        $status = true;
        if (isset($data['status'])) {
            $status = (bool) $data['status'];
            unset($data['status']);
        } elseif (empty($data)) {
            $status = false;
        }
        $this->setSuccess($status);

        if (!$this->isSuccess()) {
            if (isset($data['error'])) {
                $this->setErrorMessage($data['error']);
                unset($data['error']);
            }
        }

        $hasWarning = false;
        if (isset($data['warnings'])) {
            if (count($data['warnings']) > 0) {
                $hasWarning = true;
            }

            $this->setWarningMessages($data['warnings']);
            unset($data['warnings']);
        }
        $this->setHasWarning($hasWarning);

        Mage::log($data);
	if (isset($data['data'])) {
        	$this->setData(null, $data['data']);
	}
    }

    /**
     * Checks if the input data type is supported
     *
     * @param string $dataType
     * @return bool
     */
    protected function _supportDataType($dataType)
    {
        return in_array(strtolower($dataType), array('json'));
    }

    /**
     * @param string $name
     * @param array|mixed $data
     * @return Bootic_Api_Result Fluent interface
     */
    public function setData($name = null, $data)
    {
        if (null !== $name) {
            $this->_data[$name] = $data;
        } else {
            $this->_data = $data;
        }

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return array|mixed
     */
    public function getData($name = null, $default = null)
    {
        if (null !== $name) {
            return $this->hasData($name) ? $this->_data[$name] : $default;
        }

        return $this->_data;
    }

    /**
     * @param $warningMessages
     */
    public function setWarningMessages($warningMessages)
    {
        $this->_warningMessages = (array) $warningMessages;
    }

    /**
     * @return array
     */
    public function getWarningMessages()
    {
        return $this->_warningMessages;
    }

    /**
     * @param boolean $hasWarning
     */
    public function setHasWarning($hasWarning)
    {
        $this->_hasWarning = (bool) $hasWarning;
    }

    /**
     * @return boolean
     */
    public function hasWarning()
    {
        return $this->_hasWarning;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->_errorMessage = $errorMessage;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    /**
     * @param boolean $success
     */
    public function setSuccess($success)
    {
        $this->_success = (bool) $success;
    }

    /**
     * @return boolean
     */
    public function getSuccess()
    {
        return $this->_success;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->getSuccess();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasData($name)
    {
        return array_key_exists($name, $this->_data);
    }
}
