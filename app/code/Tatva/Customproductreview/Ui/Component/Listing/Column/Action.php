<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Customproductreview\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class DepartmentActions
 */
class Action extends Column
{
    /** Url path */
    const ROW_EDIT_URL = 'customproductreview/index/edit';

    /**
     * @var string
     */
    private $_editUrl;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::ROW_EDIT_URL
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {       
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->_urlBuilder->getUrl(
                            $this->_editUrl,
                        ['review_id' => $item['review_id']]
                    ),
                    'label' => __('Edit')
                ];
                $item[$this->getData('name')]['delete'] = [
                    'href' => $this->_urlBuilder->getUrl(
                        'customproductreview/index/delete',
                        ['object_id' => $item['review_id']]
                    ),
                    'label' => __('Delete'),
                    'confirm' => ['title'=>'Are you sure , you want to delete the task?']
                ];
            }
        }
        
        return $dataSource;
    }
}