<?php
namespace Tatva\Customer\Plugin\Model;


use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Http\Context;

/**
 * Class Session
 */
class Session
{

    /**
     * @var Context
     */
    private $httpContext;

    /**
     * Session constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->httpContext = $context;
    }

    /**
     * Check http context to know if user is really logged in
     *
     * @param CustomerSession $subject
     * @param bool $isLoggedIn
     * @return bool
     */
    public function afterIsLoggedIn(CustomerSession $subject, $isLoggedIn)
    {
        return $isLoggedIn ?: $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }
}