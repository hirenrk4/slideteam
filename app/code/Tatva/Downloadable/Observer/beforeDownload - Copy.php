<?php
namespace Tatva\Downloadable\Observer;
use Magento\Framework\Event\ObserverInterface;

class afterDownload implements ObserverInterface
{
	public function __construct()
	{
	}
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		die('Before');
	}
}