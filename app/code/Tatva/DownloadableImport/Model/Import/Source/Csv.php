<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\DownloadableImport\Model\Import\Source;

/**
 * CSV import adapter
 */
class Csv extends \Magento\ImportExport\Model\Import\AbstractSource
{

    const COL_ATTR_SET="attribute_set_code";
    const COL_TITLE_METATITLE="meta_title";
    const COL_TITLE_METADESCRIPTION="meta_description";
    const COL_TITLE_SENTENCE1="sentence1";
    const COL_TITLE_SENTENCE2="sentence2";
    const VAL_COL_TYPE="downloadable";
    const VAL_ATTR_SET="Migration_Default";

     /**
     * @var \Magento\Framework\Filesystem\File\Write
     */
    protected $_file;

    /**
     * Delimiter.
     *
     * @var string
     */
    protected $_delimiter = ',';

    /**
     * @var string
     */
    protected $_enclosure = '';




    public function __construct(
        $file,
        \Magento\Framework\Filesystem\Directory\Read $directory,
        $delimiter = ',',
        $enclosure = '"'
        
    ) {
        register_shutdown_function([$this, 'destruct']);
        try {
            $this->_file = $directory->openFile($directory->getRelativePath($file), 'r');
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            throw new \LogicException("Unable to open file: '{$file}'");
        }
        if ($delimiter) {
            $this->_delimiter = $delimiter;
        }
        $this->_enclosure = $enclosure;
        $this->_counter = 0;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $coresession = $objectManager->create('\Magento\Framework\Session\SessionManagerInterface');
        $metaDataImport=$objectManager->create('\Tatva\DownloadableImport\Model\MetaDataImport');
        $coresession->setCounter(0);
        $this->_coresession = $coresession;
        $this->_metaDataImport = $metaDataImport;
        parent::__construct($this->_getNextRow());
    }

    protected function _getNextRow()
    {
        $parsed = $this->_file->readCsv(0, $this->_delimiter, $this->_enclosure);
        $counter=$this->_coresession->getCounter();
          if (is_array($parsed) && count($parsed) != $this->_colQty) {
            
            if(!in_array(\Magento\CatalogImportExport\Model\Import\Product::COL_TYPE, $parsed))
            {
                if($counter == 0)
                {
                    $parsed[]= \Magento\CatalogImportExport\Model\Import\Product::COL_TYPE;
                    $parsed[]= \Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET;
                    $parsed[]= self::COL_ATTR_SET; 
                    $parsed[]= self::COL_TITLE_METATITLE;         
                    $parsed[]= self::COL_TITLE_METADESCRIPTION;         
                    $parsed[]= self::COL_TITLE_SENTENCE1;         
                    $parsed[]= self::COL_TITLE_SENTENCE2;      
                }
                elseif($counter > 0){ 
                    $parsed[7] = ($parsed[7] =="" || $parsed[7] ==NULL)?str_replace("_","-",$parsed[0]):$parsed[7];
                    $parsed[0] = substr($parsed[0],0,64); 
                     $parsed[]= self::VAL_COL_TYPE;
                    $parsed[]= self::VAL_ATTR_SET;
                    $parsed[]= self::VAL_ATTR_SET;  
                    $metatile=$this->_metaDataImport->setProductData(1,$parsed[1],$parsed[4]);                                             
                    $metadescription=$this->_metaDataImport->setProductData(2,$parsed[1],$parsed[4]);                                             
                    $sentences=$this->_metaDataImport->setProductData(3,$parsed[1],$parsed[4]);  
                     
                    if($parsed[0] != "sku"){
                        if($this->_metaDataImport->productModel->getIdBySku($parsed[0])){
                            $sku_product = $this->_metaDataImport->productModel->load($parsed[0],'sku');
                            $sentences[self::COL_TITLE_SENTENCE1] = $sku_product->getSentence1();
                            $sentences[self::COL_TITLE_SENTENCE2] = $sku_product->getSentence2();
                        } 
                    }

                    $parsed[]= $metatile[self::COL_TITLE_METATITLE];                                               
                    $parsed[]= $metadescription[self::COL_TITLE_METADESCRIPTION];                                               
                    $parsed[]= $sentences[self::COL_TITLE_SENTENCE1];                                               
                    $parsed[]= $sentences[self::COL_TITLE_SENTENCE2];                                               
                }
            }  

        $this->_coresession->setCounter(++$counter);
            foreach ($parsed as $element) {
                if (strpos($element, "'") !== false) {
                    $this->_foundWrongQuoteFlag = true;
                    break;
                }
            }
        } 
        else {
            $this->_foundWrongQuoteFlag = false;
        }
  
        return is_array($parsed) ? $parsed : [];
    }
    public function destruct()
    {
        if (is_object($this->_file)) {
            $this->_file->close();
        }
    }
     public function rewind()
    {
        $this->_file->seek(0);
        $this->_getNextRow();
        // skip first line with the header
        parent::rewind();
    }


}
