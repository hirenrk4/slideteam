<?php
namespace Tatva\Portfolio\Setup\Patch\Data;
 
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class PortfolioPagesNew implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
 
    /**
     * @var PageFactory
     */
    private $pageFactory;
    /**
     * @var \Magento\Cms\Model\ResourceModel\Page
     */
    private $pageResource;
 
    /**
     * AddNewCmsPage constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param PageFactory $pageFactory
     * @param \Magento\Cms\Model\ResourceModel\Page $pageResource
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        PageFactory $pageFactory,
        \Magento\Cms\Model\ResourceModel\Page $pageResource,
        \Magento\Framework\App\ResourceConnection $recource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->pageFactory = $pageFactory;
        $this->pageResource = $pageResource;
        $this->resource = $recource;
    }
 
    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        
        $identifiers = array(
            "powerpoint-presentation-design-services/portfolio" => "portfolio",
            "powerpoint-presentation-design-services/portfolio/business-presentation"=>"business",
            "powerpoint-presentation-design-services/portfolio/business-proposal"=>"proposal",
            "powerpoint-presentation-design-services/portfolio/leadership-training"=>"leadership",
            "powerpoint-presentation-design-services/portfolio/company-strategic-plan"=>"company",
            "powerpoint-presentation-design-services/portfolio/global-digitalization"=>"digitalization",
            "powerpoint-presentation-design-services/portfolio/ebilling-system-project"=>"ebilling",
            "powerpoint-presentation-design-services/portfolio/agriculture-growth-rate"=>"Agriculture",
            "powerpoint-presentation-design-services/portfolio/oil-and-gas-production-capacity"=>"Oil",
            "powerpoint-presentation-design-services/portfolio/customer-experience-training"=>"Experience",
            "powerpoint-presentation-design-services/portfolio/leadership-management"=>"Leadershipmanagement",
            "powerpoint-presentation-design-services/portfolio/digital-technology-adoption-action-plan"=>"Digital",
            "powerpoint-presentation-design-services/portfolio/debt-raising-pitch"=>"Debt",
            "powerpoint-presentation-design-services/portfolio/cost-optimization"=>"Cost",
            "powerpoint-presentation-design-services/portfolio/business-strategy-discussion"=>"Strategy",
            "powerpoint-presentation-design-services/portfolio/information-technology-solutions"=>"Information",
            "powerpoint-presentation-design-services/portfolio/automobile-cyber-security"=>"automobile"
        );

        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('cms_page');

        foreach($identifiers as $key => $value)
        {
            //$tableName = $resource->getTableName('cms_page');
            $sql = "Select page_id FROM " . $tableName." where identifier='".$key."'";
            $result = $connection->fetchAll($sql);
            if(!empty($result))
            {
                $pageid = $result[0]['page_id'];
                $sql = "Update " . $tableName . " Set layout_update_selected = '".$value."' where page_id ='".$pageid."' ";
                $connection->query($sql);
            }
        }
        
        $this->moduleDataSetup->endSetup();
    }
 
    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
 
    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}