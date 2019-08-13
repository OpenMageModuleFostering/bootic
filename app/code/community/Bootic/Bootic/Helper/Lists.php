<?php
/**
 * Lists.php
 * @copyright  Copyright (c) 2012 by  Bootic.
 */
class Bootic_Bootic_Helper_Lists extends Bootic_Bootic_Helper_Abstract
{
    /**
     * instance cache of regions
     * @var array
     */
    protected $regions   = null;
    
    /**
     * instance cache of countries
     * @var array
     */
    protected $countries = null;
    
    /**
     * instance cache of shipping companies
     * @var array
     */
    protected $shippingCompanies = null;
    
    /**
     * get region label by $regionId
     * @param str $regionId
     * @return string|NULL
     */
    public function getRegionLabel($regionId)
    {
        $regionData = $this->getRegionById($regionId);
        if (is_array($regionData)) {
            return $regionData['name'];
        }
        return null;
    }
    
    /**
     * get region data by ID
     * @param array|null $regionId
     */
    public function getRegionById($regionId)
    {
        foreach ($this->getRegions() as $region) {
            if ($region['id'] == $regionId) {
                return $region;
            }
        }
        return null;
    }
    
    /**
     * get country label
     * @param str $countryId
     */
    public function getCountryLabel($countryId)
    {
        $countryData = $this->getCountryById($countryId);
        if (is_array($countryData)) {
            return $countryData['name'];
        }
        return null;
    }
    
    /**
     * get country data
     * @param str $countryId
     */
    public function getCountryById($countryId)
    {
        foreach ($this->getCountries() as $country) {
            if ($country['id'] == $countryId) {
                return $country;
            }
        }
        return null;
    }
    
    /**
     * get region list
     * @return array
     */
    public function getRegions()
    {
        if ($this->regions === null) {
            $result = $this->getBootic()->getCommonList(Bootic_Api_Client::COMMON_LIST_REGION);
            if ($result->isSuccess()) {
                $this->regions = $result->getData();
            }
        }
        return $this->regions;
    }
    
    /**
     * get country list
     * @return array
     */
    public function getCountries()
    {
        if ($this->countries === null) {
            $result = $this->getBootic()->getCommonList(Bootic_Api_Client::COMMON_LIST_COUNTRY);
            if ($result->isSuccess()) {
                $this->countries = $result->getData();
            }
        }
        return $this->countries;
    }
    
    /**
     * get bootic carried ID from a magento carrier code
     * @param str $code
     */
    public function getIdByCarrierCode($code)
    {
        $companyName = $this->carrierCodeToCompanyName($code);
        foreach ($this->getShippingCompanies() as $company) {
            if (strtolower($company['name']) == strtolower($companyName)) {
                return $company['id'];
            }
        }
    }
    
    /**
     * get carrier name based on a magento carrier code
     * @param str $code
     * @return string
     */
    public function carrierCodeToCompanyName($code)
    {
        $name = 'Other';
        switch ($code) {
            case 'fedex':
                $name = 'FedEx';
                break;
            case 'usps':
                $name = 'USPS';
                break;
            case 'ups':
                $name = 'UPS';
                break;
                
        }
        return $name;
    }
    
    /**
     * get shipping companies
     * @return array
     */
    public function getShippingCompanies()
    {
        if ($this->shippingCompanies === null) {
            $result = $this->getBootic()->getCommonList(Bootic_Api_Client::COMMON_LIST_SHIPPING_COMPANY);
            if ($result->isSuccess()) {
                $this->shippingCompanies = $result->getData();
            }
        }
        return $this->shippingCompanies;
    }
}