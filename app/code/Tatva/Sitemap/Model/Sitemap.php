<?php

namespace Tatva\Sitemap\Model;

use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\App\ObjectManager;
use Magento\Robots\Model\Config\Value;
use Magento\Framework\DataObject;

class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    protected $langFactory;
    protected $postFactory;
    protected $_productCollectionFactory;

    public function __construct
    (
        \Tatva\Tag\Model\TagFactory $tagTagFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Tatva\Translate\Model\PostFactory $postFactory,
        \Tatva\Translate\Model\LanguageFactory $langFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Sitemap\Helper\Data $sitemapData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory,
        \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory,
        \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $modelDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot $documentRoot = null
    ) 
    {
        $this->tagTagFactory = $tagTagFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->langFactory = $langFactory;
        $this->postFactory = $postFactory;
        parent::__construct($context, $registry, $escaper, $sitemapData, $filesystem,$categoryFactory,$productFactory,$cmsFactory,$modelDate,$storeManager,$request,$dateTime);
    }

    public function getCustomurlCollection()
    {
        $siteMapcollection = array();

        $pro_collection = $this->_productCollectionFactory->create();
        $pro_collection->getSelect()->joinInner(
            array('ml'=>'multilanguage_data'),'ml.product_id=e.entity_id',
            array('ml.product_id')); 
        $pro_collection->getSelect()->joinInner(
            array('m2' => 'catalog_product_entity_varchar'),'m2.entity_id=e.entity_id',
            array('m2.entity_id'))->where('m2.attribute_id = 88')->group('e.entity_id');
        $pro_collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $pro_collection->getSelect()->columns(array('e.entity_id','m2.value'));
       
        $langcollection = $this->langFactory->create()->getCollection();

        foreach ($pro_collection as $key => $value) {
                
                $pro_id = $value['entity_id'];
                $url_key = $value['value'];

                foreach ($langcollection as $kay=>$value) 
                {
                    $language = $value['laguage'];
                
                    $siteMapcollection[] = new DataObject([
                    'url'        => $url_key.'.html'.'?lang='.$language,
                    'updated_at' => date("Y-m-d h:i:s"),
                    ]);
                }
        }        

        //$baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $tagcollection = $this->tagTagFactory->create()->getPopularCollection()->joinFields($this->_storeManager->getStore()->getId());

        foreach ($tagcollection as $key => $value) {
            
            $tag_name = $value['name'];
            $tag = str_replace(' ', '-', strtolower($tag_name));
            $tagurl = 'tag/'.$tag."-powerpoint-templates-ppt-slides-images-graphics-and-themes";

            $siteMapcollection[] = new DataObject([
                    'url'        => $tagurl,
                    'updated_at' => date("Y-m-d h:i:s"),
            ]);

        }

        return $siteMapcollection;

    }

    public function _initSitemapItems()
    {
        if($this->getStoreId() == 2){
            //parent::_initSitemapItems(); 
        }
        else
        {
            $this->_sitemapItems[] = new DataObject([
                'collection' => $this->getCustomurlCollection(),
            ]);
        } 
        parent::_initSitemapItems();
    }

    protected function _getSitemapRow($url, $lastmod = null, $changefreq = null, $priority = null, $images = null)
    {
        $url = $this->_getUrl($url);
        if(strpos($url, 'catalog/category')!==false || strpos($url, 'catalog/product')!==false)
        {
            return false;
        }else{

            $row = '<loc>' . htmlspecialchars($url) . '</loc>';
            if ($lastmod) 
            {
                $row .= '<lastmod>' . $this->_getFormattedLastmodDate($lastmod) . '</lastmod>';
            }
            if ($changefreq) 
            {
                $row .= '<changefreq>' . $changefreq . '</changefreq>';
            }
            if ($priority) 
            {
                $row .= sprintf('<priority>%.1f</priority>', $priority);
            }
            if ($images) 
            {
                // Add Images to sitemap
                foreach ($images->getCollection() as $image) 
                {
                    if (!strpos($image->getUrl(), 'placeholder'))
                    {
                        $row .= '<image:image>';
                        $row .= '<image:loc>' . htmlspecialchars($image->getUrl()) . '</image:loc>';
                        //$row .= '<image:title>' . htmlspecialchars($images->getTitle()) . '</image:title>';
                        if ($image->getCaption()) 
                        {
                            $row .= '<image:caption>' . htmlspecialchars($image->getCaption()) . '</image:caption>';
                        }
                        $row .= '</image:image>';
                    }
                }
                
                // Add PageMap image for Google web search
                if (!strpos($images->getThumbnail(), 'placeholder'))
                {
                    $row .= '<PageMap xmlns="http://www.google.com/schemas/sitemap-pagemap/1.0"><DataObject type="thumbnail">';
                    $row .= '<Attribute name="name" value="' . htmlspecialchars($images->getTitle()) . '"/>';
                    $row .= '<Attribute name="src" value="' . htmlspecialchars($images->getThumbnail()) . '"/>';
                    $row .= '</DataObject></PageMap>';
                }
            }
            return '<url>' . $row . '</url>'.PHP_EOL;
        }
    }
    protected function _getSitemapIndexRow($sitemapFilename, $lastmod = null)
    {
        $baseUrl = $this->_getStoreBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $url = rtrim($baseUrl, '/').'/sitemap/'.$sitemapFilename;
        $row = '<loc>' . htmlspecialchars($url) . '</loc>';
        if ($lastmod) {
            $row .= '<lastmod>' . $this->_getFormattedLastmodDate($lastmod) . '</lastmod>';
        }

        return '<sitemap>' . $row . '</sitemap>';
    }

    protected function _writeSitemapRow($row)
    {
        $this->_getStream()->write($row);
    }

}