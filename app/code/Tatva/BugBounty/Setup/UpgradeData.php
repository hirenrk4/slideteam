<?php

namespace Tatva\BugBounty\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $_pageFactory;

    /**
     * InstallData constructor
     *
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     */
    public function __construct(
        \Magento\Cms\Model\BlockFactory  $blockFactory
    )
    {
        $this->_blockFactory  = $blockFactory;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
 
        // run the code while upgrading module to version 0.1.1 
        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $cmsBlock = $this->_blockFactory->create()->setStoreId(0)->load('bug-bounty-thank-you', 'identifier');
            
            $cmsBlockData = [
                'title' => 'Bug Bounty Thank You',
                'identifier' => 'bug-bounty-thank-you',
                'is_active' => 1,
                'stores' => [0],
                'content' => "<div class='custom-thankyou-wrapper'>
                                <div class='container  quest-response-container'>
                                    <p class='thank-you-title'>Thank You for submitting your request to SlideTeam.net</p>
                                    <p class='thank-you-description'>Our Design Team will review your request shortly, and you will receive an email acknowledgment within 24 hours.</p>
                                    <p>&nbsp;</p>
                                    <p class='thank-you-description'>We have sent a copy of the request to your email id <a href='mailto:{{var user_email_id}}'>{{var user_email_id}}</a>. If this email id is incorrect, please let us know by sending us an email at <a href='mailto:support@slideteam.net'>support@slideteam.net</a>.</p>
                                    <p>&nbsp;</p>
                                    <p>&nbsp;</p>
                                    <p class='thank-you-title'>Next step</p>
                                    <p class='thank-you-description'>We will get back to you within 24 hours if we have any clarifying questions. The best way to contact us is by sending us a mail at <a href='mailto:support@slideteam.net'>support@slideteam.net</a> and referencing the email ID that you entered with the original request. If you have any additional information or content or mockup, please send it to <a href='mailto:support@slideteam.net'>support@slideteam.net</a>.</p>
                                    <p>&nbsp;</p>
                                    <p>&nbsp;</p>
                                    <a href='{{var previous_page}}'>Go to the previous page</a>
                                </div>
                            </div>",
            ];
 
            if (!$cmsBlock->getId()) {
                $this->_blockFactory->create()->setData($cmsBlockData)->save();
            } else {
                $cmsBlock->setContent($cmsBlockData['content'])->save(); 
            }
        }
 
        $setup->endSetup(); 
    }
}