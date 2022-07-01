<?php
namespace Tatva\Subscription\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tatva\Subscription\Helper\SubscriptionProduct;

class CreateSubscriptionProductCommand extends Command
{
	protected $subscriptionProductHelper;

	public function __construct(SubscriptionProduct $subscriptionProductHelper)
	{
		$this->subscriptionProductHelper = $subscriptionProductHelper;
		parent::__construct();
	}

	protected function configure()
	{
		$this ->setName('tatva:subscription:create')
		->setDescription('Create Subscription Products')
		->setDefinition($this->getOptionsList());
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('<info>Creating Subscription Products</info>');
		// $this->subscriptionProductHelper->execute();
		$output->writeln('<info>Subscription Products are created.</info>');
	}

	protected function getOptionsList()
	{
		return [];
	}
}