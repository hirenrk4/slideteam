<?php

namespace Tatva\Sitemap\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;

class SitemapGenerator
{
    const OPEN_TAG_KEY = 'strat';

    const CLOSE_TAG_KEY = 'end';

    const CHANNEL_INIT_CONTENT = 'channel initial content';
    
    const FILE_NAME = 'sitemap';

    const PATH = 'sitemap/sitemap-slideteam';

    /**
     * start and end tags
     *
     * @var array
     */
    protected $_tags = [];

    protected $_mainfile = [];

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directory;

    /**
     * @var \Magento\Framework\Filesystem\File\Write
     */
    protected $_stream;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Tatva\Theme\Helper\Header
     */
    protected $themeHelper;

    
    public function __construct(
            \Magento\Framework\App\ResourceConnection $resourceConnection,
            \FishPig\WordPress\Model\ResourceConnection $blogresourceConnection,
            \Magento\Framework\Filesystem $filesystem,
            \Magento\Framework\Escaper $escaper,
            \Tatva\Theme\Helper\Header $themeHelper
    ) {
        $this->_resourceConnection = $resourceConnection;
        $this->_blogresourceConnection = $blogresourceConnection;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::PUB);
        $this->_escaper = $escaper;
        $this->themeHelper = $themeHelper;
    }

    /**
     * Get file handler
     *
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     * @throws LocalizedException
     */
    protected function _getStream()
    {
        if ($this->_stream) {
            return $this->_stream;
        } else {
            throw new LocalizedException(__('File handler unreachable'));
        }
    }

    protected function _init()
    {
        $this->_tags = [
            self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' .
                PHP_EOL .$this->rssTagContent(),
            self::CLOSE_TAG_KEY =>
                '</urlset>',
            //self::CHANNEL_INIT_CONTENT =>$this->getFeedBasicContent(),
        ];

        $this->_mainfile = [
            self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' .
                PHP_EOL .$this->mainContent(),
            self::CLOSE_TAG_KEY =>
                '</sitemapindex>',
            //self::CHANNEL_INIT_CONTENT =>$this->getFeedBasicContent(),
        ];

        if (!$this->_directory->isExist(self::PATH)) {
            throw new LocalizedException(
                __(
                    'Please create the specified folder "%1" before create the sitemap.',
                    $this->_escaper->escapeHtml(self::PATH)
                )
            );
        }

        if (!$this->_directory->isWritable(self::PATH)) {
            throw new LocalizedException(
                __('Please make sure that "%1" is writable by the web-server.', self::PATH)
            );
        }
    }
    
    public function execute()
    {
       $this->_init();

       $this->generateBlogsitemap();

       $this->generatePdpsitemap(1);

       $this->generatePdpsitemap(2);

       $this->generateCategorysitemap(1);

       $this->generateCategorysitemap(2);

       $this->generateCmssitemap();

       $this->generatePdpMultilangsitemap();

       $this->generateTagsitemap();

       $this->generateImagesitemap();

    }

    public function generateBlogsitemap()
    {
        $sql = "SELECT (CASE WHEN `post_type` = 'post' THEN (CONCAT(post_name, '/')) WHEN `post_type` = 'page' THEN (CONCAT('%postnames%', '/')) END) AS `permalink`, main_table.post_modified_gmt as updated_at FROM `wp_posts` AS `main_table` WHERE (main_table.post_status LIKE 'publish' ) AND (main_table.post_title != '' ) AND (`main_table`.`post_type` IN('post', 'page')) ORDER BY main_table.menu_order ASC, main_table.post_date DESC";
        $connection = $this->_blogresourceConnection->getConnection();
        $result = $connection->fetchAll($sql);
        $count = count($result);

        if($count >= 50000)
        {
            $startLimit = 0;
            $endLimit = 50000;
            $loopIterations = ceil($count/$endLimit);

            for($m=1;$m<=$loopIterations;$m++){
                
                $sql1 = "SELECT (CASE WHEN `post_type` = 'post' THEN (CONCAT(post_name, '/')) WHEN `post_type` = 'page' THEN (CONCAT('%postnames%', '/')) END) AS `permalink`, main_table.post_modified_gmt as updated_at FROM `wp_posts` AS `main_table` WHERE (main_table.post_status LIKE 'publish' ) AND (main_table.post_title != '' ) AND (`main_table`.`post_type` IN('post', 'page')) ORDER BY main_table.menu_order ASC, main_table.post_date DESC LIMIT ".$startLimit.",".$endLimit;
                $connection = $this->_blogresourceConnection->getConnection();
                $result1 = $connection->fetchAll($sql1);

                $this->_createSitemap($m,'blog-');  

                foreach ($result1 as $key) {
                    
                    $xml = $this->_getSitemapRow(
                        $this->getSiteLink().'blog/'.$key['permalink'],
                        $key['updated_at']
                    );
                    $this->_writeSitemapRow($xml);
                }
                $this->_finalizeSitemap();   
                $startLimit += $endLimit;  
            }

            $this->generateMainFile('blog');
            for($i=1;$i<=$loopIterations;$i++){
                $xml = $this->_getFilelist($i,'blog');
                $this->_writeSitemapRow($xml); 
            }
            $this->_finalizemain();
        } 
        else{

            $m = '';

            $this->_createSitemap($m,'blog');       
            foreach ($result as $key) {
                    
                    $xml = $this->_getSitemapRow(
                        $this->getSiteLink().'blog/'.$key['permalink'],
                        $key['updated_at']
                    );
                    $this->_writeSitemapRow($xml);
                }
                $this->_finalizeSitemap();     
        }
    }

    public function generatePdpsitemap($store)
    {
        $sql = "SELECT url_key,updated_at FROM catalog_product_flat_".$store." WHERE entity_id in (SELECT entity_id FROM url_rewrite WHERE entity_type LIKE 'product' AND store_id = ".$store." GROUP BY entity_id) ORDER BY entity_id ASC";
        $connection = $this->_resourceConnection->getConnection();
        $result = $connection->fetchAll($sql);
        $count = count($result);
        
        if($count >= 50000)
        {
            $startLimit = 0;
            $endLimit = 50000;
            $loopIterations = ceil($count/$endLimit);
            
            for($m=1;$m<=$loopIterations;$m++){
            
                $sql1 = "SELECT url_key,updated_at FROM catalog_product_flat_".$store." WHERE entity_id in (SELECT entity_id FROM url_rewrite WHERE entity_type LIKE 'product' AND store_id = ".$store." GROUP BY entity_id) ORDER BY entity_id ASC LIMIT ".$startLimit.",".$endLimit;
                $connection = $this->_resourceConnection->getConnection();
                $result1 = $connection->fetchAll($sql1);

                $this->_createSitemap($m,'pdp-'.$store.'-');  

                foreach ($result1 as $key) {
                    
                    if($store == 2)
                    {
                        $x = 'business_powerpoint_diagrams/'; 
                    }
                    else{
                        $x='';
                    }
                    $xml = $this->_getSitemapRow(
                        $this->getSiteLink().$x.$key['url_key'].'.html',
                        $key['updated_at']
                    );
                    $this->_writeSitemapRow($xml);
                }
                $this->_finalizeSitemap();   
                $startLimit += $endLimit;  
            }

            $this->generateMainFile('pdp-'.$store);
            for($i=1;$i<=$loopIterations;$i++){
                $xml = $this->_getFilelist($i,'pdp-'.$store);
                $this->_writeSitemapRow($xml); 
            }
            $this->_finalizemain();
        } 
        else{
            
            $m = '';
            if($store == 2)
            {
                $x = 'business_powerpoint_diagrams/'; 
            }
            else{
                $x='';
            }

            $this->_createSitemap($m,'pdp-'.$store);       
            foreach ($result as $key) {
                    
                    $xml = $this->_getSitemapRow(
                        $this->getSiteLink().$x.$key['url_key'].'.html',
                        $key['updated_at']
                    );
                    $this->_writeSitemapRow($xml);
                }
                $this->_finalizeSitemap();     
        }
    }

    public function generateCategorysitemap($store)
    {
        $sql = "SELECT url_path,updated_at FROM catalog_category_flat_store_".$store;
        $connection = $this->_resourceConnection->getConnection();
        $result = $connection->fetchAll($sql);
        
            $m = '';
            if($store == 2)
            {
                $x = 'business_powerpoint_diagrams/'; 
            }
            else{
                $x='';
            }

            $this->_createSitemap($m,'category-'.$store);       
            foreach ($result as $key) {
                    
                    if(!empty($key['url_path']))
                    {
                        $xml = $this->_getSitemapRow(
                            $this->getSiteLink().$x.$key['url_path'].'.html',
                            $key['updated_at']
                        );
                        $this->_writeSitemapRow($xml);
                    }
                }
            $this->_finalizeSitemap();     
    }

    public function generateCmssitemap()
    {
        $sql = "SELECT identifier,update_time FROM `cms_page` WHERE is_active = 1";
        $connection = $this->_resourceConnection->getConnection();
        $result = $connection->fetchAll($sql);
            $m = '';

            $this->_createSitemap($m,'cms');       
            foreach ($result as $key) {
                    
                    $xml = $this->_getSitemapRow(
                            $this->getSiteLink().$key['identifier'],
                            $key['update_time']
                        );
                        $this->_writeSitemapRow($xml);
                }
            $this->_finalizeSitemap();     
    }

    public function generatePdpMultilangsitemap()
    {
        $sql = "SELECT value from catalog_product_entity_varchar WHERE entity_id in (SELECT product_id from multilanguage_data GROUP BY product_id) AND attribute_id = 88 AND store_id = 1";
        $connection = $this->_resourceConnection->getConnection();
        $result = $connection->fetchAll($sql);
        
        $lang = array('Spanish','German','French','Portuguese');
            $m = '';

            $this->_createSitemap($m,'pdp-multilanguage');       
            foreach ($result as $key) {
                    
                    foreach ($lang as $para) {
                
                        $xml = $this->_getSitemapRow(
                            $this->getSiteLink().$key['value'].'.html?lang='.$para,
                            date("Y-m-d h:i:s")
                        );
                        $this->_writeSitemapRow($xml);

                    }
                }
            $this->_finalizeSitemap();     
    }

    public function generateTagsitemap()
    {
        $sql = "SELECT `tag`.`name` FROM `tag_summary` INNER JOIN `tag` ON tag.tag_id = tag_summary.tag_id AND tag.status = 1 WHERE (tag_summary.store_id = 1) AND (tag_summary.products > 0) AND (tag_summary.tag_id > 72445) AND ( LOWER(tag.name) LIKE LOWER('%')) ORDER BY LOWER(TRIM(tag.name)) ASC";
        $connection = $this->_resourceConnection->getConnection();
        $result = $connection->fetchAll($sql);
        $count = count($result);
        
        if($count >= 50000)
        {
            $startLimit = 0;
            $endLimit = 50000;
            $loopIterations = ceil($count/$endLimit);
            
            for($m=1;$m<=$loopIterations;$m++){
            
                $sql1 = "SELECT `tag`.`name` FROM `tag_summary` INNER JOIN `tag` ON tag.tag_id = tag_summary.tag_id AND tag.status = 1 WHERE (tag_summary.store_id = 1) AND (tag_summary.products > 0) AND (tag_summary.tag_id > 72445) AND ( LOWER(tag.name) LIKE LOWER('%')) ORDER BY LOWER(TRIM(tag.name)) ASC LIMIT ".$startLimit.",".$endLimit;
                $connection = $this->_resourceConnection->getConnection();
                $result1 = $connection->fetchAll($sql1);

                $this->_createSitemap($m,'tag-');  

                foreach ($result1 as $key) {
                    
                    $tag_name = $key['name'];
                    $tag = str_replace(' ', '-', strtolower($tag_name));
                    $tagurl = 'tag/'.$tag."-powerpoint-templates-ppt-slides-images-graphics-and-themes";

                    $xml = $this->_getSitemapRow(
                        $this->getSiteLink().$tagurl,
                        date("Y-m-d h:i:s")
                    );
                    $this->_writeSitemapRow($xml);
                }
                $this->_finalizeSitemap();   
                $startLimit += $endLimit;  
            }

            $this->generateMainFile('tag');
            for($i=1;$i<=$loopIterations;$i++){
                $xml = $this->_getFilelist($i,'tag');
                $this->_writeSitemapRow($xml); 
            }
            $this->_finalizemain();
        } 
        else{
            
            $m = '';

            $this->_createSitemap($m,'tag');       
            foreach ($result as $key) {
                    
                    $tag_name = $key['name'];
                    $tag = str_replace(' ', '-', strtolower($tag_name));
                    $tagurl = 'tag/'.$tag."-powerpoint-templates-ppt-slides-images-graphics-and-themes";

                    $xml = $this->_getSitemapRow(
                        $this->getSiteLink().$tagurl,
                        date("Y-m-d h:i:s")
                    );
                    $this->_writeSitemapRow($xml);
                }
                $this->_finalizeSitemap();     
        }
    }

    public function generateImagesitemap()
    {
    
        $sql = "SELECT value FROM `catalog_product_entity_media_gallery` cpe LEFT JOIN catalog_product_entity_media_gallery_value_to_entity cpem ON cpem.value_id = cpe.value_id GROUP BY cpe.value_id ORDER BY cpem.entity_id DESC";
        $connection = $this->_resourceConnection->getConnection();
        $result = $connection->fetchAll($sql);
        $count = count($result);
        
        if($count >= 50000)
        {
            $startLimit = 0;
            $endLimit = 50000;
            $loopIterations = ceil($count/$endLimit);
            
            for($m=1;$m<=$loopIterations;$m++){
            
                $sql1 = "SELECT value FROM `catalog_product_entity_media_gallery` cpe LEFT JOIN catalog_product_entity_media_gallery_value_to_entity cpem ON cpem.value_id = cpe.value_id GROUP BY cpe.value_id ORDER BY cpem.entity_id DESC LIMIT ".$startLimit.",".$endLimit;
                $connection = $this->_resourceConnection->getConnection();
                $result1 = $connection->fetchAll($sql1);

                $this->_createSitemap($m,'image-');  

                foreach ($result1 as $key) {
                    
                    $imgurl = 'media/catalog/product/cache/1280x720'.$key['value'];

                    $xml = $this->_getSitemapRow(
                        $this->getSiteLink().$imgurl,
                        date("Y-m-d h:i:s")
                    );
                    $this->_writeSitemapRow($xml);
                }
                $this->_finalizeSitemap();   
                $startLimit += $endLimit;  
            }

            $this->generateMainFile('image');
            for($i=1;$i<=$loopIterations;$i++){
                $xml = $this->_getFilelist($i,'image');
                $this->_writeSitemapRow($xml); 
            }
            $this->_finalizemain();
        } 
        else{
            
            $m = '';

            $this->_createSitemap($m,'image');       
            foreach ($result as $key) {
                    
                    $imgurl = 'media/catalog/product/cache/1280x720'.$key['value'];

                    $xml = $this->_getSitemapRow(
                        $this->getSiteLink().$imgurl,
                        date("Y-m-d h:i:s")
                    );
                    $this->_writeSitemapRow($xml);
                }
                $this->_finalizeSitemap();     
        }
    }

    public function rssTagContent()
    {
        $rssTagContent = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
        return $rssTagContent;
    }

    public function mainContent()
    {
        $mainContent = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
        return $mainContent;
    }

    public function _getSitemapRow($url,$postDate)
    {
        $timestamp = max(strtotime($postDate), strtotime('0000-01-01 00:00:00'));
        $date = date('c', $timestamp);

        $row = '<loc>' . $this->_escaper->escapeUrl($url) . '</loc>';
        //$row .= '<lastmod>'. $date .'</lastmod>';

        return '<url>' . $row . '</url>';
    }

    public function _getFilelist($i,$name)
    {
        $timestamp = max(strtotime(date("r")), strtotime('0000-01-01 00:00:00'));
        $date = date('c', $timestamp);

        $row = '<loc>' . $this->getSiteLink().'sitemap/sitemap-slideteam/'.'sitemap-'.$name.'-'.$i.'.xml'. '</loc>';
        $row .= '<lastmod>' .$date. '</lastmod>';

        return '<sitemap>' . $row . '</sitemap>';
    }

    public function generateMainFile($file)
    {
        $path = self::PATH . '/' . self::FILE_NAME.'-'.$file.'.xml';
        $this->_stream = $this->_directory->openFile($path);

        $fileHeader = $this->_mainfile[self::OPEN_TAG_KEY].PHP_EOL;
        $this->_stream->write($fileHeader);
           
    }
    public function _createSitemap($m,$name){
        $path = self::PATH . '/' . self::FILE_NAME.'-'.$name.$m.'.xml';
        $this->_stream = $this->_directory->openFile($path);

        $fileHeader = $this->_tags[self::OPEN_TAG_KEY].PHP_EOL;
        $this->_stream->write($fileHeader);
    }

   /**
     * Write Feed row
     *
     * @param string $row
     * @return void
     */
    protected function _writeSitemapRow($row)
    {
        $this->_getStream()->write($row . PHP_EOL);
    }

    protected function _finalizeSitemap()
    {
        if ($this->_stream) {
            $this->_stream->write($this->_tags[self::CLOSE_TAG_KEY]);
            $this->_stream->close();
        }
    }

    protected function _finalizemain()
    {
        if ($this->_stream) {
            $this->_stream->write($this->_mainfile[self::CLOSE_TAG_KEY]);
            $this->_stream->close();
        }
    }

    private function escapeXmlText(string $text): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $fragment = $doc->createDocumentFragment();
        $fragment->appendChild($doc->createTextNode($text));
        return $doc->saveXML($fragment);
    }

    public function getSiteLink()
    {
        $baseurl = $this->themeHelper->getStoreBaseUrl();
        $url = str_replace($baseurl,'https://www.slideteam.net/',$baseurl);
        return $url;
    }

    public function getSitemapLink()
    {
        return $this->getBlogLink().'/sitemap';
    }
}
