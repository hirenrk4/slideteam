<?php
namespace Tatva\Generalconfiguration\Model;

class Variables extends \Magento\Variable\Model\Source\Variables
{
    protected $_tatvaConfigs;

    /**
     * Assoc array of configuration variables.
     *
     * @var array
     */
    private $configVariables = [];

    /**
     * @var \Magento\Config\Model\Config\Structure\SearchInterface
     */
    private $configStructure;


    public function __construct(\Magento\Config\Model\Config\Structure\SearchInterface $configStructure)
    {
        parent::__construct($configStructure);
        $this->_tatvaConfigs = 
        [
            ['value' => 'contact/customemail/recipient_email_design', 'label' => __('Send Emails To for Design Questionnaire(TO)')],
            ['value' => 'button/video_config/field1', 'label'=> __('Youtube video Link')]
        ];
        
        $this->_configVariables = array_merge($this->configVariables,$this->_tatvaConfigs);
    }
}