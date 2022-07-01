<?php

namespace Tatva\Questionnaire\Api\Data;

interface QuestionnaireInterface
{

    const id = 'id';

    public function getId();

    public function setId($id);

    public function getName();

    public function setName($name);

    public function getPhone();

    public function setPhone($phone);

    public function getCallFlag();

    public function setCallFlag($call_flag);

    public function getNumberOfSlides();

    public function setNumberOfSlides($number_of_slides);

    public function getStyleOption();

    public function setStyleOption($style_option);

    public function getTemplateOrDiagramDetails();

    public function setTemplateOrDiagramDetails($template_or_diagram_details);

    public function getDescription();

    public function setDescription($description);

    public function getClientId();

    public function setClientId($client_id);

    public function getQuestionnaireFile();

    public function setQuestionnaireFile($questionnaire_file);
}
