<?php

namespace Tatva\Brochure\Block;

use Magento\Framework\View\Element\Template;

class Blog extends Template
{
    
    protected $_listPost;
    protected $_postCollection = null;
    protected $_postfactory;

    public function __construct(
        Template\Context $context,
        \FishPig\WordPress\Block\Post\ListPost $listpost,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory $postfactory,
        array $data = []
    )
    {       
        $this->_listPost =  $listpost;
        $this->scopeConfig = $scopeConfig;
        $this->_postfactory = $postfactory;
        parent::__construct($context, $data);        
    }

    public function getSelectedListPost()
    {
        $blogIds = explode(",",$this->scopeConfig->getValue('resume/general/resume_blog_ids', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)); 
        if(!empty($blogIds))
        {
            $blogIds = array_slice($blogIds,0,2);       
            //$blogIds_keys = array_rand($blogIds,2);
            //$blogarray = array($blogIds[$blogIds_keys[0]],$blogIds[$blogIds_keys[1]]);
            $this->_postCollection = $this->_postfactory->create();
            $this->_postCollection->getSelect()->where("Id IN (?)",$blogIds);
            //$this->_postCollection->getSelect()->where("Id IN (?)",$blogarray);           
            return $this->_postCollection;
        }
        else
        {
            return NULL;
        }
        
    }

    public function getOneBlogPost()
    {
        $blogId = $this->scopeConfig->getValue('brochure/general/one_brochure_blog_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!empty($blogId))
        {           
            $this->_postCollection = $this->_postfactory->create();
            $this->_postCollection->getSelect()->where("Id = ".$blogId);
            
            
            return $this->_postCollection;
        }
        else
        {
            return NULL;
        }
        
    }

    public function renderOnePost($post)
    {
        $html = $this->_listPost->renderBlogOnePost($post,"brochure");
        return $html;
    }

}  