<?php
namespace Vgroup65\Testimonial\Controller\Adminhtml\Testimonial;
 
use Vgroup65\Testimonial\Controller\Adminhtml\Testimonial;
class MassDelete extends Testimonial
{
   /**
    * @return void
    */
   public function execute()
   {
      // Get IDs of the selected testimonial
      $testimonialIds = $this->getRequest()->getParam('testimonial');

        foreach($testimonialIds as $testimonialId){
            try {
               /** @var $newsModel \Vgroup65\News\Model\Newslist */
                $testimonialModel = $this->_testimonialFactory->create();
                
                $testimonialModel->load($testimonialId);
                
                $testimonialData = $testimonialModel->getData();
                $helper = $this->helper;
                if(!empty($testimonialData['image'])):
                    $currentImage = $testimonialData['image'];
                    if (!filter_var($currentImage, FILTER_VALIDATE_URL)):
                         if (file_exists($helper->getBaseDir().$currentImage)):
                            unlink($helper->getBaseDir().$currentImage); 
                         endif; 
                    endif;                
                endif;
                
                if(!empty($testimonialData['resize_image'])):
                    $currentResizeImage = $testimonialData['resize_image'];
                    if (!filter_var($currentResizeImage, FILTER_VALIDATE_URL)):
                         if (file_exists($helper->getBaseDir().$currentResizeImage)):
                            unlink($helper->getBaseDir().$currentResizeImage); 
                         endif; 
                    endif;                
                endif;
                
                $testimonialModel->delete();
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
 
        if (count($testimonialIds)) {
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) were deleted.', count($testimonialIds))
            );
        }
 
        $this->_redirect('*/*/index');
   }
}