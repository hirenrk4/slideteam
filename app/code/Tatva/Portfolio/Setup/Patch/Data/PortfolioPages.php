<?php
namespace Tatva\Portfolio\Setup\Patch\Data;
 
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class PortfolioPages implements DataPatchInterface
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
        \Magento\Cms\Model\ResourceModel\Page $pageResource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->pageFactory = $pageFactory;
        $this->pageResource = $pageResource;
    }
 
    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        

        /*$pageIdentifier1 = 'mypage';
        $cmsPageModel = $this->pageFactory->create()->load($pageIdentifier1);
        $this->pageResource->delete($cmsPageModel); /*delete the existing page */
        $portfoliocontent = '<div class="portfolio_main_wrapper portf_new">
        <div class="container clearfix">
            <div class="portfolio_custom_main">
                <h1>Our Portfolio PowerPoint Templates</h1>
                <p class="portf_sub_heading">If you\'re looking for a way to create an engaging and professional-looking portfolio, look no further than our portfolio PowerPoint templates. These PPT layouts are perfect for entrepreneurs who want to showcase their work in a visually appealing way. With easy-to-use slides, you can quickly create a portfolio that will make your business stand out from the competition. Our portfolio PPT templates are readily available and customizable, making it perfect for any entrepreneur. We\'ve got a variety of themes and layouts to choose from, so you can find the perfect one for your presentation. These PowerPoint templates come in many different styles and colors, allowing you to beautify your presentation in minutes. Our portfolio PPT layouts help you track your company\'s growth and illustrate your successes to potential investors or partners. Whether you\'re pitching your new business idea or introducing your company to potential investors, these templates will help you make a great impression. So download our easily accessible portfolio PowerPoint templates now!</p>
                <div class="product data items" id="myBtnContainer" data-mage-init=\'{"mage/tabs":{"openedState": "active", "animate":{"duration": 100}, "active": 0}}\'>
                    <div class="tab-header">
                        <div class="item title btn" data-role="collapsible"> <a class="switch1" data-toggle="trigger" href="#tab-all">All</a> </div>
                        <div class="item title btn" data-role="collapsible"> <a class="switch1" data-toggle="trigger" href="#tab-business">Business</a> </div>
                        <div class="item title btn" data-role="collapsible"> <a class="switch1" data-toggle="trigger" href="#tab-energy">Energy & Agriculture</a> </div>
                        <div class="item title btn" data-role="collapsible"> <a class="switch1" data-toggle="trigger" href="#tab-marketing">Marketing, Research & Strategy</a> </div>
                        <div class="item title tab-technology btn" data-role="collapsible"> <a class="switch1" data-toggle="trigger" href="#tab-technology">Technology</a> </div>
                    </div>
                    <div id="tab-all" class="item content" data-role="content">
                        <div class="row">
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/business-presentation"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide1.JPG"}}" alt="Business Presentation Introduction PPT Slide"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Business Presentation</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/business-proposal"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide6.jpg"}}" alt="Business Proposal"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Business Proposal</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/leadership-training"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide4.jpg"}}" alt="Leadership Training"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Leadership Training</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/company-strategic-plan"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide2.JPG"}}" alt="Company Strategic Plan"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Company Strategic Plan</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/global-digitalization"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide5.jpg"}}" alt="Global Digitalization"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Global Digitalization</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/ebilling-system-project"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide3.jpg"}}" alt="E-Billing System Project"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>E-Billing System Project</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/agriculture-growth-rate"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide8.jpg"}}" alt="Agriculture Growth Rate"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Agriculture Growth Rate</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/oil-and-gas-production-capacity"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide9.jpg"}}" alt="Oil and Gas Production Capacity"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Oil and Gas Production Capacity</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/customer-experience-training"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide7.jpg"}}" alt="Customer Experience Training">
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Customer Experience Training</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/cost-optimization"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide10.jpg"}}" alt="Cost Optimization"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Cost Optimization</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/business-strategy-discussion"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide11.jpg"}}" alt="Business Strategy Discussion"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Business Strategy Discussion</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/debt-raising-pitch"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide12.jpg"}}" alt="Debt Raising Pitch"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Debt Raising Pitch</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/digital-technology-adoption-action-plan"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide13.PNG"}}" alt="Digital Technology Adoption Action Plan"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Digital Technology Adoption Action Plan</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/leadership-management"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide14.PNG"}}" alt="Leadership Management"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Leadership Management</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/automobile-cyber-security"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide15.PNG"}}" alt="Automobile Cyber Security"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Automobile Cyber Security</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column all">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/information-technology-solutions"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide16.PNG"}}" alt="Information Technology Solutions"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Information Technology Solutions</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="portfolio_button">
                            <div class="container clearfix">
                                <div class="load_more_btn"> <button type="button" name="button"> Load More </button> </div>
                            </div>
                        </div>
                    </div>
                    <div id="tab-business" class="item content" data-role="content">
                        <div class="row">
                            <div class="column business">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/business-presentation"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide1.JPG"}}" alt="Business Presentation"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Business Presentation</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column business">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/company-strategic-plan"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide2.JPG"}}" alt="Company Strategic Plan">
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Company Strategic Plan</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column business">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/leadership-training"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide4.jpg"}}" alt="Leadership Training"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Leadership Training</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column business">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/global-digitalization"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide5.jpg"}}" alt="Global Digitalization"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon">
                                                <h3>Global Digitalization</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column business">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/business-proposal"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide6.jpg"}}" alt="Business Proposal">
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Business Proposal</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="portfolio_button">
                                <div class="container clearfix">
                                    <div class="load_more_btn"> <button type="button" name="button"> Load More </button> </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="tab-energy" class="item content" data-role="content">
                        <div class="row">
                            <div class="column energy">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/ebilling-system-project"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide3.jpg"}}" alt="E-Billing System Project"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>E-Billing System Project</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column energy">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/customer-experience-training"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide7.jpg"}}" alt="Customer Experience Training"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Customer Experience Training</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column energy">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/agriculture-growth-rate"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide8.jpg"}}" alt="Agriculture Growth Rate"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Agriculture Growth Rate</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column energy">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/oil-and-gas-production-capacity"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide9.jpg"}}" alt="Oil and Gas Production Capacity"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Oil and Gas Production Capacity</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="portfolio_button">
                            <div class="container clearfix">
                                <div class="load_more_btn"> <button type="button" name="button"> Load More </button> </div>
                            </div>
                        </div>
                    </div>
                    <div id="tab-marketing" class="item content" data-role="content">
                        <div class="row">
                            <div class="column marketing">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/cost-optimization"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide10.jpg"}}" alt="Cost Optimization"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Cost Optimization</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column marketing">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/business-strategy-discussion"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide11.jpg"}}" alt="Business Strategy Discussion"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Business Strategy Discussion</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column marketing">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/debt-raising-pitch"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide12.jpg"}}" alt="Debt Raising Pitch"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Debt Raising Pitch</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column marketing">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/digital-technology-adoption-action-plan"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide13.PNG"}}" alt="Digital Technology Adoption Action Plan"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Digital Technology Adoption Action Plan</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column marketing">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/leadership-management"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide14.PNG"}}" alt="Leadership Management"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Leadership Management</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="portfolio_button">
                            <div class="container clearfix">
                                <div class="load_more_btn"> <button type="button" name="button"> Load More </button> </div>
                            </div>
                        </div>
                    </div>
                    <div id="tab-technology" class="item content tab-technology-content" data-role="content">
                        <div class="row">
                            <div class="column technology">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/automobile-cyber-security"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide15.PNG"}}" alt="Automobile Cyber Security"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Automobile Cyber Security</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="column technology">
                                <div class="content">
                                    <a href="{{store url="powerpoint-presentation-design-services/portfolio/information-technology-solutions"}}">
                                        <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Slide16.PNG"}}" alt="Information Technology Solution"> 
                                        <div class="content_overlay">
                                            <div class="overlay_custom">
                                                <img src="{{view url="Magento_Cms::images/design_services_pages/portfolio/p_plus.png"}}" alt="plus icon"> 
                                                <h3>Information Technology Solution</h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="portfolio_button">
                            <div class="container clearfix">
                                <div class="load_more_btn"> <button type="button" name="button"> Load More </button> </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Magento_Cms::portfolio/portfolio_list.phtml"}}';
        $pageIdentifier0 = 'powerpoint-presentation-design-services/portfolio';
        $pageData0 = [
            'title' => 'Our Portfolio',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Custom Design Portfolio PowerPoint Templates - SlideTeam',
            'meta_keywords' => 'Custom Design PPT Portfolio',
            'meta_description' => 'Grab our unique portfolio PPT templates and talk about your company’s success with ease. You can edit these PowerPoint slides without any prior design experience.',
            'identifier' => $pageIdentifier0,
            'content_heading' => '',
            'content' => $portfoliocontent,
            'url_key' => $pageIdentifier0,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData0)->save();

        $pageIdentifier1 = 'powerpoint-presentation-design-services/portfolio/business-presentation';
        $pageData1 = [
            'title' => 'Business Presentation',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Custom Design Portfolio PowerPoint Templates - SlideTeam',
            'meta_keywords' => 'Custom Design PPT Portfolio',
            'meta_description' => 'Grab our unique portfolio PPT templates and talk about your company’s success with ease. You can edit these PowerPoint slides without any prior design experience.',
            'identifier' => $pageIdentifier1,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>Business Presentation</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/BUSINESS-PRESENTATION/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/BUSINESS-PRESENTATION/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/BUSINESS-PRESENTATION/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/BUSINESS-PRESENTATION/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/BUSINESS-PRESENTATION/Slide5.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/BUSINESS-PRESENTATION/Slide6.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/BUSINESS-PRESENTATION/Slide7.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Take advantage of our pre designed business presentation PowerPoint template to showcase your company’s value effectively. With the aid of this PPT design, you can determine the factors affecting business growth. Our company presentation PPT template comes with ample space wherein you can jot down the content that suits your business needs perfectly. By utilizing our eye-catching PowerPoint template, you can portray your business checklists with ease. Our designers have crafted this PPT layout with utmost care and dedication, leaving no room for mistakes. You can deploy this PowerPoint design to create a fool-proof strategy that takes your business to newer heights. There are various high-quality images in our engaging PPT template that enhance the look and feel of your business presentation. You can take advantage of this business PowerPoint design and portray new concepts that make your business grow. So without any further ado, download this engaging business PowerPoint template now!</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier1,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData1)->save();

        $pageIdentifier2 = 'powerpoint-presentation-design-services/portfolio/business-proposal';
        $pageData2 = [
            'title' => 'PQR Proposal PowerPoint Design',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Modern PQR Proposal PowerPoint Design - SlideTeam',
            'meta_keywords' => 'Business Proposal PPT ',
            'meta_description' => 'Employ our PQR proposal PPT design to close deals with your potential clients. You can use this PowerPoint template to create a compelling proposal with ease.',
            'identifier' => $pageIdentifier2,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>PQR Proposal PowerPoint Design</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/PQR-PROPOSAL/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/PQR-PROPOSAL/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/PQR-PROPOSAL/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/PQR-PROPOSAL/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/PQR-PROPOSAL/Slide5.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/PQR-PROPOSAL/Slide6.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/BUSINESS-PRESENTATION/Slide7.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Employ our modern PQR proposal PowerPoint template to create stunning presentations in minutes. Use this PPT design and introduce your viewers about the PQR concept in detail. Our readily available PowerPoint template helps you convey outstanding PQR proposals to the clients. With the aid of this PPT layout, you can let your clients know about the services your company offers. You can take advantage of this PowerPoint design to create an appealing cover letter for your proposal. Take the assistance of this PPT layout to explain why your services are better than that of your competitors. You can add your company’s logo in this PowerPoint design that helps you make your proposal stand out from the crowd. With the aid of our fantastic PowerPoint layout, you can tell your clients about your past projects. This PQR proposal PowerPoint design allows you to create a compelling client testimonial that grabs the attention of your future clients. So download this readily available PQR proposal PPT template.</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier2,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData2)->save();

        $pageIdentifier3 = 'powerpoint-presentation-design-services/portfolio/leadership-training';
        $pageData3 = [
            'title' => 'Leadership Training',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Engaging Leadership Training PPT Template - SlideTeam',
            'meta_keywords' => 'Leadership Training PPT Template',
            'meta_description' => 'Incorporate our leadership training PowerPoint template in your presentation and motivate your team towards success. So hurry up and grab this PPT design!',
            'identifier' => $pageIdentifier3,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>Leadership Training PPT Template</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/EXCEPTIONAL-LEADER/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/EXCEPTIONAL-LEADER/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/EXCEPTIONAL-LEADER/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/EXCEPTIONAL-LEADER/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/EXCEPTIONAL-LEADER/Slide5.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Become an inspiring leader for your employees with the help of this leadership training PPT template. This engaging PowerPoint design helps you concentrate on creating efficient leadership skills. With the aid of our PPT template, you can bring innovative strategies into action. Employ this stunning leadership training PowerPoint layout to educate your employees about a cohesive work culture. Take advantage of our PPT design to share your thoughts and ideas with the bosses in an attractive yet informative manner. You can use this PowerPoint template to portray the elements for transforming strategic thinking in leadership. By using our leadership PPT design, you can determine the unique features of the decision-making process that make your employees become future leaders. Incorporate this amazing PowerPoint template in your presentation to describe the concept of team building to the viewers. Our PPT design includes high-grade icons that enhance your content. So get access to this leadership training PowerPoint presentation template right away!</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier3,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData3)->save();

        $pageIdentifier4 = 'powerpoint-presentation-design-services/portfolio/company-strategic-plan';
        $pageData4 = [
            'title' => 'Company Strategic Plan',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Impressive Company Strategic Plan PPT Template - SlideTeam',
            'meta_keywords' => 'Company Strategic Plan PPT Template',
            'meta_description' => 'Use our company strategic plan PPT design and run your business operations smoothly. Get access to this PowerPoint template and personalize it the way you want.',
            'identifier' => $pageIdentifier4,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>Company Strategic Plan PPT Template</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/Company-Strategic-Plan for 2021/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/Company-Strategic-Plan for 2021/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/Company-Strategic-Plan for 2021/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/Company-Strategic-Plan for 2021/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/Company-Strategic-Plan for 2021/Slide5.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/Company-Strategic-Plan for 2021/Slide6.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Take advantage of our company strategic plan PPT template to streamline your business operations effectively. Use this readymade PowerPoint design to create a well-crafted strategy that helps you grow your business. Our PowerPoint layout has been designed to keep your business needs in mind, so you can personalize it the way you want. Employ this easily available PPT template and talk about your company’s annual sales performance. By using our PPT design, you can define your company’s moral values in an organized manner. With the aid of this company plan PowerPoint template, you can monitor your employees’ productivity. Our PPT template is fully editable, which means you can change its colors, fonts, font types, and font sizes as per your choices. The template comes with enough text room for you to jot down the relevant information that you want to convey to the audience. Download this business plan PowerPoint design and increase your company’s success rate.</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier4,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData4)->save();

        $pageIdentifier5 = 'powerpoint-presentation-design-services/portfolio/global-digitalization';
        $pageData5 = [
            'title' => 'Global Digitalization',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Attractive Global Digitalization PPT Design - SlideTeam',
            'meta_keywords' => 'Global Digitalization PPT Templates',
            'meta_description' => '',
            'identifier' => $pageIdentifier5,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>Global Digitalization</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/Global Digitalization/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/Global Digitalization/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/Global Digitalization/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/Global Digitalization/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/Global Digitalization/Slide5.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/business/Global Digitalization/Slide6.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Introducing our eye-catching global digitalization PowerPoint template. With the aid of this PPT layout, you can let your audience know how digitalization is transforming the world. You can take advantage of our easily available PowerPoint template to showcase the digital services that automates the business operations. By using this PPT layout, you can define the role of IaaS ( infrastructure as a service) in global digitalization. This predesigned global digitalization PowerPoint template helps you to create stunning presentations in minutes. There are various high-quality images in this PPT layout that beautify your presentation. For more personalized effect, you can also add your company’s images to this PowerPoint design. You can use this global digitalization PowerPoint design to educate the audience about modern digital capabilities. This engaging PowerPoint layout includes aesthetic colors and attractive background images that spruce up your presentation. So without wasting any extra minute, grab this professional-looking global digitalization PPT template now!</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier5,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData5)->save();
        
        $pageIdentifier6 = 'powerpoint-presentation-design-services/portfolio/ebilling-system-project';
        $pageData6 = [
            'title' => 'E-Billing System Project',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => '',
            'meta_keywords' => 'E-Billing System project ppt',
            'meta_description' => 'Download our E-billing system project PPT template for your business presentations. Use this PowerPoint design to grab your audiences’ attention instantly!',
            'identifier' => $pageIdentifier6,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>E-Billing - Financial Rules and Regulations</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/E-Billing/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/E-Billing/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/E-Billing/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/E-Billing/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/E-Billing/Slide5.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/E-Billing/Slide6.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">As an entrepreneur, you\'re always looking for ways to streamline your business and make it more efficient. One way to do that is by implementing an e-billing system. This professionally-designed PPT template can help you get started on your project. With its modern design and easy-to-use format, you\'ll be able to create a presentation that is sure to wow your audience. This fully customizable PPT template is designed to help you create a concise and visually appealing presentation that will help you communicate your ideas effectively. By using this PowerPoint layout, you can let your audience know how to manage your payments quickly and easily, without all the stress. With this template, you\'ll be able to put together a high-quality presentation that will help convince your stakeholders of the viability of your e-billing system project. So if you\'re looking to help get your new e-billing system off the ground, be sure to check out this PPT template today!</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier6,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData6)->save();

        $pageIdentifier7 = 'powerpoint-presentation-design-services/portfolio/agriculture-growth-rate';
        $pageData7 = [
            'title' => 'Agriculture Growth Rate',
            'page_layout' => '1column-design-services-cms',
            'meta_keywords' => 'Agriculture Growth Rate PPT Template',
            'meta_title' => 'Top Agriculture Growth Rate PPT Template - SlideTeam',
            'meta_description' => 'Introducing our stylish agriculture growth rate PPT template. You can grab this PowerPoint design to portray information about various crops, fertilizers, etc. ',
            'identifier' => $pageIdentifier7,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>Agriculture Growth Rate PPT Template</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/KRF/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/KRF/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/KRF/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/KRF/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/KRF/Slide5.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/KRF/Slide6.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/KRF/Slide7.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/KRF/Slide8.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/KRF/Slide9.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/KRF/Slide10.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Introducing our ready made agriculture growth rate PowerPoint template to help you create compelling presentations. By using this attractive PPT design, you can determine the geographical dominance of your agricultural functions. With the aid of our PowerPoint template, you can illustrate the strategies that help you resolve the risks associated with agricultural activities. Our content-ready agricultural growth rate PPT design has been extensively researched and crafted by our professionals, allowing you to deploy it without any hassle. Also, the PPT layout comes with plenty of space for you to jot down the valuable information that you want to share with your audience. You can also use this easily editable PowerPoint template to elaborate the different policies and reforms of your agricultural industry. Employ our PPT design to elucidate the measures that help you increase the growth rate of your agricultural company. So download our eye-catching agriculture growth PPT design and showcase the benefits of organic farming.</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier7,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData7)->save();

        $pageIdentifier8 = 'powerpoint-presentation-design-services/portfolio/oil-and-gas-production-capacity';
        $pageData8 = [
            'title' => 'Oil and Gas Production Capacity',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Oil and Gas Production Capacity PPT Slides - SlideTeam',
            'meta_keywords' => 'Oil and Gas Production Capacity PPT Slides',
            'meta_description' => 'Presenting our engaging oil and gas production capacity PPT template. Get access to this editable PowerPoint design and tweak the content as per your needs.',
            'identifier' => $pageIdentifier8,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>Oil and Gas Production Capacity</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Oil-and-Gas/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Oil-and-Gas/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Oil-and-Gas/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Oil-and-Gas/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Oil-and-Gas/Slide5.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Oil-and-Gas/Slide6.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Oil-and-Gas/Slide7.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Presenting our stylish oil and gas production capacity PowerPoint template. Use this amazing PPT layout to educate your audience about the fundamentals of the oil and gas industry. Our topic-specific PowerPoint design comes with ample space wherein you can tweak the content as per your needs. With the aid of this PPT template, you can portray the overall revenue from your oil production industry. By utilizing our eye-catching PowerPoint layout, you can determine the phenomena of import and export of crude oil. You can incorporate this PPT template in your presentation to let your audience know about the oil and gas prices all over the world. There are various high-quality icons in our PowerPoint design that enhance your presentation quotient. By using this PPT template, you can give your audience an overview of oil and refinery undertakings. You can easily convert our PowerPoint design into various file formats like PDF, PNG, and JPG. So hurry up and grab this oil and gas production PowerPoint template instantly!</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier8,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData8)->save();

        $pageIdentifier9 = 'powerpoint-presentation-design-services/portfolio/customer-experience-training';
        $pageData9 = [
            'title' => 'Customer Experience Training',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Customer Experience PowerPoint Template - SlideTeam',
            'meta_keywords' => 'Customer Experience PowerPoint Template',
            'meta_description' => 'Build a long-term relationship with your users by utilizing our customer experience PowerPoint template. So download this eye-catching PPT design right away!',
            'identifier' => $pageIdentifier9,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>Customer Experience PowerPoint Template</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Defining/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Defining/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Defining/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Defining/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Defining/Slide5.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Defining/Slide6.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Defining/Slide7.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Energy-agriculture/Defining/Slide8.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Meet the buyers’ expectations by utilizing our eye-catching customer experience PowerPoint template. With the aid of this stunning PPT design, you can provide your customers with the best experience. Our ready made consumer experience PowerPoint template has been designed with utmost care and attention, allowing you to input your content with ease. You can employ this PPT layout to let your employees know the importance of customer retention. By using our unique PowerPoint template, you can create a fool-proof strategy that helps you bag more clients. You can deploy this pre-designed PPT layout to portray your products or services in an appealing manner. Incorporate our easily available PowerPoint template in your presentation to define the customer touchpoints. The template includes an attractive backdrop and fonts that enhance the look of your presentation. Our customer experience PowerPoint design is 100% editable, so that you can change it as per your needs. So download this compelling PowerPoint template and create a seamless customer experience.</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier9,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData9)->save();

        $pageIdentifier10 = 'powerpoint-presentation-design-services/portfolio/leadership-management';
        $pageData10 = [
            'title' => 'Leadership Management',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Leadership Management PPT Template | SlideTeam',
            'meta_keywords' => 'Leadership Management PPT Templates',
            'meta_description' => 'Introducing our pre designed leadership management PowerPoint design. Use this fantastic PPT template and boost your team members to become great leaders!',
            'identifier' => $pageIdentifier10,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>Leadership Management PPT Template</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Nancy Ray/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Nancy Ray/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Nancy Ray/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Nancy Ray/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Nancy Ray/Slide5.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Nancy Ray/Slide6.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Nancy Ray/Slide7.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Motivate your peers to become a great leader by using our fully editable leadership management PowerPoint template. With the aid of this PPT design, you can define various leadership styles that enhance your company’ growth. Take advantage of our ready made PowerPoint template to portray the roles and responsibilities of a good leader. You can use this PowerPoint design to create a solid structure for your leadership that helps you become a potential leader. There are various high-quality icons in this leadership PPT layout that make your content even more modern and stylish. You can incorporate this stunning PowerPoint template in your presentation to explain the leadership model to your employees. This leadership PowerPoint template is easily adaptable with Google Slides, making it accessible at once. You can open and save this professional-looking PowerPoint template into various file formats like PDF, PNG, and JPG. So download this PPT presentation design and impress the bosses with your leadership skills.</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier10,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData10)->save();

        $pageIdentifier11 = 'powerpoint-presentation-design-services/portfolio/digital-technology-adoption-action-plan';
        $pageData11 = [
            'title' => 'Digital Technology Action Plan PPT Template',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Digital Technology Action Plan PPT Template - SlideTeam',
            'meta_keywords' => 'Digital Technology Adoption Action Plan',
            'meta_description' => 'Download our digital technology action plan PPT template to create stunning presentations. Use this PowerPoint design to customize your content in one go!',
            'identifier' => $pageIdentifier11,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>Digital Technology Action Plan PPT Template</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Digital Technology/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Digital Technology/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Digital Technology/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Digital Technology/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Digital Technology/Slide5.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Digital Technology/Slide6.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Introducing our eye-catching digital technology action plan PowerPoint template. With the aid of this PPT layout, you can educate your audience about the concept of digital technology. By using this PowerPoint design, you can create a solid strategy that helps increase your IT framework and different processes. Employ this stunning PPT template to portray the steps that improve your IT skill set. Our digital technology PowerPoint template comes with enough textroom for you to jot down the valuable content. This PPT design includes attractive images and appealing colors that beautify your presentation. You can also edit this PowerPoint template as per your requirement. Take advantage of this PPT layout to create a digital technology plan that helps achieve your business goals. By utilizing our engaging PowerPoint design, you can define your recent milestones, upcoming projects, and project deliverables. The best part is, Our PPT design is available in both standard and widescreen. So hurry up and grab the PPT template now!</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier11,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData11)->save();

        $pageIdentifier12 = 'powerpoint-presentation-design-services/portfolio/debt-raising-pitch';
        $pageData12 = [
            'title' => 'Debt Raising Pitch',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Impressive Debt Raising Pitch PPT Design - SlideTeam',
            'meta_keywords' => 'Debt Raising Pitch PPT',
            'meta_description' => 'Introducing our eye-catching debt raising pitch PowerPoint template. You can download this PPT design and impress your potential investors without any hassle.',
            'identifier' => $pageIdentifier12,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>Debt Raising Pitch PPT Design</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Debt Raising pitch/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Debt Raising pitch/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Debt Raising pitch/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Debt Raising pitch/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Debt Raising pitch/Slide5.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Debt Raising pitch/Slide6.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Presenting our professional-looking debt raising pitch PowerPoint template for your business presentation. You can use this readily available PPT layout to gain your prospects’ trust. With the aid of this pre designed PowerPoint design, you can let your audience know the concept of equity financing. Employ our engaging PPT template to enhance the growth of your startup. You can use this debt raising pitch PPT design to create an outstanding clientele that impresses your current and future clients. Take advantage of this easily available PowerPoint layout to build an astounding fundraising toolkit. You can take the help of our PPT design to create a killer pitch deck for your potential investors. There are several high-grade images in this PowerPoint template that enhance the look and feel of your pitch deck presentation. You can change the colors, fonts, and background images of the PPT layout and personalize it according to your needs. So hurry up and download our intuitively designed debt raising pitch PPT template.</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier12,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData12)->save();

        $pageIdentifier13 = 'powerpoint-presentation-design-services/portfolio/cost-optimization';
        $pageData13 = [
            'title' => 'Cost Optimization',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Eye-Catching Cost Optimization PPT Template - SlideTeam',
            'meta_keywords' => 'Cost Optimization PPT Templates',
            'meta_description' => 'Get access to our ready made cost negotiation PowerPoint template. You can deploy this PPT design and save it in various file formats like PDF, PNG, and JPG.',
            'identifier' => $pageIdentifier13,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>Cost Optimization PPT Template</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/business women in bargening/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/business women in bargening/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/business women in bargening/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/business women in bargening/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/business women in bargening/Slide5.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/business women in bargening/Slide6.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Are you starting a business and looking for ways to keep costs low? Then, look no further! SlideTeam brings you a cost optimization PowerPoint template that you can use immediately. With this PPT design, you can easily create a presentation that outlines your costs and what you are willing to negotiate. By using this cost negotiation PPT template, you can easily outline your bargaining position and make the case for why you should get the best price possible. With our pre designed PowerPoint layout, you\'ll be able to outline the basics of cost negotiation, identify potential negotiating points, and develop a strategy for reaching an agreement. This PPT design will help you understand the different factors that go into cost negotiations, so you can get the best deal for your business. Our PPT template is completely customizable, so you can make it fit your specific needs. So don\'t wait any longer – download our cost negotiation PPT template today!</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier13,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData13)->save();

        $pageIdentifier14 = 'powerpoint-presentation-design-services/portfolio/business-strategy-discussion';
        $pageData14 = [
            'title' => 'Conducting Meeting to Make Strategic Plans',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Business Strategy Discussion PPT Template - SlideTeam',
            'meta_keywords' => 'Strategy Discussion PowerPoint Templates',
            'meta_description' => 'Download our 100% editable business strategy discussion PowerPoint template. Use this impressive PPT design to create a solid plan for your company’s growth.',
            'identifier' => $pageIdentifier14,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>Business Strategy Discussion PPT Template</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Conductin Meeting to Make/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Conductin Meeting to Make/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Conductin Meeting to Make/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Conductin Meeting to Make/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Conductin Meeting to Make/Slide5.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Marketing/Conductin Meeting to Make/Slide6.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Maintain focus and productivity within your workplace by utilizing our business strategy discussion PowerPoint template. Use this PPT layout to create a fool-proof plan that helps your business grow. You can employ this PowerPoint design to analyze the current performance of the organization. With the aid of our professional-looking PPT template, you can define your company’s future objectives in a systematic manner. By using this easily available PowerPoint design, you can define your corporate strategy to acquire business goals. Our business strategy PPT template comes with ample space wherein you can add the information that you want to convey to the audience. Take advantage of this pre designed PowerPoint layout to portray the core competencies of your firm. By using our eye-catching PPT template, you can discuss the challenges that obstruct your organization’s growth. This ready made PowerPoint layout can be used by entrepreneurs to increase their decision-making ability. So download our business strategy PPT template and make your business workflow smooth.</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier14,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData14)->save();

        $pageIdentifier15 = 'powerpoint-presentation-design-services/portfolio/information-technology-solutions';
        $pageData15 = [
            'title' => 'Information Technology Solution',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Information Technology PPT Template - SlideTeam',
            'meta_keywords' => 'Information Technology Solution PowerPoint Templates',
            'meta_description' => 'Employ our information technology PPT template to deliver stunning IT presentations without any hassle. So move a step ahead and download this PowerPoint design now!',
            'identifier' => $pageIdentifier15,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>Information Technology PPT Template</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Technology/Information Technology/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Technology/Information Technology/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Technology/Information Technology/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Technology/Information Technology/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Technology/Information Technology/Slide5.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Enhance your company’s operational efficiency by using our readily available information technology solution PowerPoint template. With the aid of this PPT design, you can create amazing IT presentations without any hassle. This fully editable technology solution PowerPoint template comes with plenty of space wherein you can jot down the content as per your needs. Take advantage of this PPT template and explain to the employees how to improve productivity with increased automation. Our attractive PowerPoint layout allows you to plan and execute different information technology objectives in a systematic manner. Incorporate this impressive PPT template in your presentation to let your audience know how IT infrastructure helps transform business processes and make them smooth. Our unique information technology solution PPT layout comes with appealing colors and compelling information that grabs your audiences’ attention instantly. So move a step ahead and download this fantastic IT solution PowerPoint presentation template now!</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier15,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData15)->save();

        $pageIdentifier16 = 'powerpoint-presentation-design-services/portfolio/automobile-cyber-security';
        $pageData16 = [
            'title' => 'Cyber Security',
            'page_layout' => '1column-design-services-cms',
            'meta_title' => 'Automobile Cyber Security PPT Template - SlideTeam',
            'meta_keywords' => 'Automobile Cyber Security PPT',
            'meta_description' => 'Automobile Cyber Security PPT Template - SlideTeam',
            'identifier' => $pageIdentifier16,
            'content_heading' => '',
            'content' => '<!--  Banner Slider Start --> <div class="portfolio_main_wrapper portf_new"> <div class="container clearfix"> <div class="portfolio_custom_main"> <h1>Automobile Cyber Security PPT Template</h1> <!-- Slider --> <style> .mySlides {display:none} </style> <div class="w3-content" style="max-width:800px"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Technology/Cyber Security/Slide1.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Technology/Cyber Security/Slide2.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Technology/Cyber Security/Slide3.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Technology/Cyber Security/Slide4.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Technology/Cyber Security/Slide5.JPG"}}" style="width:100%"> <img class="mySlides" src="{{view url="Magento_Cms::images/design_services_pages/portfolio/Technology/Cyber Security/Slide6.JPG"}}" style="width:100%"> </div> <div class="w3-center"> <div class="w3-section"> <button class="w3-button w3-light-grey Portf_left_btn" onclick="plusDivs(-1)">❮ </button> <button class="w3-button w3-light-grey Portf_right_btn" onclick="plusDivs(1)"> ❯</button> </div> </div> <!-- Sllider End --> <p class="portf_sub_heading">Presenting our professional-looking automobile cyber security PowerPoint template for all your presentation needs. You can deploy this stunning PPT layout to provide your audience with a brief overview of automobile cyber security. With the aid of our easily accessible PowerPoint template, you can explain to the viewers why protecting your automotive electronic system is important. By using this pre designed PPT template, you can portray the steps that help secure the vehicles. This automobile cyber security PowerPoint layout comes with plenty of space wherein you can tweak the content as per your requirement. By using this PPT template, you can define the process of vehicle logistic networks. Employ our PowerPoint layout to describe the cybersecurity challenges or issues in vehicular communications. You can use this magnificent PPT template and deliver an outstanding presentation on automobile cyber security. The PPT template is completely editable, allowing you to edit the changes as per your needs. So grab our unique automobile cyber security PowerPoint layout and get your work started!</p> </div> </div> </div> <!--form start --> {{block class="Magento\Framework\View\Element\Template" name="portfolio" template="Tatva_Portfolio::portfolio.phtml"}} <!--form end --> <!-- next_prev start --> {{block class="Tatva\Portfolio\Block\Navigation" name="pre_next" template="Tatva_Portfolio::pre_next.phtml"}} <!-- next_prev end -->',
            'url_key' => $pageIdentifier16,
            'is_active' => 1,
            'stores' => [0], // store_id comma separated
            'sort_order' => 0
        ];
        $this->pageFactory->create()->setData($pageData16)->save();
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