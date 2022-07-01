<?php
/**
 * We have to copy some more part of the file as the method is private and called in this class' public methods
 *
 * 
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tatva\Catalog\Model\View\Asset;

use Magento\Catalog\Model\Config\CatalogMediaConfig;
use Magento\Catalog\Model\Product\Media\ConfigInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * A locally available image file asset that can be referred with a file path
 *
 * This class is a value object with lazy loading of some of its data (content, physical file path)
 */
class Image extends \Magento\Catalog\Model\View\Asset\Image
{
    /**
     * Image type of image (thumbnail,small_image,image,swatch_image,swatch_thumb)
     *
     * @var string
     */
    private $sourceContentType;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var string
     */
    private $contentType = 'image';

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * Misc image params depend on size, transparency, quality, watermark etc.
     *
     * @var array
     */
    private $miscParams;

    /**
     * @var ConfigInterface
     */
    private $mediaConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;
    private $_resourceConnection;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $mediaFormatUrl;

    /**
     * Image constructor.
     *
     * @param ConfigInterface $mediaConfig
     * @param ContextInterface $context
     * @param EncryptorInterface $encryptor
     * @param string $filePath
     * @param array $miscParams
     */
    public function __construct(
        ConfigInterface $mediaConfig,
        ContextInterface $context,
        EncryptorInterface $encryptor,
        $filePath,
        \Magento\Framework\Registry $registry,       
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,        
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        array $miscParams,
        ImageHelper $imageHelper = null,
        CatalogMediaConfig $catalogMediaConfig = null,
        StoreManagerInterface $storeManager = null
    ) {
        if (isset($miscParams['image_type'])) {
            $this->sourceContentType = $miscParams['image_type'];
            unset($miscParams['image_type']);
        } else {
            $this->sourceContentType = $this->contentType;
        }
        parent::__construct($mediaConfig,$context,$encryptor,$filePath,$miscParams); 
        $this->mediaConfig = $mediaConfig;
        $this->context = $context;
        $this->filePath = $filePath;        
        $this->miscParams = $miscParams;
        $this->encryptor = $encryptor;
        $this->_registry = $registry;        
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->_resourceConnection = $resourceConnection;
        $this->_coreSession = $coreSession;
        $this->imageHelper = $imageHelper ?: ObjectManager::getInstance()->get(ImageHelper::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);

        $catalogMediaConfig =  $catalogMediaConfig ?: ObjectManager::getInstance()->get(CatalogMediaConfig::class);
        $this->mediaFormatUrl = $catalogMediaConfig->getMediaUrlFormat();
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        $file = $this->getFilePath();
        $pos = strrpos($file, '.');
        $pathEnding = substr($file, $pos + 1);
        if ($pathEnding == "gif" || $pathEnding == "GIF") {
            $path = substr($file, strpos($file, "/") + 1);
            return  str_replace("pub/","",$this->context->getBaseUrl() . DIRECTORY_SEPARATOR . $path);
        } else {
            switch ($this->mediaFormatUrl) {
                case CatalogMediaConfig::IMAGE_OPTIMIZATION_PARAMETERS:
                    return $this->getUrlWithTransformationParameters();
                case CatalogMediaConfig::HASH:
                    return $this->context->getBaseUrl() . DIRECTORY_SEPARATOR . $this->getImageInfo();
                default:
                    throw new LocalizedException(
                        __("The specified Catalog media URL format '$this->mediaFormatUrl' is not supported.")
                    );
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->context->getPath() . DIRECTORY_SEPARATOR . $this->getImageInfo();
        //return $this->getAbsolutePath($this->context->getPath());
    }

    /**
     * Generate path from image info
     *
     * @return string
     */
    private function getImageInfo()
    {
        $path = $this->getModule()
            . DIRECTORY_SEPARATOR . $this->getMiscPath()
            . DIRECTORY_SEPARATOR . $this->getFilePath();
        return preg_replace('|\Q'. DIRECTORY_SEPARATOR . '\E+|', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @todo need to skip images which are not needed for case
     * Retrieve part of path based on misc params
     *
     * @return string
     */
    private function getMiscPath()
    {
        $moduleName = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        $action     = $this->request->getActionName();
        $route      = $this->request->getRouteName();

        $Resumefound = 0;
        if($moduleName == "resume-template" && $controller == "index" && $action == "index")
        {
            $Resumefound = 1;
        }
        elseif($moduleName == "brochure-templates" && $controller == "index" && $action == "index")
        {
            $Resumefound = 1;
        }
        else
        {
            $product = $this->_registry->registry('current_product');
            $category = $this->_registry->registry('current_category');            
            
            if(is_object($product))
            {
                $proData = $product->getData('entity_id');
                
                if(!empty($proData))
                {            
                    $categoryIds = $product->getCategoryIds();

                    $resumecategoryId = $this->scopeConfig->getValue('resume/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                    $brochurecategoryId = $this->scopeConfig->getValue('brochure/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                    if(in_array($resumecategoryId,$categoryIds) || in_array($brochurecategoryId,$categoryIds))
                    {                    
                        $Resumefound = 1;
                    }
                    else
                    {
                        $customCategories = $this->scopeConfig->getValue("resume/general/custom_category_manager",\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $customCategories = explode(',', $customCategories);

                        foreach ($customCategories as $categoryid) {

                            $id = "custom_cat_".$categoryid;
                            $customecatIds = $this->_registry->registry($id);
                            if(empty($customecatIds))
                            {
                                $connection = $this->_resourceConnection->getConnection();                            
                                $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$categoryid;
                                $results = $connection->fetchAll($sql);
                                if(!empty($results))
                                {
                                    $customecatIds = explode(',',$results[0]['category_list']);
                                    $this->_registry->register($id,$customecatIds);
                                    if(array_intersect($categoryIds,$customecatIds))
                                    {
                                        $Resumefound = 1;
                                    } 
                                }
                            }
                            else
                            {
                                if(array_intersect($categoryIds,$customecatIds))
                                {
                                    $Resumefound = 1;
                                }   
                            }
                        }
                    }   
                }
            }  
           
            if(is_object($category))
            {
                $catid = $category->getId();

                $customCategories = $this->scopeConfig->getValue("resume/general/custom_category_manager",\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $customCategories = explode(',', $customCategories);
                foreach ($customCategories as $categoryid) {

                    $id = "custom_cat_".$categoryid;
                    $customecatIds = $this->_registry->registry($id);
                    if(empty($customecatIds))
                    {
                        $connection = $this->_resourceConnection->getConnection();                            
                        $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$categoryid;
                        $results = $connection->fetchAll($sql); 
                        if(!empty($results))
                        {
                            $customecatIds = explode(',',$results[0]['category_list']); 
                            $this->_registry->register($id,$customecatIds); 
                            if(in_array($catid,$customecatIds))
                            {
                                $Resumefound = 1;
                            }
                        }
                    }
                    else
                    {
                        if(in_array($catid,$customecatIds))
                        {
                            $Resumefound = 1;    
                        }
                    }
                }        
            }
        }   

        $imgDir = "default_misc_dir";
        $miscParams = $this->miscParams;
        $imgWidth = isset($miscParams['image_width']) ? $miscParams['image_width'] : null;
        $imgHeight = isset($miscParams['image_height']) ? $miscParams['image_height'] : null;

        $isonepage = $this->_coreSession->getOnePageFind();
        if(!empty($isonepage))
        {
            $Resumefound = 1;
        }

        if(!empty($imgWidth) && !empty($imgHeight)){
            if($Resumefound == 1)
            {
                switch ($imgWidth) {
                    case '260':
                        $imgDir = "260x195";
                        break;

                    case '330':
                        $imgDir = "330x186";
                        break;

                    case '560':
                        $imgDir = "560x315";
                        break;
                    
                    case '1280':
                        $imgDir = "1280x720";
                        break;
                    case '298':
                        $imgDir = "298x427";
                        break;  
                    case '80':
                        $imgDir = "80x115";
                        break; 
                    // Just for testing how many type of sizes are created  
                    default:
                        $imgDir = "default_misc_dir";                    
                        break;
                }                    
            }
            else
            {
                switch ($imgWidth) {
                    case '260':
                        $imgDir = "260x195";
                        break;

                    case '330':
                        $imgDir = "330x186";
                        break;

                    case '560':
                        $imgDir = "560x315";
                        break;
                    
                    case '1280':
                        $imgDir = "1280x720";
                        break;
                    // Just for testing how many type of sizes are created  
                    default:
                        $imgDir = "default_misc_dir";                    
                        break;
                }
            }
        }
        elseif (empty($imgWidth) && empty($imgHeight)) {
            $imgDir = "1280x720";
        }

        return $imgDir;
    }
    
}
