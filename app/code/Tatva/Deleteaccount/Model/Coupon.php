<?php
namespace Tatva\Deleteaccount\Model;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\Framework\Exception\InputException;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Api\Data\RuleInterfaceFactory;

class Coupon
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CouponRepositoryInterface
     */
    protected $couponRepository;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var CouponInterface
     */
    protected $coupon;
    protected $_dateFactory;

    public function __construct(
        \Tatva\CustomAttribute\Model\CustomAttribute $customAttribute, 
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Magento\SalesRule\Model\Rule $shoppingCartPriceRule,
        CouponRepositoryInterface $couponRepository,
        RuleRepositoryInterface $ruleRepository,
        RuleInterfaceFactory $rule,
        CouponInterface $coupon,
        LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->customAttribute = $customAttribute;
        $this->_dateFactory = $dateTimeDateTimeFactory;
        $this->shoppingCartPriceRule = $shoppingCartPriceRule;
        $this->couponRepository = $couponRepository;
        $this->ruleRepository = $ruleRepository;
        $this->rule = $rule;
        $this->coupon = $coupon;
        $this->logger = $logger;
        $this->_timezone = $timezone;
    }

    /**
     * Create Rule
     *
     * @return void
     */
    public function createRule(int $loginCustomerid)
    {
        $coupon = $this->RandomString();

    	$nowGMTDate = $this->_dateFactory->create()->gmtDate("Y-m-d H:i:s");
        $localeStartDate = $this->_timezone->date(new \DateTime($nowGMTDate))->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

    	$toGMTDate = $this->_dateFactory->create()->gmtDate("Y-m-d H:i:s", strtotime('+24 hours', strtotime($nowGMTDate)));
        $localeEndDate = $this->_timezone->date(new \DateTime($toGMTDate))->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

        $this->shoppingCartPriceRule->setName('40% discount')
            ->setDescription("40% discount")
            ->setFromDate($localeStartDate)
        	->setToDate($localeEndDate)
            ->setIsAdvanced(true)
            ->setStopRulesProcessing(false)
            ->setDiscountQty(20)
            ->setCustomerGroupIds(array('0','1','2','3',))
            ->setUsesPerCustomer('1')
            ->setWebsiteIds([1])
            ->setIsRss('0')
            ->setUsesPerCoupon(1)
            ->setTimesUsed('1')
            ->setDiscountStep(0)
            ->setCouponType('2')
            ->setSimpleAction('by_percent')
            ->setDiscountAmount(40)
            ->setIsActive(true);

        try 
        {
                $this->shoppingCartPriceRule->save();
                $salesRuleId = $this->shoppingCartPriceRule->getId();
                $couponcode = $this->coupon;
                $couponcode->setCode($coupon)
                    ->setIsPrimary(1)
                    ->setRuleId($salesRuleId);
                $couponcode = $this->couponRepository->save($couponcode);

                $couponCustomer = $this->customAttribute;
                $couponCustomer->setSalesRuleId($salesRuleId)->setCustomerId($loginCustomerid)->setCouponCode($coupon)->save();
                //echo('success');
                //echo($coupon);
        } catch (Exception $exception) 
        {
            $this->logger->error($exception->getMessage());
        }
    }

    function RandomString()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $length = strlen($characters)-1;
        $randstring = '';
        for ($i = 0; $i < 5; $i++) {
            $randstring .= $characters[rand(0, $length)];
        }
        return $randstring;
    }
}