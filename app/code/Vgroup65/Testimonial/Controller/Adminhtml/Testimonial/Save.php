<?php
namespace Vgroup65\Testimonial\Controller\Adminhtml\Testimonial;
use Magento\Backend\App\Action\Context;
use Vgroup65\Testimonial\Controller\Adminhtml\Testimonial;

class Save extends Testimonial {
    
    public function execute() {
        $formData = $this->getRequest()->getPost('testimonial');
        
        $file = $this->getRequest()->getFiles('image');
        //update testimonial
        if(isset($formData['testimonial_id'])):
                $testimonialModel = $this->_testimonialFactory->create();
                $testimonialCollection = $testimonialModel->getCollection();
                $testimonialCollection->addFieldToFilter('testimonial_id' , array('eq' => $formData['testimonial_id']));
                
                //check for mage resolution
                if(isset($file['size']) && $file['size'] > 0):
                    $size = @getImageSize($file['tmp_name']);
                    if(!empty($size) && count($size) > 0):
                            if($size[0] < 200 || $size[1] < 200):
                                $this->messageManager->addError(__('Minimum image size should be 200*200px'));
                                $this->_redirect('*/*/edit' , array('id' => $formData['testimonial_id']));
                                return; 
                            endif;
                    endif;
                endif;
                
                
                //delete request from checkbox
                $deleteImageFromCheckBox = 0;
                if(isset($formData['image']['delete']) && $formData['image']['delete'] == 1):
                    $deleteImageFromCheckBox = 1;
                endif;
                
                foreach($testimonialCollection as $testimonial):
                    $curretnImage = $testimonial['image'];
                    $curretnResizeImage = $testimonial['resize_image'];
                    $formData['image'] = $curretnImage;
                endforeach;
                
                if($deleteImageFromCheckBox == 1):
                    $imageHelper = $this->helper;
                    $path = $imageHelper->getBaseDir();
                    if(!empty($curretnImage)): 
                        if(file_exists($path.$curretnImage)):
                            unlink($path.$curretnImage);
                        endif;
                        $formData['image'] = '';
                    endif;
                    
                    if(!empty($curretnResizeImage)):
                        if(file_exists($path.$curretnResizeImage)):
                            unlink($path.$curretnResizeImage);
                        endif;
                        $formData['resize_image'] = '';
                    endif;
                endif; 
                   
                //upload image
                if(isset($file['size']) && $file['size'] > 0):
                    $imageResponce = $this->getUploadNewsImageName($file['size']);
                    foreach($imageResponce as $imagekey=> $imageValue):
                           if($imagekey == 'success'):
                               //unlink image
                               if(!empty($curretnImage)):
                                 $imageHelper = $this->helper;  
                                 $path = $imageHelper->getBaseDir();
                                 unlink($path.$curretnImage);
                               endif;
                               
                               $formData['image'] = $imageValue;
                               
                               //update and unlink resize image
                               $updateResizeImage = $this->resize($imageValue , 200 , 200);
                               if($updateResizeImage):
                                   $formData['resize_image'] = '/resize/'.basename($imageValue);
                               endif;
                               
                               //unlink resize image
                               if(!empty($curretnResizeImage)):
                                 $imageHelper = $this->helper;  
                                 $path = $imageHelper->getBaseDir();
                                 
                                 if(file_exists($path.$curretnResizeImage)):
                                     unlink($path.$curretnResizeImage);
                                 endif;
                               endif;
                               
                           endif;

                           if($imagekey == 'errorUpload'):
                               $this->_getSession()->setFormData($formData);
                               $this->messageManager->addError(__($imageValue));
                               $this->_redirect('*/*/new');
                               return;                             
                           endif;
                    endforeach;
                endif;
                
                try{
                $testimonialModel->setData($formData);
                $testimonialModel->save();
                }
                catch(\Exception $e){
                   $this->messageManager->addError(__($e->getMessage()));
                   $this->_redirect('*/*/edit' , array('id' => $formData['testimonial_id']));
                   return;       
                }
            $this->messageManager->addSuccess(__('Testimonial Updated Successfully'));
            $this->_redirect('*/*/');
            return;      
                
            //create testimonial
            else:
                $testimonialModel = $this->_testimonialFactory->create();
                $testimonialCollection = $testimonialModel->getCollection();
                
                if(isset($file['size']) && $file['size'] > 0):
                    $size = @getImageSize($file['tmp_name']);
                    if(!empty($size) && count($size) > 0):
                            if($size[0] < 200 || $size[1] < 200):
                               $this->messageManager->addError(__('Minimum image size should be 200*200px'));
                               $this->_redirect('*/*/new');
                               return; 
                            endif;
                    endif;
                endif;
                
                if(isset($file['size']) && $file['size'] > 0):
                    $imageResponce = $this->getUploadNewsImageName($file['size']);
                    foreach($imageResponce as $imagekey=> $imageValue):
                           if($imagekey == 'success'):
                               $formData['image'] = $imageValue;
                           
                               $resizeImage = $this->resize($imageValue , 200 , 200);
                               
                               $formData['resize_image'] = '';
                               if($resizeImage):
                                   $formData['resize_image'] = '/resize/'.basename($formData['image']);
                               endif;
                           endif;

                           if($imagekey == 'errorUpload'):
                               $this->_getSession()->setFormData($formData);
                               $this->messageManager->addError(__($imageValue));
                               $this->_redirect('*/*/new');
                               return;                             
                           endif;
                    endforeach;
                endif;
                
                $testimonialModel->setData($formData);
                
                try {
                // Save news
                $testimonialModel->save();
                }
                catch(\Exception $e){
                    $this->messageManager->addError($e->getMessage());
                    $this->_redirect('*/*/new');
                    return;
                }
                
                $this->messageManager->addSuccess(__('The Testimonial has been saved.'));
                $this->_redirect('*/*/');
                return;
        endif;
    }
    
    public function getUploadNewsImageName($imageSize){
        $imageHelper = $this->helper;
        //Upload news image
                if($imageSize > 0): 
                    try{ 
                    $uploader = $imageHelper->getFileUploaderFactory()->create(['fileId' => 'image']);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);
                    $uploader->setAllowCreateFolders(true);
                    $path = $imageHelper->getBaseDir();
                    $responce = $uploader->save($path); 
                    
                    $imageName = $responce['file'];
                    return array('success' => $imageName);
                    }

                    catch(\Exception $e){
                        return array('errorUpload' => $e->getMessage());
                    }   
                    
                    else:
                        return array('error' => 'Image field is empty');
                endif;
    }
    
    // pass imagename, width and height
    public function resize($image, $width = 200, $height = 200)
    {
        $absolutePath = $this->helper->getBaseDir().$image;
        $imageResized = $this->helper->getBaseDir().'/resize/'.basename($image);  

        //create image factory...
        $imageResize = $this->_imageFactory->create();         
        $imageResize->open($absolutePath);
        $imageResize->constrainOnly(TRUE);         
        $imageResize->keepTransparency(TRUE);         
        $imageResize->keepFrame(FALSE);         
//        $imageResize->keepAspectRatio(TRUE);         
        $imageResize->resize($width,$height);  
        //destination folder                
        $destination = $imageResized ;    
        //save image      
        $imageResize->save($destination);         
        return true;
    } 
}