<?php
namespace Tatva\SLIFeed;

use Psr\Log\LoggerInterface;
use SLI\Feed\Helper\GeneratorHelper;
use SLI\Feed\Helper\XmlWriter;
use SLI\Feed\Logging\LoggerFactoryInterface;
use SLI\Feed\Model\Generators\GeneratorInterface;

class FeedGenerator extends \SLI\Feed\FeedGenerator
{
    
    public function __construct(array $generators,
            \SLI\Feed\Logging\LoggerFactoryInterface $loggerFactory, 
            \SLI\Feed\Helper\GeneratorHelper $generatorHelper,
            \Magento\Framework\App\ResourceConnection $resourceconnection,
            \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) {
        parent::__construct($generators, $loggerFactory, $generatorHelper, $resourceconnection, $configWriter, $scopeConfig);
    }
    
    public function updateForStoreId($storeId, $filename)
    {
        /** @var GeneratorInterface $generator */
        $generator = null;
        $result = true;
        $totalStart = time();
        $logger = $this->loggerFactory->getStoreLogger($storeId);

        $this->generatorHelper->logSystemSettings($logger);

        try {
            $logger->info(sprintf('Starting Feed generation for storeId [%s], filename: %s', $storeId, $filename));

            $xmlWriter = new XmlWriter($filename);

            foreach ($this->generators as $generator) {
                $start = time();
                if (!$result = $generator->updateForStoreId($storeId, $xmlWriter, $logger)) {
                    break;
                }
                $end = time();
                $logger->debug(sprintf('[%s] %s: %s; duration: %s sec, start: %s, end: %s',
                    $storeId, get_class($generator), $result ? 'success' : 'failed', $end - $start, $start, $end
                ));
            }

            $xmlWriter->closeFeed();
        } catch (\Exception $e) {
            $logger->error(sprintf('[%s] %s feed generation failed: %s',
                $storeId, get_class($generator), $e->getMessage()),
                ['exception' => $e]
            );

            if ('cli' != php_sapi_name()) {
                // rethrow exception so that it can be handled by FeedManager and show it in UI
                throw new \Exception($e);
            }

            $result = false;
        }

        $totalEnd = time();
        $logger->info(sprintf('[%s] Finish Feed generation: %s, duration: %s sec, start: %s, end: %s',
            $storeId, $result ? 'success' : 'failed', $totalEnd - $totalStart, $totalStart, $totalEnd
        ));

        return $result;
    }
    
}