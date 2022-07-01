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
 * Catalog product model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Product extends \Magento\Catalog\Model\AbstractModel
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $catalogCategoryFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory
    ) {
        $this->registry = $registry;
        $this->catalogCategoryFactory = $catalogCategoryFactory;
    }
    /**
     * Retrieve product categories
    */   

    public function getCategories()
    {
        $category = array();

        $_product = $this->registry->registry('product');
        $categoryIds = $_product->getCategoryIds();

        foreach($categoryIds as $_category_id)
        {
            $cat = $this->catalogCategoryFactory->create()->load($_category_id);
            array_push($category, $cat);
        }

        return $category;
    }

}
