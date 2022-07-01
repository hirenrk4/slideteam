<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\DownloadableImport\Model\Import\Product;

/**
 * Class CategoryProcessor
 *
 * @api
 * @since 100.0.2
 */
class CategoryProcessor extends \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor
{
    /**
     * Delimiter in category path.
     */
    const DELIMITER_CATEGORY = '/';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryColFactory;

    /**
     * Categories text-path to ID hash.
     *
     * @var array
     */
    protected $categories = [];

    /**
     * Categories id to object cache.
     *
     * @var array
     */
    protected $categoriesCache = [];

    /**
     * Instance of catalog category factory.
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * Failed categories during creation
     *
     * @var array
     * @since 100.1.0
     */
    protected $failedCategories = [];

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
	protected function createCategory($name, $parentId)
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->categoryFactory->create();
        if (!($parentCategory = $this->getCategoryById($parentId))) {
            $parentCategory = $this->categoryFactory->create()->load($parentId);
        }

        $title= __("%1 - SlideTeam",ucfirst($name));
        $metakeywords = __("%1, %1 PPT Slides, %1 PowerPoint Templates",ucfirst($name));
        $metadescription = __("Communicate clearly with predesigned %1. Save time by presenting well-researched templates, designed by our experts.",ucfirst($name));

        $category->setPath($parentCategory->getPath());
        $category->setParentId($parentId);
        $category->setName($this->unquoteDelimiter($name));
        $category->setIsActive(true);
        $category->setIncludeInMenu(true);
        $category->setAttributeSetId($category->getDefaultAttributeSetId());
        $category->setMetaTitle($title);
        $category->setMetaKeywords($metakeywords);
        $category->setMetaDescription($metadescription);
        //try {
            $category->save();
            $this->categoriesCache[$category->getId()] = $category;
        /*} catch (\Exception $e) {
            $this->addFailedCategory($category, $e);
        }*/

        return $category->getId();
    }

    private function addFailedCategory($category, $exception)
    {
        $this->failedCategories[] =
            [
                'category' => $category,
                'exception' => $exception,
            ];
        return $this;
    }

    public function upsertCategories($categoriesString, $categoriesSeparator)
    {
        $categoriesIds = [];
        $categories = explode($categoriesSeparator, $categoriesString);

        foreach ($categories as $category) {
            try {
                $categoriesIds[] = $this->upsertCustomCategory($category);
            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                $this->addFailedCategory($category, $e);
            }
        }

        return $categoriesIds;
    }


    protected function upsertCustomCategory($categoryPath)
    {
        /** @var string $index */
        $index = $this->standardizeCustomString($categoryPath);

        if (isset($this->categories[$index])) {
            
            return $this->categories[$index];   
        }
        else
        {
            return;
        }
        
    }
    

    protected function standardizeCustomString($string)
    {
        return mb_strtolower($string);
    }
}