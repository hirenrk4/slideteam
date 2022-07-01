<?php
namespace Tatva\Sentence\Controller\Adminhtml\Imports;
use Tatva\Sentence\Model\Sentence;

class generateDescription extends \Magento\Backend\App\Action
{
 protected $sentence;

 protected $_messageManager;


 public function __construct(
   \Magento\Backend\App\Action\Context $context,
   Sentence $sentence,
   \Magento\Framework\Message\ManagerInterface $messageManager
   ) {
  $this->sentence = $sentence;
  $this->_messageManager = $messageManager;
    parent::__construct($context); // If your class doesn't have a parent, you don't need to do this, of course.
  }

  public function execute()
  {

    $this->sentence->generateDescription();
    $this->_messageManager->addSuccessMessage(__('Descriptions are successfully generated.'));
    $this->_redirect('admin_sentence/index/index');
    return;
  }


}
