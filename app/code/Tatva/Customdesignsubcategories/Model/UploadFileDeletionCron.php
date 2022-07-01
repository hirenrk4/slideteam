<?php
namespace Tatva\Customdesignsubcategories\Cron;

class UploadFileDeletionCron
{
	protected $_logger;
	public function __construct(\Psr\Log\LoggerInterface $logger) 
	{
		$this->_logger = $logger;
	}
	public function execute()
	{
		$this->_logger->debug('Cron run successfully');
		return $this;
	}
}