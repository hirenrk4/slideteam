<?php
namespace Tatva\Checkout\Block\Checkout;

use Magento\Framework\App\ObjectManager;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Ui\Component\Form\AttributeMapper;
use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Customer\Model\Options;

class LayoutProcessor extends \Magento\Checkout\Block\Checkout\LayoutProcessor
{
	private $attributeMetadataDataProvider;
    protected $attributeMapper;
    protected $merger;
    private $options;

    public function __construct(
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AttributeMapper $attributeMapper,
        AttributeMerger $merger
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper               = $attributeMapper;
        $this->merger                        = $merger;
    }

    private function getOptions()
    {
        //same code as Magento/Checkout/Block/LayoutProcessor::getOptions()
    }


    private function getAddressAttributes()
    {
        //same code as Magento/Checkout/Block/LayoutProcessor::getAddressAttributes()
    }

    private function convertElementsToSelect($elements, $attributesToConvert)
    {
        //same code as Magento/Checkout/Block/LayoutProcessor::convertElementsToSelect()
    }

    public function process($jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['cart_items'])) {
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['cart_items']['sortOrder'] = 0;
        }
		unset($jsLayout['components']['checkout']['children']['progressBar']);
        unset($jsLayout['components']['checkout']['children']['estimation']);
        unset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['cart_items']['children']['details']['children']['thumbnail']);
		return $jsLayout;
    }

    private function processPaymentConfiguration(array &$configuration, array $elements)
    {
        /*
        code from Magento/Checkout/Block/LayoutProcessor::processPaymentConfiguration()
        with a couple changes.
        It works when I apply the changes in code file (vendor/magento/magento-checkout/...)
        */
    }

}