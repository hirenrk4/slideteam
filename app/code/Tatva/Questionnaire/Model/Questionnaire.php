<?php

namespace Tatva\Questionnaire\Model;

use Tatva\Questionnaire\Api\Data\QuestionnaireInterface;

class Questionnaire extends \Magento\Framework\Model\AbstractModel implements QuestionnaireInterface
{

    const CACHE_TAG = 'questionnaire';

    protected $_cacheTag = 'questionnaire';
    protected $_eventPrefix = 'questionnaire';

    protected function _construct()
    {
        $this->_init('Tatva\Questionnaire\Model\ResourceModel\Questionnaire');
    }

    public function getId()
    {
        return $this->getData(self::id);
    }

    public function setId($id)
    {
        return $this->setData(self::id, $id);
    }

    public function getName()
    {
        return $this->getData(self::name);
    }

    public function setName($name)
    {
        return $this->setData(self::name, $name);
    }

    public function getPhone()
    {
        return $this->getData(self::phone);
    }

    public function setPhone($phone)
    {
        return $this->setData(self::phone, $phone);
    }

    public function getCallFlag()
    {
        return $this->getData(self::call_flag);
    }

    public function setCallFlag($call_flag)
    {
        return $this->setData(self::call_flag, $call_flag);
    }

    public function getNumberOfSlides()
    {
        return $this->getData(self::number_of_slides);
    }

    public function setNumberOfSlides($number_of_slides)
    {
        return $this->setData(self::number_of_slides, $number_of_slides);
    }

    public function getStyleOption()
    {
        return $this->getData(self::style_option);
    }

    public function setStyleOption($style_option)
    {
        return $this->setData(self::style_option, $style_option);
    }

    public function getTemplateOrDiagramDetails()
    {
        return $this->getData(self::template_or_diagram_details);
    }

    public function setTemplateOrDiagramDetails($template_or_diagram_details)
    {
        return $this->setData(self::template_or_diagram_details, $template_or_diagram_details);
    }

    public function getDescription()
    {
        return $this->getData(self::description);
    }

    public function setDescription($description)
    {
        return $this->setData(self::description, $description);
    }

    public function getClientId()
    {
        return $this->getData(self::client_id);
    }

    public function setClientId($client_id)
    {
        return $this->setData(self::client_id, $client_id);
    }

    public function getQuestionnaireFile()
    {
        return $this->getData(self::questionnaire_file);
    }

    public function setQuestionnaireFile($questionnaire_file)
    {
        return $this->setData(self::questionnaire_file, $questionnaire_file);
    }

}
