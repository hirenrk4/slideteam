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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Tatva\Categoryid\Model;

/**
 * Catalog category
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Category extends \Magento\Catalog\Model\AbstractModel {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\TreeFactory
     */
    protected $catalogResourceModelCategoryTreeFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $catalogCategoryFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $catalogResourceModelCategoryTreeFactory,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Catalog\Helper\Output $helper
        ) {
        $this->registry = $registry;
        $this->catalogResourceModelCategoryTreeFactory = $catalogResourceModelCategoryTreeFactory;
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->helper = $helper;
    }
    public function get_categories()
    {
        $current_category = $this->registry->registry('current_category');
        $current_category_id = $this->helper->categoryAttribute($current_category, $current_category->getId(), 'entity_id');
        $current_category_parent_id = $this->helper->categoryAttribute($current_category, $current_category->getParentId(), 'parent_id');

        $tree = $this->catalogResourceModelCategoryTreeFactory->create();
        $tree->load();
        $ids = $tree->getCollection()->getAllIds();
        $arr = array();
        if ($ids)
        {
            foreach ($ids as $id)
            {
                $cat = $this->catalogCategoryFactory->create()->load($id);
                if($cat['entity_id'] != $current_category_id && $cat['entity_id'] != $current_category_parent_id)
                {
                 if($cat->getLevel() == 2)
                    array_push($arr, $cat);
            }
        }
    }
    return $arr;
}
}
