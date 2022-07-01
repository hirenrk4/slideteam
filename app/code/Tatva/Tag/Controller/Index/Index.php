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
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Tag
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Tatva\Tag\Controller\Index;

/**
 * Customer tags controller
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Tatva\Tag\Model\Tag\RelationFactory
     */
    protected $tagTagRelationFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Tatva\Tag\Model\TagFactory
     */
    protected $tagTagFactory;

    /**
     * @var \Tatva\Tag\Model\Session
     */
    protected $tagSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Tatva\Tag\Model\Tag\RelationFactory $tagTagRelationFactory,
        \Magento\Framework\Registry $registry,
        \Tatva\Tag\Model\TagFactory $tagTagFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Escaper $_escaper,
        \Magento\Framework\View\Result\PageFactory $resultPage
    ) {
        $this->customerSession = $customerSession;
        $this->tagTagRelationFactory = $tagTagRelationFactory;
        $this->registry = $registry;
        $this->tagTagFactory = $tagTagFactory;
        $this->request = $request;
        $this->_escaper=$_escaper;
        $this->_resultPageFactory=$resultPage;
        parent::__construct(
            $context
        );
    }


    public function execute()
    {
        $this->_view->loadLayout();
        $tag_id=$this->request->getParam('tagId');
          $tag=$this->tagTagFactory->create()->load($tag_id);
        if($tag->getName()) {            
            $title= __("%1 | SlideTeam.net",ucfirst($tag->getName()));
            //$metadescription = __("Leave an everlasting impression on your audience with %1 PowerPoint templates PPT slides, that could fit into all kinds of presentations with ease.",ucfirst($tag->getName()));
            $metakeywords = __("%1, %1 PPT Slides, %1 PowerPoint Templates",ucfirst($tag->getName()));
        } else {
            $title="";
            $metakeywords = "";
            // $metadescription = "";
        }
        $resultPage = $this->_resultPageFactory->create();        

        $resultPage->getConfig()->getTitle()->set($title);
        //$resultPage->getConfig()->setDescription($metadescription);
        $resultPage->getConfig()->setKeywords($metakeywords);
        $this->_view->renderLayout();
    }

   
}
