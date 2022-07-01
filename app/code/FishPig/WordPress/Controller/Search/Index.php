<?php
/*
 *
 */
namespace FishPig\WordPress\Controller\Search;

/* Parent Class */
use Magento\Framework\App\Action\Action;

/* Constructor Args */
use Magento\Framework\App\Action\Context;
use FishPig\WordPress\Model\SearchFactory;

/* Misc */
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
	/**
	 * @var
	**/
	protected $searchFactory;
	
  /**
   * Constructor
   *
   * @param Context $context
   * @param PageFactory $resultPageFactory
   */
  public function __construct(Context $context, SearchFactory $searchFactory)
  {
    parent::__construct($context);
      
    $this->searchFactory = $searchFactory;
  }	
  
  public function execute()
  {
    $params = $this->getRequest()->getParams();
    $lang = '';
    if($lang = isset($params['lang']) && (!empty($params['lang']))){
        $lang = '?lang='.$params['lang'];
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl(rtrim($this->searchFactory->create()->getUrl(), "/").$lang);
    }
    else
    {
      return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($this->searchFactory->create()->getUrl());
    }

	}
}
