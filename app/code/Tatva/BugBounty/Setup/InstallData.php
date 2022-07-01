<?php

namespace Tatva\BugBounty\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
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
        \Magento\Cms\Model\PageFactory $pageFactory
    )
    {
        $this->_pageFactory = $pageFactory;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws \Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        // Load cms page by identifier
        $cmsPage = $this->_pageFactory->create()->load('bug-bounty', 'identifier');

        $pagecontent = "<section class='bug_bounty_main'>
        <div class='container'>
            <div class='bug_bounty_inner'>
                <h1>SlideTeam Bug Bounty Program</h1>
                <img src='{{view url='Tatva_BugBounty::images/bug_bounty.png'}}'>
                <p>We are always looking for ways to make Slideteam the best platform in its category. That's why we have implemented a bug bounty program, which will reward researchers who discover and report any vulnerabilities that could jeopardize security
                    on our site or apps. If you spot a vulnerability and want to report it, ensure that the qualification requirements are met. Then follow our reporting procedures for submitting an incident disclosure form.
                </p>
                <h2>Program Rules</h2>
                <p>SlideTeam is an industry standard that follows a set of well-defined and informational disclosure terms. To avoid confusion, submissions will be rated using the Bugcrowd Vulnerability Rating Taxonomy for clarity purposes only; however,
                    all information provided by SlideTeam has been verified to provide you with accurate data on your declared vulnerabilities.<br /><br /> We're always on the lookout for creative and innovative minds that can help us make SlideTeam even
                    better. If you find an issue with our platform, don't hesitate to alert them. You'll qualify for a reward if your first clue leads directly to code or configuration changes to prevent future issues from happening again. <br /><br />Our
                    Bug Bounty program pays cash incentives in three severity levels: P1, P2 & 3. Any issue rated at 4 or lower will not likely qualify to receive payment but can still be submitted. We offer standard payouts based on the values below.
                </p>

            </div>
        </div>
    </section>
    <section class='bug_bounty_table'>
        <div class='container'>
            <div class='table_content'>
                <h2>Payout Table Here</h2>
                <table>
                    <tr>
                        <th>Severity</th>
                        <th>Cash Reward</th>
                    </tr>
                    <tr>
                        <td>P1</td>
                        <td>Upto $500</td>
                    </tr>
                    <tr>
                        <td>P2</td>
                        <td>Upto $250</td>
                    </tr>
                    <tr>
                        <td>P3</td>
                        <td>Upto $100</td>
                    </tr>
                </table>
            </div>
        </div>
    </section>

    <section class='bug_bounty_guidelines'>
        <div class='container'>
            <div class='guidelines_content'>

                <ul>
                    <p>Please be sure to follow these guidelines when participating in our SlideTeam bug bounty program:</p>
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'> It's important to test only on systems listed as part of your project. Anything else is out-of-scope and should not be tested with this technique.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Creating an account for testing purposes is a great way to experiment with and understand how things work before implementing changes in your production environment.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>To be eligible for our reward, submissions must use the Security Bug Reporting Form and include specific details about your vulnerability. You can also find instructions on reproducing it using
                        SlideTeam's platform.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>We take the security of our customers' data seriously. Strict rules govern what you can and cannot do with SlideTeam. We maintain a strict policy that prohibits any actions which could affect how
                        well we operate, such as dropping unexpectedly low loads or increased overhead caused by automated tools being used inefficiently without permission from staff members here at sending headquarters safely.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Submitters will be rewarded for reporting vulnerabilities that impact the SlideTeam platforms, with submissions qualifying if they affect users or systems. Submitting may require defenders who
                        want their names listed as well.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>After 7 days, the submission will be closed if a researcher has not responded to our requests for information.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>We want to make sure that your submissions are fresh and engaging. Please include a video or screenshot proof-of-concept for us to take notice. Do not share these files publicly, as they should
                        only be used with the researcher's approval.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Violation of our SlideTeam programs disclosure policy may result in enforcement action.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Please note that any violation of these rules may result in the invalidation of submissions and termination from participation.</li>

                </ul>
            </div>
        </div>
    </section>
    <section class='tasting_targets'>
        <div class='container'>
            <div class='tasting_targets_custom'>
                <h2>Testing Targets</h2>
                <p> As part of our bug bounty program, all URLs hosted under www.slideteam.net are included within the scope for testing, and we ask that you keep the following things in mind as this is a production environment. So it is crucial to ensure
                    that when performing any tests, it does not affect any live traffic or cause unwanted issues with user experience on either side. </p>
                <img src='{{view url='Tatva_BugBounty::images/test_targets.png'}}' alt='image'>

                <ul>
                    <p>The few things that you need to keep in mind are:</p>
                    <li> You should not use vulnerabilities to access, modify or harm the data that does not belong solely in your possession.</li>
                    <li>Vulnerabilities are a common target of hackers, but it's important to demonstrate them only if you're working with us.</li>
                    <li>To prevent any service disruption, please do not conduct network-level or DDoS attacks against our systems.</li>
                    <li>The best way to maintain the integrity of your environment is by not conducting any tests that will impact its performance. It means no aggressive scanning or scripting.</li>
                    <li>Please do not target other SlideTeam users or customers. All of our client's information is out-of-scope and should never be used for marketing purposes under any circumstances.</li>

                </ul>
            </div>
        </div>
    </section>
    <section class='bounty_notes'>
        <div class='container'>
            <div class='bounty_notes_custom'>
                <ul>
                    <p>Please also note that the following findings are specifically excluded from the bounty:</p>
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Findings identified through physical testing of office access (e.g., open doors, tailgating).</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>The use of social engineering techniques in attacks is a common tactic. These methods include phishing and vishing.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>It includes anything relating to applications or systems not listed in the 'Testing Targets' section.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Unexplained bugs and mistakes in the user interface.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Network-level Denial of Service (DoS/DDoS) vulnerabilities.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Descriptive error messages (e.g., Stack Traces, application or server errors).</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>HTTP 404 codes/pages or other HTTP non-200 codes/pages.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Fingerprinting/banner disclosure on common/public services.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Disclosure of known public files or directories (e.g., robots.txt).</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>CSRF on forms available to anonymous users (e.g., the contact form) and the Login/Logout URL.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Presence of application or web browser 'autocomplete' or 'save password' functionality.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Lack of HTTPOnly cookie flags.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Lack of Security Speedbump when leaving the site.</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Login Brute Force (unless the CAPTCHA can be bypassed)</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>OPTIONS HTTP method enabled</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Missing X-Content-Type-Options Header</li><br />
                    <li><img src='{{view url='Tatva_BugBounty::images/tick.png'}}' alt='icon'>Use of SHA-1 SSL Certificate and support for TLS 1.0</li>
                </ul>
            </div>
        </div>
    </section>
    <section class='bug_report'>
        <div class='container'>
            <div class='bug_report_custom'>
                <h2>Submitting a Bug Report</h2>
                <p>To ensure the safety of our users, all submissions must be made using this Security Bug Reporting Form. You will be required to explain the bug, its effect on various users, and how it can easily be reproduced. You must also provide any
                    PoC code that may help reproduce this issue or capture an image from which one could trigger the occurrence of these bugs. Uploading files that prove the vulnerability exists is a great way to help ensure your issue gets fixed quickly.
                    To help reproduce the vulnerability, add as much information to your description so that people can understand what they are experiencing. It will allow for more reproductions and, ultimately, a faster bounty payout. It is a great
                    way to quickly reproduce the issue and get your submission through the review process.</p>
                <p class='bug_report_p'> The following information will be required for all valid bug submissions.</p>
                <ul>
                    <li><b>Caption: </b> This report describes the type of bug found, where it was spotted, and its overall impact on our systems. For example, 'Remote File Inclusion in Resume Upload Form allows remote code execution' is much more descriptive
                        and helpful than 'RFI Injection found.'</li>
                    <li><b>Target:</b> The Target field specifies the particular target that has been affected by your bug.</li>
                    <li><b>Bug Type:</b> When you find a bug, the type of bugs must be identified. Different types present different risks and needs from organizations, so your choice will help us understand how dangerous or valuable their potential situation
                        could be.
                    </li>
                    <li><b>Bug URL:</b> The Bug URL is a unique string that identifies where you found any bugs in your application.</li>
                    <li><b>Proof of concept: </b> Your report must provide clear and detailed instructions for reproducing the experiment so that other researchers can easily replicate it.</li>
                    <li><b>Additional info:</b> You can provide context for your discovery by explaining what you found and describing the impact risk involved in its acquisition.</li>
                    <li><b>Screenshots:</b> The evidence shows that this vulnerability is serious and deserves attention.</li>

                </ul>
            </div>
        </div>
    </section>
    <section class='bug_reporting_form'>
        <div class='container'>
            <div class='bug_form_custom'>
                <h2>Security Bug Reporting Form</h2>
                <p>Our security team will rate all your submission and will reply you back within 2-3 working days.</p>
                {{block class='Magento\Framework\View\Element\Template'  template='Tatva_BugBounty::form.phtml'}}
                <div class='bug_question'>
                    <p>Questions? Send an email to <a href='mailto:support@slideteam.net'>support@slideteam.net</a></p>
                </div>
            </div>
        </div>
        </div>
        </div>
        </div>
    </section>";
        // Create CMS Page
        if (!$cmsPage->getId()) {
            $cmsPageData = [
                'title' => 'Slideteam Bug Bounty Program',
                'identifier' => 'bug-bounty',
                'content' => $pagecontent,
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ];

            $this->_pageFactory->create()->setData($cmsPageData)->save();
        }
    }
}