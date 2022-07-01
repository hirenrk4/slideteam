<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Emarsys
 * @copyright  Copyright (c) 2017 Emarsys. (http://www.emarsys.net/)
 */
namespace Emarsys\Emarsys\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger as EmarsysLogger;

/**
 * Class Handler
 * @package Emarsys\Emarsys\Logger
 */
class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = EmarsysLogger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/emarsys_contact_sync.log';
}