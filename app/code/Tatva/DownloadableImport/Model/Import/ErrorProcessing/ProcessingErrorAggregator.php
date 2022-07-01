<?php

namespace Tatva\DownloadableImport\Model\Import\ErrorProcessing;

/**
 * Import/Export Error Aggregator class
 */
class ProcessingErrorAggregator extends \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator
{
	/**
    * @return string $validationStrategy
    */
    public function getValidationStrategy()
    {
        return $this->validationStrategy;
    }
}