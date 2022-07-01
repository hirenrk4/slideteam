<?php
namespace Vgroup65\Testimonial\Model\System\Config;
 
use Magento\Framework\Option\ArrayInterface;
class Status implements ArrayInterface
{
    const ACTIVE  = 1;
    const INACTIVE = 0;
    const MALE = 'male';
    const FEMALE = 'female';
 
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            self::ACTIVE => __('Active'),
            self::INACTIVE => __('Inactive')
        ];
 
        return $options;
    }
    
    public function toOptionGender()
    {
        $options = [
            '' => 'Select Gender',
            self::MALE => __('Male'),
            self::FEMALE => __('Female')
        ];
 
        return $options;
    }
    
    public function toOptionStates()
    {
        $options = [
            '' =>  'Select State',
            'Alabama' => 'Alabama',
            'Alaska' => 'Alaska',
            'American Samoa' => 'American Samoa',
            'Arizona' => 'Arizona',
            'Arkansas' => 'Arkansas',
            'Armed Forces Africa' => 'Armed Forces Africa',
            'Armed Forces Americas' => 'Armed Forces Americas',
            'Armed Forces Canada' => 'Armed Forces Canada',
            'Armed Forces Europe' => 'Armed Forces Europe',
            'Armed Forces Middle East' => 'Armed Forces Middle East',
            'Armed Forces Pacific' => 'Armed Forces Pacific',
            'California' => 'California',
            'Colorado' => 'Colorado',
            'Connecticut' => 'Connecticut',
            'Delaware' => 'Delaware',
            'District of Columbia' => 'District of Columbia',
            'Federated States Of Micronesia' => 'Federated States Of Micronesia', 
            'Florida' => 'Florida',
            'Georgia' => 'Georgia',
            'Guam' => 'Guam',
            'Hawaii' => 'Hawaii',
            'Idaho' => 'Idaho',
            'Illinois' => 'Illinois',
            'Indiana' => 'Indiana',
            'Iowa' => 'Iowa',
            'Kansas' => 'Kansas',
            'Kentucky' => 'Kentucky',
            'Louisiana' => 'Louisiana',
            'Maine' => 'Maine',
            'Marshall Islands' => 'Marshall Islands',
            'Maryland' => 'Maryland',
            'Massachusetts' => 'Massachusetts',
            'Michigan' => 'Michigan',
            'Minnesota' => 'Minnesota',
            'Mississippi' => 'Mississippi',
            'Missouri' => 'Missouri',
            'Montana' => 'Montana',
            'Nebraska' => 'Nebraska',
            'Nevada' => 'Nevada',
            'New Hampshire' => 'New Hampshire',
            'New Jersey' => 'New Jersey',
            'New Mexico' => 'New Mexico',
            'New York' => 'New York',
            'North Carolina' => 'North Carolina',
            'North Dakota' => 'North Dakota',
            'Northern Mariana Islands' => 'Northern Mariana Islands',
            'Ohio' => 'Ohio',
            'Oklahoma' => 'Oklahoma',
            'Oregon' => 'Oregon',
            'Palau' => 'Palau',
            'Pennsylvania' => 'Pennsylvania',
            'Puerto Rico' => 'Puerto Rico',
            'Rhode Island' => 'Rhode Island',
            'South Carolina' => 'South Carolina',
            'South Dakota' => 'South Dakota',
            'Tennessee' => 'Tennessee',
            'Texas' => 'Texas',
            'Utah' => 'Utah',
            'Vermont' => 'Vermont',
            'Virgin Islands' => 'Virgin Islands',
            'Virginia' => 'Virginia',
            'Washington' => 'Washington',
            'West Virginia' => 'West Virginia',
            'Wisconsin' => 'Wisconsin',
            'Wyoming' => 'Wyoming'
        ];
 
        return $options;
    }
    
    
}