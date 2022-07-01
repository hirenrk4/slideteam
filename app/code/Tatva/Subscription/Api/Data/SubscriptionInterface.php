<?php

namespace Tatva\Subscription\Api\Data;

interface SubscriptionInterface
{

    const SUBSCRIPTION_HISTORY_ID = 'subscription_history_id';
    const FIRSTNAME = 'firstname';
    const LATNAME = 'lastname';
    const EMAIL = 'email';
    const SUBSCRIPTION_PERIOD = 'subscription_period';
    const DOWNLOAD_LIMIT = 'download_limit';
    const FROM_DATE = 'from_date';
    const TO_DATE = 'to_date';
    const RENEW_DATE = 'renew_date';
    const UPDATE_TIME = 'update_time';
    const STATUS = 'status_success';


    public function getId();

    public function setId($id);

    public function getFirstName();

    public function setFirstName($firstName);

    public function getLastName();

    public function setLastName($lastName);

    public function getEmail();
    
    public function setEmail($email);

    public function getPeriod();
    
    public function setPeriod($Period);

    public function getDownloadLimit();
    
    public function setDownloadLimit($downloadlimit);

    public function getFromDate();
    
    public function setFromDate($fDate);

    public function getToDate();
    
    public function setToDate($tDate);
    public function getRenewDate();
    
    public function setRenewDate($rDate);

    public function getUpdateDate();
    
    public function setUpdateDate($uDate);

    public function getStatus();
    
    public function setStatus($status);
    
}
