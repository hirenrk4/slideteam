<?php
namespace Tatva\Notification\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Action extends Column
{
    const URL_PATH_EDIT = 'notification/index/edit';
    const URL_PATH_DELETE = 'notification/index/delete';

    /**
     * UrlBuilder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     *  EditUrl
     *
     * @var string
     */
    protected $editUrl;

    /**
     * Construct
     *
     * @param ContextInterface   $context            context
     * @param UiComponentFactory $uiComponentFactory uiComponentFactory
     * @param UrlInterface       $urlBuilder         urlBuilder
     * @param array              $components         components
     * @param array              $data               data
     * @param string             $editUrl            editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['notification_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            $this->editUrl,
                            ['id' => $item['notification_id']]
                        ),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::URL_PATH_DELETE,
                            ['id' => $item['notification_id']]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'message' => __(
                                'Are you sure you wan\'t to delete a record?'
                            )
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
