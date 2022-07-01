<?php

namespace Tatva\EduTech\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;


/**
 * Data provider for attraction highlights field
 */
class Content extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @var string
     */
    protected $scopeName;
    protected $attributeSet;
    protected $_categoryFactory;
    protected $_storeConfig;
    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $storeConfig,
        $scopeName = ''
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->attributeSet = $attributeSet;
        $this->_categoryFactory = $categoryFactory;
        $this->scopeName = $scopeName;
        $this->_storeConfig = $storeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $model = $this->locator->getProduct();
        $modelId = $model->getId();

        $attributeSetRepository = $this->attributeSet->get($model->getAttributeSetId());
        $AttributeSetName = $attributeSetRepository->getAttributeSetName();
        $completeCurriculumData1 = [];
        if ($AttributeSetName == 'EduTech')
        {
            $collection = $this->_categoryFactory->create()->getCollection()->addAttributeToFilter('name','Edu Tech')->setPageSize(1);
            if ($collection->getSize()) {
                $educategoryId = $collection->getFirstItem()->getId();
            }
            $completeCurriculumData1 = [$educategoryId];
        }
        
        $categoryIds = array_unique(array_merge($model->getCategoryIds(),$completeCurriculumData1));
        $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/category_ids';
        $data = $this->arrayManager->set($path, $data, $categoryIds);

        $edu_top1 = $model->getData('edu_top1');
        $edu_top2 = $model->getData('edu_top2');
        $edu_top3 = $model->getData('edu_top3');
        $trainers_tab = $model->getData('trainers_tab');
        $teachers_tab = $model->getData('teachers_tab');
        $trainers_teachers_list = $model->getData('trainers_teachers_list');

        //What is it
        if ($edu_top1 == null)
        {
            $edu_top1 = $this->_storeConfig->getValue('edu_tech/edu_tech_attributes/edutop1');
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/edu_top1';
            $data = $this->arrayManager->set($path, $data, $edu_top1);
        }

        //Who is it for?
        if ($edu_top2 == null)
        {
            $edu_top2 = $this->_storeConfig->getValue('edu_tech/edu_tech_attributes/edutop2');
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/edu_top2';
            $data = $this->arrayManager->set($path, $data, $edu_top2);
        }

        //Why EduDecks?
        if ($edu_top3 == null)
        {
            $edu_top3 = $this->_storeConfig->getValue('edu_tech/edu_tech_attributes/edutop3');
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/edu_top3';
            $data = $this->arrayManager->set($path, $data, $edu_top3);
        }

        //trainers_tab
        if ($trainers_tab == null)
        {
            $trainers_tab = $this->_storeConfig->getValue('edu_tech/edu_tech_attributes/trainers_tab');
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/trainers_tab';
            $data = $this->arrayManager->set($path, $data, $trainers_tab);
        }

        //teachers_tab
        if ($teachers_tab == null)
        {
            $teachers_tab = $this->_storeConfig->getValue('edu_tech/edu_tech_attributes/teachers_tab');
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/teachers_tab';
            $data = $this->arrayManager->set($path, $data, $teachers_tab);
        }

        //trainers_teachers_list
        if ($trainers_teachers_list == null)
        {
            $trainers_teachers_list =   $this->_storeConfig->getValue('edu_tech/edu_tech_attributes/trainers_teachers_list');
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/trainers_teachers_list';
            $data = $this->arrayManager->set($path, $data, $trainers_teachers_list);
        }
        
        // Complete Curriculum 
        $completeCurriculum = $model->getCompleteCurriculum();
        $completeCurriculumData = $this->getDynamicRowData($completeCurriculum, 'complete_curriculum', 'complete_curriculum_content');

        if ($completeCurriculumData) {
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/complete_curriculum_row';
            $data = $this->arrayManager->set($path, $data, $completeCurriculumData);
        }

        // Sample Instructor Notes
        $sampleInstructore = $model->getSampleInstructorNotes();
        $sampleInstructoreData = $this->getDynamicRowData($sampleInstructore, 'sample_instructor_notes', 'sample_instructor_content');

        if ($sampleInstructoreData) {
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/sample_instructor_notes_rows';
            $data = $this->arrayManager->set($path, $data, $sampleInstructoreData);
        }


        // FAQs
        $faq = $model->getProductFaq();
        $faqData = $this->getDynamicRowData($faq, 'product_faq', 'product_faq_content');

        if ($faqData) {
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/product_faq_row';
            $data = $this->arrayManager->set($path, $data, $faqData);
        }

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        return $this->meta;
    }

    public function getDynamicRowData($wholeContent, $mainContentID, $contentID)
    {
        if ($wholeContent) {

            $contentData = array();
            $wholeContent = explode('#', $wholeContent);
            $mainContent = explode('|',  $wholeContent[0]);

            for ($i = 0; $i < count($mainContent); $i++) {
                $contentData[$i][$mainContentID] = $mainContent[$i];
            }

            if (count($wholeContent) == 2) {
                $content = explode('|', $wholeContent[1]);
                for ($i = 0; $i < count($content); $i++) {
                    $contentData[$i][$contentID] =  $content[$i];
                }
            }

            return $contentData;

        } else {
            return false;
        }
    }
}
