<?php
namespace Vgroup65\Testimonial\Controller\Adminhtml\Samplefile;

use Vgroup65\Testimonial\Controller\Adminhtml\Testimonial;
class Index extends Testimonial {

    public function execute() {
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

        $outputFile = $basePath."/SampleFile.csv";
        $handle = fopen($outputFile, 'w');
        fputcsv($handle, $heading);
        
        $sampleValue = [
            '',
            'First_Name',
            'Last_Name',
            'MALE',
            '20',
            'Designation',
            'Company',
            '',
            'Testimonial',
            '',
            '',
            '',
            '',
            'Active'
        ];
        fputcsv($handle, $sampleValue);
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