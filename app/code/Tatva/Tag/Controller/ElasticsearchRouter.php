<?php
namespace Tatva\Tag\Controller;


class ElasticsearchRouter implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
    }

    /**
     * Validate and Match
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifierTag = trim($request->getPathInfo(), '/');    
        if(strpos($identifierTag, 'catalogsearch') !== false) {
            $paramValue = substr($identifierTag, strpos($identifierTag, "q=") + 2);
            $request->setParam('q',$paramValue);
            $request->setModuleName('catalogsearch')->setControllerName('result')->setActionName('index');
        } else {
            return;
        }

        return $this->actionFactory->create(
            'Magento\Framework\App\Action\Forward',
            ['request' => $request]
        );
    }
}