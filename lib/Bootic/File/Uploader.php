<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_File_Uploader extends Varien_File_Uploader
{
    /**
     * {@inheritDoc}
     */
    public function __construct($fileId)
    {
        try {
            parent::__construct($fileId);
        } catch(Exception $e) {
            $this->_fileExists = false;
        }
    }


    /**
     * Returns the original file name
     *
     * @return string
     */
    public function getIncomingFileName()
    {
        return $this->_file['name'];
    }

    /**
     * Returns the original file extension
     *
     * @return string
     */
    public function getIncomingFileExtension()
    {
        preg_match('/[^?]*/', $this->_file['name'], $matches);
        $string = $matches[0];

        $pattern = preg_split('/\./', $string, -1, PREG_SPLIT_OFFSET_CAPTURE);

        if(count($pattern) > 1)
        {
            $filenamepart = $pattern[count($pattern)-1][0];
            preg_match('/[^?]*/', $filenamepart, $matches);
            return $matches[0];
        }

        return '';
    }

    /**
     * Returns the tmp file binary content
     *
     * @return null|string
     */
    public function getTempFileBinaryContent()
    {
        return $this->_fileExists ? file_get_contents($this->_file['tmp_name']) : null;
    }

    /**
     * Returns the tmp file name (full path)
     *
     * @return string|null
     */
    public function getTempFileName()
    {
        return $this->_fileExists ? $this->_file['tmp_name'] : null;
    }
}