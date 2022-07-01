<?php
namespace Magento\RecurringPayment\Ui\DataProvider;
  
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutFactory;
  
class ProductForm extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;
  
    /**
     * @var RequestInterface
     */
    protected $request;
  
    /**
     * @var LayoutFactory
     */
    private $layoutFactory;
  
    public function __construct(
        LocatorInterface $locator,
        RequestInterface $request,
        LayoutFactory $layoutFactory
    ) {
        $this->locator = $locator;
        $this->request = $request;
        $this->layoutFactory = $layoutFactory;
    }
  
    public function modifyMeta(array $meta)
    {
        if ($this->getProductType() != "virtual") {
            $meta["recurring-payment"] = [
                "arguments" => [
                    "data" => [
                        "config" => [
                            "componentType" => "fieldset",
                            "collapsible" => false,
                            "sortOrder" => 1,
                            'opened' => false,
                            'canShow' => false,
                            'visible' => false
                        ]
                    ]
                ]
            ];
        }
        return $meta;
    }
  
    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }
  
    /**
     * Get product type
     *
     * @return null|string
     */
    private function getProductType()
    {
        return (string)$this->request->getParam('type', $this->locator->getProduct()->getTypeId());
    }
}
