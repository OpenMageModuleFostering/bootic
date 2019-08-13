<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Api_Client
{
    /** @var string */
    protected $_defaultUri = 'https://secure.bootic.com/cgi-bin/api';

	/** @var array|Zend_Uri_Http[] */
    protected $_uris = array();

    /** @var Zend_Http_CookieJar */
    protected $_cookieJar;

    /** @var Zend_Rest_Client */
    protected $_restClient;

    const COMMON_LIST_SHIPPING_COMPANY = 'shipping_company';
    const COMMON_LIST_COUNTRY          = 'country';
    const COMMON_LIST_REGION           = 'region';
    
    /**
     * Constructor
     *
     * @param Zend_Rest_Client $restClient
     */
    public function __construct(Zend_Rest_Client $restClient = null)
    {
        if (null !== $restClient) {
            $this->setRestClient($restClient);
        }
    }

    /**
     * Initialize stuff like authentication
     *
     * @return void
     */
    protected function _init()
    {
        // URIs
        if (empty($this->_uris)) {
            $this->_configureUris();
        }

        $httpClient = $this->getRestClient()->getHttpClient();
        $httpClient->resetParameters();
        $this->getRestClient()->setUri($this->getUri('api_ssl'));
        if (null == $this->_cookieJar) {
            $httpClient->setCookieJar();
            $this->_cookieJar = $httpClient->getCookieJar();
        } else {
            $httpClient->setCookieJar($this->_cookieJar);
        }
    }

    /**
     * Makes a call to the API to retrieve actual URIs to use for
     * next calls.
     */
    protected function _configureUris()
    {
        $result = $this->_doRequest('/common/info');
        if ($result->isSuccess()) {
            $this->addUri('api', $result->getData('api_url'));
            $this->addUri('api_ssl', $result->getData('api_url_ssl'));
            $this->addUri('static', $result->getData('server_url_static'));
            $this->addUri('server_main', $result->getData('server_url_main'));
            $this->addUri('server_ssl', $result->getData('server_url_ssl'));
        }
    }

    /**
     * Authenticates user using the username and password
     *
     * @param string $username
     * @param string $password
     * @return Bootic_Api_Result
     */
    public function authenticateByEmailAndPassword($email, $password)
    {
        return $this->_request('/account/login', array(
            'email' => $email,
            'password' => $password,
        ));
    }

    /**
     * Authenticates user using the API Key
     *
     * @param $apiKey
     * @return Bootic_Api_Result
     */
    public function authenticateByApiKey($apiKey)
    {
        return $this->_request('/account/login', array(
            'api_key' => $apiKey
        ));
    }

    /**
     * @return Bootic_Api_Result
     */
    public function getInfo()
    {
        $result = $this->_request('/common/info');

        return $result;
    }

    /**
     * get common list
     * @param str $type
     */
    public function getCommonList($type)
    {
        return $this->_request('/common/list', array('name' => $type));
    }
    /**
     * Creates an account
     *
     * @param array $credentials
     * @return Bootic_Api_Result
     */
    public function createAccount($email, $password)
    {
        $result = $this->_request('account/create', array(
            'email' => $email,
            'password' => $password
        ));

        return $result;
    }

    /**
     * Generates a unique API Key that can be used as an alternate way to connect
     * to the API
     *
     * @param $apiKeyName
     * @param $apiKeyDescription
     * @return Bootic_Api_Result
     */
    public function createKey($apiKeyName, $apiKeyDescription)
    {
        $result = $this->_request('/account/create_key', array(
            'api_key_name' => $apiKeyName,
            'api_key_description' => $apiKeyDescription
        ));

        return $result;
    }

    /**
     * Get Profile
     *
     * @return Bootic_Api_Result
     */
    public function getProfile()
    {
        $result = $this->_request('/account/get_profile');

        return $result;
    }

    /**
     * Edit Profile
     *
     * @param array $profile The profile data to post
     * @return Bootic_Api_Result
     */
    public function editProfile(array $profile)
    {
        $result = $this->_request('/account/edit_profile', $profile);

        return $result;
    }

    /**
     * Edit Profile Picture
     *
     * @param array $data
     * @return Bootic_Api_Result
     */
    public function editProfilePicture(array $data)
    {
        $result = $this->_request('/account/edit_profile_image', $data);

        return $result;
    }

    /**
     * Get order list
     *
     * @param array $params
     * @return Bootic_Api_Result
     */
    public function getOrderList(array $params = array())
    {
        $result = $this->_request('/transaction/list_orders', $params);
        if ($result->getData('record_count') > 0) {
            //load additional data
            $transactions = array();
            foreach ($result->getData('transactions') as $k => $transaction) {
                $p = array('id' => $transaction['transaction']);
                $transactionResult = $this->_request('/transaction/get_order_details', $p);
                if (is_array($transactionResult->getData())) {
                    $transactions[] = $transaction + $transactionResult->getData();
                }
            }
            $result->setData('transactions', $transactions);
        }

        return $result;
    }

    /**
     * Update transaction status
     *
     * @param int $id
     * @param array $data
     */
    public function updateTransactionStatus($id, array $data)
    {
        $data['id'] = $id;
        $result = $this->_request('/transaction/update_status', $data);
        return $result;
    }
    
    /**
     * get sales list
     * @param array $params
     */
    public function getSalesList(array $params = array())
    {
        $result = $this->_request('/transaction/list_sales', $params);
        return $result;
    }

    /**
     * Gets list of storefronts - in our use case, should only be one
     *
     * @return Bootic_Api_Result
     */
    public function getStoreFrontList()
    {
        $result = $this->_request('/storefront/list');

        return $result;
    }

    /**
     * Create a storefront
     *
     * @param array $data
     * @return Bootic_Api_Result
     */
    public function createStorefront(array $data)
    {
        $result = $this->_request('/storefront/create', $data);

        return $result;
    }

    /**
     * Updates a particular storefront
     *
     * @param array $data
     * @return Bootic_Api_Result
     */
    public function updateStorefront(array $data)
    {
        $result = $this->_request('/storefront/update', $data);

        return $result;
    }

    /**
     * Gets all options for a particular storefront, like banners, templates, etc...
     *
     * @param $shop_id
     * @return Bootic_Api_Result
     */
    public function getAvailableOptionsForStorefront($shop_id)
    {
        $result = $this->_request('/storefront/get_available_options', array(
            'shop_id' => $shop_id
        ));

        return $result;
    }

    /**
     * Adds a banner to the list of available banners
     *
     * @param array $data
     * @return Bootic_Api_Result
     */
    public function addStorefrontBanner(array $data)
    {
        $result = $this->_request('/storefront/add_banner', $data);

        return $result;
    }

    /**
     * Adds a product
     *
     * @param array $data
     * @return Bootic_Api_Result
     */
    public function addProduct(array $data)
    {
        $result = $this->_request('product/add_product', $data);

        return $result;
    }

    /**
     * Edits a product
     *
     * @param array $data
     * @return Bootic_Api_Result
     */
    public function editProduct(array $data)
    {
        $result = $this->_request('product/edit_product', $data);

        return $result;
    }

    /**
     * Updates the stock level for a particular product
     *
     * @param array $data
     * @return Bootic_Api_Result
     */
    public function updateProductStock(array $data)
    {
        $result = $this->_request('product/update_product_stock', $data);

        return $result;
    }

    /**
     * Gets the stock level for a particular product
     *
     * @param array $data
     * @return Bootic_Api_Result
     */
    public function getProductStock(array $data)
    {
        $result = $this->_request('product/get_product_stock', $data);

        return $result;
    }

    /**
     * Gets the product detailed info
     *
     * @param array $data
     * @return Bootic_Api_Result
     */
    public function getProductInfo(array $data)
    {
        $result = $this->_request('/product/get_product_info', $data);

        return $result;
    }

    /**
     * Creates a new attribute for product
     *
     * @param array $data
     * @return Bootic_Api_Result
     */
    public function createProductAttribute(array $data)
    {
        $result = $this->_request('product/create_attribute', $data);

        return $result;
    }

    /**
     * Lists all available attributes
     *
     * @return Bootic_Api_Result
     */
    public function listProductAvailableAttributes()
    {
        $result = $this->_request('product/list_available_attributes');

        return $result;
    }

    /**
     * Lists all available categories
     *
     * @return Bootic_Api_Result
     */
    public function listCategories()
    {
        $result = $this->_request('category/list');

        return $result;
    }

    /**
     * Gets all messages for an account
     *
     * @return Bootic_Api_Result
     */
    public function getAccountMessages()
    {
        $result = $this->_request('account/message');

        return $result;
    }

    /**
     * Marks a specific message as read
     *
     * @param array $data
     * @return Bootic_Api_Result
     */
    public function markMessageAsRead(array $data)
    {
        $result = $this->_request('message/mark_as_read', $data);

        return $result;
    }


    public function getOrderDetails($orderId)
    {
        $result = $this->_request('/transaction/get_order_details', array('id' => $orderId));

        return $result;
    }

    /**
     * Issues the request, always with POST method
     *
     * @param string $path
     * @param array  $params
     * @return Bootic_Api_Result
     */
    protected function _request($path, array $params = array())
    {
        $this->_init();

        return $this->_doRequest($path, $params);
    }

    protected function _doRequest($path, array $params = array())
    {
        $client = $this->getRestClient();
        $path = '/' . ltrim($path, '/');
        $realPath = rtrim($client->getUri()->getPath(), '/') . $path;

        try {
            $response = $this->getRestClient()->restPost($realPath, $params);

            // If network or server failed, we throw an exception
            if ($response->isError()) {
                throw new \Exception($response->getMessage());
            }

            return new Bootic_Api_Result($response->getBody());

        } catch(\Exception $e) {
            // If anything weird happens, we throw a Bootic_Api_Exception
            throw new Bootic_Api_Exception($e->getMessage());
        }
    }

    /**
     * @param Zend_Rest_Client $restClient
     * @return Bootic_Api_Client Fluent interface
     */
    public function setRestClient($restClient)
    {
        $this->_restClient = $restClient;
        $this->_restClient->setUri(Zend_Uri_Http::fromString($this->_defaultUri));

        return $this;
    }

    /**
     * @return Zend_Rest_Client
     */
    public function getRestClient()
    {
        if (null == $this->_restClient) {
            $restClient = new Zend_Rest_Client();
            $this->setRestClient($restClient);
        }

        return $this->_restClient;
    }

    /**
     * @param Zend_Http_CookieJar $cookieJar
     * @return Bootic_Api_Client Fluent interface
     */
    public function setCookieJar($cookieJar)
    {
        $this->_cookieJar = $cookieJar;

        return $this;
    }

    /**
     * @return Zend_Http_CookieJar
     */
    public function getCookieJar()
    {
        return $this->_cookieJar;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function addUri($key, $value)
    {
        $this->_uris[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasUri($key)
    {
        return array_key_exists($key, $this->_uris);
    }

    /**
     * @param string $key
     * @param bool   $asString
     * @return Zend_Uri_Http
     *
     * @throws \InvalidArgumentException
     */
    public function getUri($key, $asString = false)
    {
        if ($this->hasUri($key)) {
            if ($asString) {
                return (string) $this->_uris[$key];
            }

            return (!$this->_uris[$key] instanceof Zend_Uri_Http)
                ? Zend_Uri_Http::fromString($this->_uris[$key])
                : $this->_uris[$key]
            ;
        }

        throw new \InvalidArgumentException(sprintf('No URI found for %s', $key));
    }
}
