<?php
/**
 * Copyright © 2017 BORN . All rights reserved.
 */
namespace Tatva\EduTech\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

class SerializedContent implements ObserverInterface
{
    /**
     * @var  \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getDataObject();
        $post = $this->request->getPost();
        $post = $post['product']; 

        // Complete Curriculum 
        if(array_key_exists('complete_curriculum_row', $post) ){
        $completeCurriculum = $this->getDynamicData($post['complete_curriculum_row'] , 'complete_curriculum','complete_curriculum_content');
        $product->setCompleteCurriculum($completeCurriculum);
        } else {
            $product->setCompleteCurriculum('');
        }
        // Sample Instructor Notes
        if(array_key_exists('sample_instructor_notes_rows', $post) ){
        $sampleInstructore = $this->getDynamicData($post['sample_instructor_notes_rows'] , 'sample_instructor_notes','sample_instructor_content');
        $product->setSampleInstructorNotes($sampleInstructore);
        }else {
            $product->setSampleInstructorNotes('');
        }
        // FAQs
        if(array_key_exists('product_faq_row', $post) ){
        $faq = $this->getDynamicData($post['product_faq_row'] , 'product_faq','product_faq_content');
        $product->setProductFaq($faq);
        }else {
            $product->setProductFaq('');
        }
    }

    public function getDynamicData($rowData , $mainContentName , $contentName ){
        $mainContent = '';
        $content = '';
        foreach($rowData as $data){
            $mainContent .= $data[$mainContentName].'|';
            $content .= $data[$contentName].'|';
        }
        $mainContent = rtrim($mainContent, "|");
        $content = rtrim($content, "|");
        $mainContent .= "#".$content;
        if($mainContent == "#") {
            return '';
        }
        return $mainContent;
    }
}