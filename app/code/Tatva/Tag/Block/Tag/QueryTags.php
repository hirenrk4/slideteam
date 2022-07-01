<?php
namespace Tatva\Tag\Block\Tag;

/**
 * This block over rides magento's default functionality for listing tags with pager
 */


class QuerTags extends \Tatva\Tag\Block\Tag\AllTag
{

    /**
     * Tags are set as array of objects using their first letter as key
     */
    protected $alphabatic_tags = array();

    /**
     *
     */
    protected $query;

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
        $this->storeManager = $storeManager;
        $this->redirect = $redirect;
        $this->response = $response;  
        $this->_urlinterface = $urlinterface;      
        parent::__construct($context,$tagTagFactory,$storeManager,$tagCollectionFactory,$response,$redirect,$urlinterface,$data);
    }

    /**
     * These are the collections of objects of tags which are going to be showed
     */
    protected $collection_of_tags;

    protected function _loadTags($query = null)
    {   

        if($query == null || $query == "ALL"){
            if(empty($this->_tags)){
                $this->_tags = array();
                $this->_tags = $this->tagTagFactory->create()->getPopularCollection()
                                ->joinFields($this->storeManager->getStore()->getId());
                $this->collection_of_tags = $this->_tags;
            }
        }
        else{
            if(empty($this->alphabatic_tags[$query])){
                $this->alphabatic_tags[$query] = array();
                $this->alphabatic_tags[$query] = Mage::getResourceModel('tag/popular_collection')
                                        ->joinFields(Mage::app()->getStore()->getId(),$query);
            }
            $this->collection_of_tags = $this->alphabatic_tags[$query];
        }
        return $this;
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->query = Mage::app()->getRequest()->getParam('t');
        $collection_of_tags = $this->getTags($this->query);

        $this->addBreadCrumb();


        $toolbar = $this->getLayout()->createBlock('corerewrite/tag_toolbar');
        $toolbar->setCollection($collection_of_tags);
        $pager = $this->getLayout()->createBlock('page/html_pager');
        $toolbar->setChild('product_list_toolbar_pager', $pager);
        $this->setCollection($collection_of_tags);
        $this->setChild('toolbar', $toolbar);

        return $this;

    }

    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }

    public function getToolbarHtml() {
        if(!is_null($this->query))
            return $this->getChildHtml('toolbar');
        else
            return '';
    }

    public function getTags($query = null)
    {
        $this->_loadTags($query);

        return $this->collection_of_tags;
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

    protected function addBreadCrumb(){
        // add Home breadcrumb
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $title = $this->__("Search results for: '%s'", $this->query);
            $all_tag_title = $this->__("Popular Categories");

            $breadcrumbs->addCrumb('home', array(
                'label' => $this->__('Home'),
                'title' => $this->__('Go to SlideTeam Home'),
                'link'  => Mage::getBaseUrl()
            ))->addCrumb('query', array(
                'label' => $all_tag_title,
                'title' => $all_tag_title,
                'link'  => $this->getUrl('all-powerpoint-categories')
            ));
            if(!is_null($this->query)){
                $breadcrumbs->addCrumb('search', array(
                    'label' => $this->query,
                    'title' => $this->query
                ));
            }
        }
    }
}