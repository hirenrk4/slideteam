<?php

namespace Tatva\Subscription\Model\Newsletter;

use Magento\Newsletter\Model\Subscriber as SubscriberModel;

class Subscriber
{
    /**
     * @param SubscriberModel $oSubject
     * @param callable $proceed
     */
    public function aroundSendConfirmationRequestEmail(SubscriberModel $oSubject, callable $proceed) {}

    /**
     * @param SubscriberModel $oSubject
     * @param callable $proceed
     */
    public function aroundSendConfirmationSuccessEmail(SubscriberModel $oSubject, callable $proceed) {}

    /**
     * @param SubscriberModel $oSubject
     * @param callable $proceed
     */
    public function aroundSendUnsubscriptionEmail(SubscriberModel $oSubject, callable $proceed) {}
}