<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Tatva_Tag
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Tatva\Tag\Block\Tag;

/**
 * All tags block
 *
 * @category   Mage
 * @package    Tatva_Tag
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class AllTag extends \Magento\Framework\View\Element\Template
{

    protected $_tags;
    protected $_minPopularity;
    protected $_maxPopularity;
   protected $alphabatic_tags = array();
    /**
     * @var \Tatva\Tag\Model\TagFactory
     */
    protected $tagTagFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $toolbar;


       public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,  
        \Tatva\Tag\Model\TagFactory $tagTagFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Tatva\Tag\Model\ResourceModel\Popular\CollectionFactory $tagCollectionFactory,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\UrlInterface $urlinterface,
        array $data = [])
    {
        $this->tagTagFactory = $tagTagFactory;
        $this->_tagCollectionFactory = $tagCollectionFactory;
        $this->storeManager = $storeManager;
        $this->redirect = $redirect;
        $this->response = $response;    
        $this->_urlinterface = $urlinterface;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
            $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
            $breadcrumbs->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'title' => __('Home'),
                        'link' => $this->_storeManager->getStore()->getBaseUrl()
                    ]
                );
            $breadcrumbs->addCrumb('Popular Categories', array('label' => 'Popular Categories'  , 'link' => $this->getUrl('all-powerpoint-categories')) );
            $breadcrumbs->addCrumb('Tagnamesymbol', array('label' => $this->getRequest()->getParam('t') ));

            $query = $this->getRequest()->getParam('t');
            $collection_of_tags = $this->getTags($query);

            if($collection_of_tags)
            {
                $pager = $this->getLayout()->createBlock(
                '\Tatva\Downloadscount\Block\Pager',
                'downloadscount.items.pager');
                $pager->setAvailableLimit(array(500 => 500, 1000 => 1000, 1500 => 1500, 2000 => 2000)); 
                $pager->setShowPerPage(true);  
                $pager->setCollection($collection_of_tags);
                $this->setChild('pager', $pager);
            }

            return $this;
    }
    
    public function getPageCurrentUrl()
    {
        return $this->_urlinterface->getCurrentUrl();
    }
    
    function UrlProcessCustom($currentUrl)
    {
        $equalCount = substr_count($currentUrl,"%3D");
        $questionCount = substr_count($currentUrl,"%3F");       
        $redirect = false;
        if($equalCount >= 1 || $questionCount >= 1)
        {
            $redirect = true;
        }
        
        $currentUrl = str_replace("%3D","=",$currentUrl);
        $currentUrl = rtrim($currentUrl,"=");      
        $currentUrl = str_replace("%3F","?",$currentUrl);
        $currentUrl = str_replace("=?","?",$currentUrl);        
        $fcount =  substr_count($currentUrl,"?");
        
        
        $tcount =  substr_count($currentUrl,"t=");
        
        $query = $this->getRequest()->getParam('t');
        $components = parse_url($currentUrl);
        if(isset($components['query']))
        {
            parse_str($components['query'], $results);
            if(empty($results['t']))
            {
                $this->response->setRedirect('noroute', 404);
                return;  
            }
        }

        if (strpos($query, '=') !== false) {
            $this->response->setRedirect('noroute', 404);
            return; 
        }
        /*if($tcount > 1)
        {
            $this->response->setRedirect('all-powerpoint-categories', 301);
            return; 
        }*/
        
        if(preg_match("/\=/",$currentUrl) && preg_match("/\?/",$currentUrl) && $fcount == 1)
        {
            if($redirect)
            {
                header("Location: ".$currentUrl,true,301);
                exit;
            }else
            {
                return $currentUrl;
            }
        }
        else if(preg_match("/\?/",$currentUrl) && $fcount >= 2)
        {
            //$this->redirect->redirect($this->response, 'all-powerpoint-categories');
            $this->response->setRedirect('all-powerpoint-categories', 301);
            return;     
        }
        else
        {
            if($redirect)
            {
                header("Location: ".$currentUrl,true,301);
                exit;
            }else
            {
                return $currentUrl;
            } 
        }
        
    }

    protected function _loadTags($query=null)
    {
         if (empty($this->_tags) && $query===null && $query=="") {
            $this->_tags = array();
            $tags = $this->tagTagFactory->create()->getPopularCollection()
                ->joinFields($this->storeManager->getStore()->getId());
                /*->limit(100)
                ->load()
                ->getItems();*/
            $this->collection_of_tags = $this->_tags;

            /*if( count($tags) == 0 ) {
                return $this;
            }

            $this->_maxPopularity = reset($tags)->getPopularity();
            $this->_minPopularity = end($tags)->getPopularity();
            $range = $this->_maxPopularity - $this->_minPopularity;
            $range = ( $range == 0 ) ? 1 : $range;
            foreach ($tags as $tag) {
                $tag->setRatio(($tag->getPopularity()-$this->_minPopularity)/$range);
                $this->_tags[$tag->getName()] = $tag;
            }
            ksort($this->_tags);*/
        }
          else{
            if(empty($this->alphabatic_tags[$query])){
                $this->alphabatic_tags[$query] = array();
                $this->alphabatic_tags[$query] = $this->_tagCollectionFactory->create()
                                        ->joinFields($this->storeManager->getStore()->getId(),$query);
            }
            $this->_tags = $this->alphabatic_tags[$query];
        }
        return $this;
    }
     public function str_replace_once ($needle, $replace, $haystack) {
         // Looks for the first occurence of $needle in $haystack
         // And replaces it with $ replace.
            $pos = strpos ($haystack, $needle);
            if ($pos === false) {
                // Nothing found
            return $haystack;
            }
        return substr_replace ($haystack, $replace, $pos, strlen($needle));
    }

    public function getTags($query=null)
    {
        $this->_loadTags($query);
        return $this->_tags;
    }

    public function getMaxPopularity()
    {
        return $this->_maxPopularity;
    }

    public function getMinPopularity()
    {
        return $this->_minPopularity;
    }

    protected function _getHeadText()
    {
        return __('All Tags');
    }

    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }
}
