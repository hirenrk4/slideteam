<?php
namespace Tatva\Limit\Controller;

    class Router implements \Magento\Framework\App\RouterInterface
    {

     /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;


    /**
     * Config primary
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;


  public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,        
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_eventManager = $eventManager;
        $this->_url = $url;        
        $this->_storeManager = $storeManager;
        $this->_response = $response;
    }

    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        
        if(strpos($identifier, 'blog/') !== false ){
            return false;
        }

        if(strpos($identifier, 'catalog/category') !== false || strpos($identifier, 'new-powerpoint-templates') !== false || strpos($identifier, 'professional-powerpoint-templates') !== false || strpos($identifier, 'free-business-powerpoint-templates') !== false || strpos($identifier, 'share-and-download-products') !== false || strpos($identifier, 'allproducts') !== false) {
            $request->setModuleName('limit')-> //module name
            setControllerName('index')-> //controller name
            setActionName('index');
        } else {
            return false;
        }

        return $this->actionFactory->create(
           'Magento\Framework\App\Action\Forward',
           ['request' => $request]
       );
        
    }
}