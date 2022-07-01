<?php
namespace Tatva\Catalog\Cron;

class Newcategoryadded
{
    protected $categoryCollectionFactory;

    protected $categoryFactory;

    protected $categoryModel;

    public function __construct(       
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {     
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->_scopeConfig  = $scopeConfig; 
    }

    public function execute()
    {
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->getSelect()->where('created_at >= DATE_SUB(NOW(),INTERVAL 1 DAY)');

        if(!empty($categoryCollection->getData()))
        {
            $message = '<p>Below categories added in the system in last 24 hours</p>';
            $message .= '<table style="border: 1px solid black;border-collapse: collapse;text-align: left;"><tr><th style="border: 1px solid black;">Category Name</th><th style="border: 1px solid black;">Parent Category Tree</th></tr>';
            $text = '';
            $path = '';
            foreach ($categoryCollection->getData() as $cat) {
                $id = $cat['entity_id'];
                $category = $this->getCategoryData($id);
                $mainCategory = $category->getName();

                if($category->getLevel() > 1)
                {
                    $firstparentId = $category->getParentId();
                    $firstCategory = $this->getCategoryData($firstparentId);
                    $firstCategoryName = $firstCategory->getName();
                    $path = $firstCategoryName;
                    if($firstCategory->getLevel() > 1)
                    {
                        $secondparentId = $firstCategory->getParentId();
                        $secondCategory = $this->getCategoryData($secondparentId);
                        $secondCategoryName = $secondCategory->getName();
                        $path = $secondCategoryName .' > '.$path;   
                        if($secondCategory->getLevel() > 1)
                        {
                            $thirdparentId = $secondCategory->getParentId();
                            $thirdCategory = $this->getCategoryData($thirdparentId);
                            $thirdCategoryName = $thirdCategory->getName();
                            $path = $thirdCategoryName .' > '.$path;
                        }
                    }
                }
                $text = '<tr><td style="border: 1px solid black;">'.$mainCategory.'</td><td style="border: 1px solid black">'.$path.'</td></tr>';
                $message .= $text;
            }
            $message .= '</table>';
            $mail = new \Zend_Mail();
            $mail->setFrom("support@slideteam.net",'SlideTeam Support');
            $mail->setSubject('New category added in SlideTeam');
            $mail->setBodyHtml($message);

            $to_email = explode(',',$this->_scopeConfig->getValue('button/newcategoryadd/to_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $cc_email = explode(',',$this->_scopeConfig->getValue('button/newcategoryadd/cc_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));

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
    }
    public function getCategoryData($id)
    {
        return $this->categoryFactory->create()->load($id);
    }
}