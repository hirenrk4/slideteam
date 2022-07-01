<?php
/**
 * Copyright (c) 2015 S.L.I. Systems, Inc. (www.sli-systems.com) - All Rights Reserved
 * This file is part of Learning Search Connect.
 * Learning Search Connect is distributed under a limited and restricted
 * license – please visit www.sli-systems.com/LSC for full license details.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE. TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, IN NO
 * EVENT WILL SLI BE LIABLE TO YOU OR ANY OTHER PARTY FOR ANY GENERAL, DIRECT,
 * INDIRECT, SPECIAL, INCIDENTAL OR CONSEQUENTIAL LOSS OR DAMAGES OF ANY
 * CHARACTER ARISING OUT OF THE USE OF THE CODE AND/OR THE LICENSE INCLUDING
 * BUT NOT LIMITED TO PERSONAL INJURY, LOSS OF DATA, LOSS OF PROFITS, LOSS OF
 * ASSIGNMENTS, DATA OR OUTPUT FROM THE SERVICE BEING RENDERED INACCURATE,
 * FAILURE OF CODE, SERVER DOWN TIME, DAMAGES FOR LOSS OF GOODWILL, BUSINESS
 * INTERRUPTION, COMPUTER FAILURE OR MALFUNCTION, OR ANY AND ALL OTHER DAMAGES
 * OR LOSSES OF WHATEVER NATURE, EVEN IF SLI HAS BEEN INFORMED OF THE
 * POSSIBILITY OF SUCH DAMAGES.
 */

namespace SLI\Feed;

use Psr\Log\LoggerInterface;
use SLI\Feed\Helper\GeneratorHelper;
use SLI\Feed\Helper\XmlWriter;
use SLI\Feed\Logging\LoggerFactoryInterface;
use SLI\Feed\Model\Generators\GeneratorInterface;

/**
 * Generator for a full store feed.
 *
 * @package SLI\Feed
 */
class FeedGenerator
{
    /**
     * Logger factory
     *
     * @var LoggerFactoryInterface
     */
    protected $loggerFactory;

    /**
     * List of generators.
     */
    protected $generators;

    /**
     * Feed generation helper
     *
     * @var GeneratorHelper
     */
    protected $generatorHelper;
    protected $resourceconnection;
    /**
     * @param array $generators
     * @param LoggerFactoryInterface $loggerFactory
     * @param GeneratorHelper $generatorHelper
     */
    public function __construct(array $generators, LoggerFactoryInterface $loggerFactory, GeneratorHelper $generatorHelper,
        \Magento\Framework\App\ResourceConnection $resourceconnection,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->generators = $generators;
        $this->loggerFactory = $loggerFactory;
        $this->generatorHelper = $generatorHelper;
        $this->resourceconnection = $resourceconnection;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Generate a feed for a certain store ID.
     *
     * @param int $storeId
     * @param string $filename The filename to write to
     * @return bool
     * @throws \Exception
     */
    public function generateForStoreId($storeId, $filename)
    {
        /** @var GeneratorInterface $generator */
        $generator = null;
        $result = true;
        $totalStart = time();
        $logger = $this->loggerFactory->getStoreLogger($storeId);

        $this->generatorHelper->logSystemSettings($logger);

        try {

            $today_count = $this->scopeConfig->getValue('feed_count/sli_feed/today_count', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $this->configWriter->save('feed_count/sli_feed/yesterday_count', $today_count);

            $logger->info(sprintf('Starting Feed generation for storeId [%s], filename: %s', $storeId, $filename));

            $xmlWriter = new XmlWriter($filename);

            foreach ($this->generators as $generator) {
                $start = time();
                if (!$result = $generator->generateForStoreId($storeId, $xmlWriter, $logger)) {
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
        
        $today = $this->resourceconnection->getConnection()->fetchAll('SELECT `value` FROM core_config_data where `path` LIKE "feed_count/sli_feed/today_count"');
        $today_count = $today[0]['value'];

        $yesterday = $this->resourceconnection->getConnection()->fetchAll('SELECT `value` FROM core_config_data where `path` LIKE "feed_count/sli_feed/yesterday_count"');

        $yesterday_count = $yesterday[0]['value'];

        $diff = $yesterday_count - $today_count;
        
        if ($diff > 5)
        {
            $mail = new \Zend_Mail();
            $message = "Please find below count of SLI Feed Genration";
            $message .= "<br/><br/>Yesterday Count :: ".$yesterday_count;
            $message .= "<br/><br/>Today Count :: ".$today_count;
            $message .= "<br/><br/>Difference :: ".$diff;
            $mail->setFrom("support@slideteam.net",'SlideTeam Support');
            $mail->setSubject('Today SLI Feed Generate Count Less Then Yesterday');
            $mail->setBodyHtml($message);

            $to_array = $this->scopeConfig->getValue('feed_count/feed_email/to_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $to_email = explode(",",$to_array);
            $cc_array = $this->scopeConfig->getValue('feed_count/feed_email/cc_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $cc_email = explode(",",$cc_array);

            $send = 0;
                if(!empty($to_email))
                {
                    $mail->addTo($to_email);
                    $send = 1;
                }
                if(!empty($cc_email))
                {
                    $mail->addCc($cc_email);
                }
                
                try
                {
                    if($send) :
                        $mail->send();
                    endif;
                }catch(Exception $e)
                {
                    echo $e->getMessage();
                }
        }

        return $result;
    }

}
