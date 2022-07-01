<?php
/**
 * Base router
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Framework\App\Router;

class Base extends \Magento\Framework\App\Router\Base
{

	public function __construct(
        \Magento\Framework\App\Router\ActionList $actionList,
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\App\Route\ConfigInterface $routeConfig,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \Magento\Framework\App\Router\PathConfigInterface $pathConfig
    ) {
        $this->actionList = $actionList;
        $this->actionFactory = $actionFactory;
        $this->_responseFactory = $responseFactory;
        $this->_defaultPath = $defaultPath;
        $this->_routeConfig = $routeConfig;
        $this->_url = $url;
        $this->nameBuilder = $nameBuilder;
        $this->pathConfig = $pathConfig;
    }

    protected function parseRequest(\Magento\Framework\App\RequestInterface $request)
    {
        
        $url = $this->pathConfig->getCurrentSecureUrl($request);
        $pattern = "/\(/";
        $homepageUrl = $this->_url->getUrl();
        
        if(preg_match($pattern, $url))
        {
            $this->_responseFactory->create()->setRedirect($homepageUrl)->sendResponse();
            exit;
        }

        $pattern = "/\/business_powerpoint_diagrams\/pricing/";
        if(preg_match($pattern,$url) && ($url == $homepageUrl."pricing" || $url == $homepageUrl."pricing/")){
            
            $pricingUrl = str_replace("/business_powerpoint_diagrams/", "/", $url);
            $this->_responseFactory->create()->setRedirect($pricingUrl)->sendResponse();
            exit;
        }

        if(preg_match("/loginPost\/referer\/aHR0cHM6Ly93d3cuc2xpZGV0ZWFtLm5ldC8%2C/",$url))
        {
            $this->_responseFactory->create()->setRedirect($homepageUrl)->sendResponse();
            exit;    
        }
       
        if(preg_match("/\/aHR0cHM6Ly93d3cuc2xpZGV0ZWFtLm5ldC9jdXN0b21lci9hY2NvdW50L2xvZ291dFN1Y2Nlc3Mv/",$url))
        {           
            $noroute = $this->_url->getUrl("noRoute");
            //$urlexplode = explode("referer",$url);
            $this->_responseFactory->create()->setRedirect($noroute)->sendResponse();
            exit;
        }
        $output = [];

        $path = trim($request->getPathInfo(), '/');

        $params = explode('/', $path ? $path : $this->pathConfig->getDefaultPath());
        foreach ($this->_requiredParams as $paramName) {
            $output[$paramName] = array_shift($params);
        }

        for ($i = 0, $l = sizeof($params); $i < $l; $i += 2) {
            $output['variables'][$params[$i]] = isset($params[$i + 1]) ? urldecode($params[$i + 1]) : '';
        }
        return $output;
    }
}