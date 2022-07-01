<?php

namespace Tatva\SLIFeed\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class GeneratorHelper extends \SLI\Feed\Helper\GeneratorHelper
{
    protected $feedUpdateFileTemplate; 
    protected $feedDeleteFileTemplate; 


    public function __construct(\Magento\Framework\App\Helper\Context $context, 
            \Magento\Framework\App\ResourceConnection $connection,
            \Magento\Framework\Filesystem $filesystem, 
            \Magento\Framework\Module\ResourceInterface $moduleResource,
            array $xmlPathMap,
            \Magento\Framework\Serialize\SerializerInterface $serializeInterface, $feedFileTemplate,
            $feedUpdateFileTemplate, $feedDeleteFileTemplate) {
        parent::__construct($context, $connection, $filesystem, $moduleResource, $xmlPathMap, $serializeInterface, $feedFileTemplate);
        $this->feedUpdateFileTemplate = $feedUpdateFileTemplate;
        $this->feedDeleteFileTemplate = $feedDeleteFileTemplate;
    }
    
    public function getFeedFileTemplate()
    {
        $template = $this->filesystem
                ->getDirectoryRead(DirectoryList::PUB)
                ->getAbsolutePath().'Scripts/' . $this->feedFileTemplate;
        $path = dirname($template);
        if (!is_dir($path) && !mkdir($path, 0777, true)) {
            throw new \Exception('Feed file path could not be created.');
        }

        return $template;
    }
    
    public function getFeedUpdateFileTemplate()
    {
        $template = $this->filesystem
                ->getDirectoryRead(DirectoryList::PUB)
                ->getAbsolutePath().'Scripts/' . $this->feedUpdateFileTemplate;
        $path = dirname($template);
        if (!is_dir($path) && !mkdir($path, 0777, true)) {
            throw new \Exception('Feed file path could not be created.');
        }

        return $template;
    }

    public function getFeedDeleteFileTemplate()
    {
        $template = $this->filesystem
                ->getDirectoryRead(DirectoryList::PUB)
                ->getAbsolutePath().'Scripts/' . $this->feedDeleteFileTemplate;
        $path = dirname($template);
        if (!is_dir($path) && !mkdir($path, 0777, true)) {
            throw new \Exception('Feed file path could not be created.');
        }

        return $template;
    }
    
}