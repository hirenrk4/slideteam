<?php
namespace Vgroup65\Testimonial\Controller\Adminhtml\Export;

use Vgroup65\Testimonial\Controller\Adminhtml\Testimonial;
class Index extends Testimonial {

    public function execute() {
        $testimonialCollection = $this->_testimonialFactory->create()->getCollection();
        $helper = $this->helper;
        $basePath = $helper->getBaseDir();

        $heading = [
            __('Testimonial ID'),
            __('First Name'),
            __('Last Name'),
            __('Gender'),
            __('Age'),
            __('Designation'),
            __('Company'),
            __('Image'),
            __('Testimonial'),
            __('Website'),
            __('Address'),
            __('City'),
            __('State'),
            __('Status')
        ];
        
        if (!file_exists($basePath)):
             mkdir($basePath,0775, true);
        endif;
         
        $outputFile = $basePath."/TestimonialList.csv";
        $handle = fopen($outputFile, 'w');
        fputcsv($handle, $heading); 
        foreach ($testimonialCollection as $testimonial) {
             //status
            $status = 'Inactive';
             if($testimonial['status'] == '1'):
                 $status = 'Active';
             endif;
             
             //image 
            $testimonialImageValue = '';
            $testimonialImage = $testimonial['image'];
            if(!empty($testimonialImage)):
                    $getBaseUrl = $helper->getBaseUrl();
                    $testimonialImageValue = $getBaseUrl.$testimonialImage; 
            endif;    
             
             
             $row = [
                $testimonial['testimonial_id'],
                $testimonial['first_name'],
                $testimonial['last_name'],
                $testimonial['gender'],
                $testimonial['age'],
                $testimonial['designation'],
                $testimonial['company'],
                $testimonialImageValue,
                $testimonial['testimonial'],
                $testimonial['website'],
                $testimonial['address'],
                $testimonial['city'],
                $testimonial['state'],
                $status
             ];
             fputcsv($handle, $row);
         }
         $this->downloadCsv($outputFile);
    }
 
    public function downloadCsv($file)
    {
         if (file_exists($file)) {
             //set appropriate headers
             header('Content-Description: File Transfer');
             header('Content-Type: application/csv');
             header('Content-Disposition: attachment; filename='.basename($file));
             header('Expires: 0');
             header('Cache-Control: must-revalidate');
             header('Pragma: public');
             header('Content-Length: ' . filesize($file));
             ob_clean();flush();
             readfile($file);
             unlink($file);
         }
    }
}