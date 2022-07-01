<?php
namespace Vgroup65\Testimonial\Controller\Adminhtml\Testimonial;
 
use Vgroup65\Testimonial\Controller\Adminhtml\Testimonial;
 
class Delete extends Testimonial
{
   /**
    * @return void
    */
   public function execute()
   {
      $testimonialId = (int) $this->getRequest()->getParam('id');
      $helper = $this->helper;
      
      if (isset($testimonialId)) {
         /** @var $newsModel \Mageworld\SimpleNews\Model\News */
         $testimonialModel = $this->_testimonialFactory->create();
         $testimonialModel->load($testimonialId);
 
         // Check this news exists or not
         if (!$testimonialModel->getId()) {
            $this->messageManager->addError(__('This testimonial no longer exists.'));
         } else {
               try {
                $testimonialData = $testimonialModel->getData();

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
                   
                  // Delete news
                  $testimonialModel->delete();
                  $this->messageManager->addSuccess(__('The testimonial has been deleted.'));
 
                  // Redirect to grid page
                  $this->_redirect('*/*/');
                  return;
               } catch (\Exception $e) {
                   $this->messageManager->addError($e->getMessage());
                   $this->_redirect('*/*/edit', ['id' => $testimonialModel->getId()]);
               }
            }
      }
   }
   
}
 