<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $product = Mage::getModel('catalog/product')->load(83);

        $usedProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);

        foreach ($usedProducts as $usedProduct) {
            $arr[] = $usedProduct->getId();
        }

        var_dump($arr);
    }

    /**
     * Index action
     */
    public function childrenAction()
    {
        var_dump(Mage::getModel('catalog/product_type_configurable')->getChildrenIds(83));
    }

    public function testAction()
    {
        $product = Mage::getModel('bootic/product_data')->load(83);
        echo '<pre>'.print_r($product, true).'</pre>';
    }

    public function attributeAction()
    {
        $product = Mage::getModel('catalog/product')->load(93);
        var_dump(Mage::helper('bootic/product')->makeConfigurableProduct($product));
    }

    public function categoriesAction()
    {
        $booticCategories = Mage::helper('bootic/product')->listCategories();

        foreach ($booticCategories as $booticCategory) {
            $category = Mage::getModel('bootic/category');
            $category->setId($booticCategory['id']);
            $category->setName($booticCategory['name']);
            $category->setParents($booticCategory['parents']);
            try {
                $category->save();
                echo 'cool<br/>';
            } catch (Exception $e) {
                echo $category->getCategoryId() . '<br/>';
                echo $e->getMessage() . '<br/>';
            }
        }

//        echo ('<pre>' . print_r(Mage::helper('bootic/product')->listCategories(), true) . '</pre>');

//        echo ('<pre>' . print_r(Mage::getModel('bootic/category')->load(49, 'category_id'), true) . '</pre>');

//        $collection = mage::getResourceModel('bootic/category_collection');
//        foreach ($collection as $cat) {
//            echo ('<pre>' . print_r($cat, true) . '</pre>');
//        }

        echo ('<pre>' . print_r(Mage::helper('bootic/category')->getBooticCategories(), true) . '</pre>');
//        echo ('<pre>' . print_r(Mage::helper('bootic/category')->getBooticCategories(), true) . '</pre>');

    }

    public function categorycollectionAction()
    {
        /** @var Mage_Catalog_Model_Product $product  */
        $product = Mage::getModel('catalog/product')->load(166);

//        $categoryCollection = $product->getCategoryCollection()->addAttributeToSort('level', 'DESC');
        $categoryCollection = Mage::getModel('bootic/resource_product')->getMappedCategoryCollection($product);

        foreach ($categoryCollection as $category) {
            echo $category->getId() . ': ' . $category->getBooticCategoryId() . '<br/>';
        }
    }

    public function imagesAction()
    {
        /** @var Mage_Catalog_Model_Product $product  */
        $product = Mage::getModel('catalog/product')->load(83);

        $gallery = $product->getMediaGalleryImages();
        foreach ($gallery as $image) {
            var_dump(Mage::helper('catalog/image')->init($product, 'thumbnail', $image->getFile())->__toString());
        }
    }

    public function productAction()
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')->load(46);

        $gallery = $product->getMediaGalleryImages();

        foreach ($gallery as $image) {
            echo 'image';
        }
    }

    public function cronAction()
    {
        try {
            Mage::helper('bootic/product')->uploadProducts();
            Mage::helper('bootic/product')->editProducts();
            Mage::helper('bootic/product')->checkProductsStatus();
            Mage::helper('bootic/product')->syncProductsStocks();
            Mage::helper('bootic/orders')->processPendingOrders();
            Mage::helper('bootic/orders')->syncOrders();
        } catch (Exception $e) {
            var_dump($e);
        }
    }

    public function statusAction()
    {
        Mage::helper('bootic/product')->checkProductsStatus();
    }

    public function messagesAction()
    {
        Mage::helper('bootic/message')->pullUnreadMessages();
    }

    public function testScopeAction()
    {
        $messages = Mage::helper('bootic/message')->getAccountMessages();

        $productInfo = Mage::helper('bootic/product')->getProductInfo();
    }

    public function shortDescAction()
    {
        $p = Mage::getModel('catalog/product')->load(140);

        var_dump($p->getShortDescription());

        // Truncate the string
                $shortDesc = Mage::helper('core/string')->truncate($p->getShortDescription(), 120, '...');
                // Remove line breaks
                $shortDesc = preg_replace("/[\n\r\t]/","", $shortDesc);
                // Make sure everything is UTF8
                $shd = Mage::helper('core/string')->cleanString($shortDesc);
        var_dump($shd);
    }

    public function orderslistAction()
    {
        $orders = Mage::helper('bootic/product')->getOrdersList();

        echo '<pre>' . print_r($orders, true) . '</pre>';
    }

    public function salesListAction()
    {
        $sales = Mage::helper('bootic/product')->getSalesList();

        echo '<pre>' . print_r($sales, true) . '</pre>';
    }

    public function orderDetailsAction()
    {
        $details = Mage::helper('bootic/product')->getOrderDetails(5510);

        echo '<pre>' . print_r($details, true) . '</pre>';
    }

    public function processOrdersAction()
    {
        Mage::helper('bootic/orders')->processPendingOrders();
    }

    public function syncOrdersAction()
    {
        Mage::helper('bootic/orders')->syncOrders();
    }

    public function pullShippedOrdersAction()
    {
        Mage::helper('bootic/orders')->pullShippedOrderStatus();
    }

    public function uploadAction()
    {
        Mage::helper('bootic/product')->uploadProducts();
    }

    public function editAction()
    {
        Mage::helper('bootic/product')->editProducts();
    }

    public function syncStockAction()
    {
        Mage::helper('bootic/product')->syncProductsStocks();
    }

    public function regionsAction()
    {
        Mage::helper('bootic/lists')->getCountries();
    }

    public function testTestAction()
    {
//        $result = Mage::helper('bootic/category')->listOrigCategories();
//        $booticCategories = $result->getData();



        $categoryResource = Mage::getResourceModel('bootic/category');
//        $categoryResource->insertCategories($booticCategories);
        $result = $categoryResource->getLastUpdate();

    }

    public function syncCategoriesAction()
    {
        Mage::helper('bootic/category')->syncCategories();
    }

}
