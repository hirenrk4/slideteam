<?php
namespace Vgroup65\Testimonial\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
class Data extends \Magento\Framework\App\Helper\AbstractHelper{

    /**
     * Media path to extension images
     *
     * @var string
     */
    const MEDIA_PATH    = 'testimonial';

    /**
     * Maximum size for image in bytes
     * Default value is 1M
     *
     * @var int
     */
    const MAX_FILE_SIZE = 1048576;

    /**
     * Manimum image height in pixels
     *
     * @var int
     */
    const MIN_HEIGHT = 50;

    /**
     * Maximum image height in pixels
     *
     * @var int
     */
    const MAX_HEIGHT = 800;

    /**
     * Manimum image width in pixels
     *
     * @var int
     */
    const MIN_WIDTH = 50;

    /**
     * Maximum image width in pixels
     *
     * @var int
     */
    const MAX_WIDTH = 1024;

    /**
     * Array of image size limitation
     *
     * @var array
     */
    protected $_imageSize   = array(
        'minheight'     => self::MIN_HEIGHT,
        'minwidth'      => self::MIN_WIDTH,
        'maxheight'     => self::MAX_HEIGHT,
        'maxwidth'      => self::MAX_WIDTH,
    );
    
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\HTTP\Adapter\FileTransferFactory
     */
    protected $httpFactory;
    
    /**
     * File Uploader factory
     *
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;
    
    /**
     * File Uploader factory
     *
     * @var \Magento\Framework\Io\File
     */
    protected $_ioFile;
    
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    protected $_systemStore;
    
    protected $_storeRepository;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
//        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Store\Model\StoreRepository $storeRepository
    ) {
//        $this->_scopeConfig = $scopeConfig;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->httpFactory = $httpFactory;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_ioFile = $ioFile;
        $this->_storeManager = $storeManager;
        $this->_imageFactory = $imageFactory;
        $this->_systemStore = $systemStore;
        $this->_storeRepository = $storeRepository;
        parent::__construct($context);
    }
    
    /**
     * Remove <Module> item image by image filename
     *
     * @param string $imageFile
     * @return bool
     */
    public function removeImage($imageUrl)
    {
        $urlData = @explode('/' , $imageUrl);
        $index = count($urlData) - 1;
        $imageName = $urlData[$index];
        
        $path = "/".$imageName[0]."/".$imageName[1]."/".$imageName;
        
        $completePath = $this->getBaseDir(). $path;
        
        if($completePath):
            unlink($completePath);
            return true;
            else:
            return false;
        endif;
    }
    
    /**
     * Return the base media directory for <Module> Item images
     *
     * @return string
     */
    public function getBaseDir()
    {
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(self::MEDIA_PATH);
        return $path;
    }
    
    /**
     * Return the Base URL for <Module> Item images
     *
     * @return string
     */
    public function getBaseUrl()
    { 
        return $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . self::MEDIA_PATH;
    }
    
    public function getFileUploaderFactory(){
        return $this->_fileUploaderFactory;
    }
    
    public function getStoreManager(){
        return $this->_storeManager;
    }
            
    public function getSystemStore(){
        return $this->_systemStore;
    }
    
    
    //insted of getAllStoreIds please use getCurrentStores
//    public function getCurrentStores(){
//        $stores = $this->_storeRepository->getList();
//        $storeList = array();
//        foreach ($stores as $store) {
//            if($store["store_id"] == 0):
//                continue;
//            endif;
//            $storeList['store_id'] = $store["store_id"];
//            $storeList['name'] = $store["name"];
//            $currentStoreList[] = $storeList;
//        }
//        return $currentStoreList;
//    }
}
