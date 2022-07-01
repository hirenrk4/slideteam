<?php
/*
 *
 */
namespace FishPig\WordPress\Controller\Index;

class Index extends \FishPig\WordPress\Controller\Action
{   
    protected $_scopeConfig;
    
    public function __construct(
            \Magento\Framework\App\Action\Context $context, 
            \FishPig\WordPress\Model\Context $wpContext,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Framework\App\Response\RedirectInterface $redirect,
            \Magento\Framework\HTTP\Client\Curl $curl,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \FishPig\WordPress\Model\Url $wpUrlBuilder,
            \Magento\Framework\Session\SessionManagerInterface $sessionManager,
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
            \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    ) {
        parent::__construct($context, $wpContext ,$sessionManager,$cookieMetadataFactory,$cookieManager);
        $this->_customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->_curl = $curl;
        $this->_scopeConfig = $scopeConfig;
        $this->wpUrlBuilder = $wpUrlBuilder;
    }
    
    protected function _getEntity()
    {
        return $this->getFactory('Index')->create();
    }
    
    public function execute()
    {
        $previous_url = $this->redirect->getRefererUrl();
        $captch= $this->getRequest()->getPostValue('captcha');
        
        $email = $this->_customerSession->getCustomer()->getEmail();
        $author = $this->_customerSession->getCustomer()->getName();

        $_data = $this->getRequest()->getPost();

        if(!isset($_data['email']) && !isset($_data['author']))
        {
            $_data['email'] = $email;
            $_data['author'] = $author;
        }

        if(isset($captch['blog_captcha_form']))
        {
                $captcha = $captch['blog_captcha_form'];
        }
        else
        {
                $captcha = "";
        }


        $_data['comment_author_IP'] = $_SERVER['REMOTE_ADDR'];
        $comment_postdata = array();
        $comment_postdata[$_data['comment_post_ID']]['email'] = $_data['email'];
        $comment_postdata[$_data['comment_post_ID']]['author'] = $_data['author'];
        $comment_postdata[$_data['comment_post_ID']]['comment'] = $_data['comment'];
        $comment_postdata[$_data['comment_post_ID']]['comment_author_IP'] = $_data['comment_author_IP'];
        $postcatcha = $captcha;
        $data=$this->_customerSession->getData('blog_captcha_form_word');
        $this->_customerSession->setCommentData($comment_postdata);
        $fields_string="";
        // $postcatcha=$captch['blog_captcha_form'];
        $master_enable = $this->_scopeConfig->getValue('customer/captcha/master_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $master_captcha = $this->_scopeConfig->getValue('customer/captcha/master_captcha', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($data['data'] != $postcatcha && $postcatcha == $master_captcha)
        {
                if($master_enable)
                {
                        $data['data'] = $master_captcha;
                }
        }

        if($data['data'] == $postcatcha)
        {  
                $fields = $_data;

                foreach ($fields as $key => $value) 
                {
                        if($key == 'captcha')
                        {
                                foreach ($value as $key1 => $value1) 
                                {
                                        $key = $key1;
                                        $value = $value1;
                                }
                        }
                        $fields_string .= $key . '=' . $value . '&';
                }
                $final_fields_string = rtrim($fields_string, '&');

                $url = $this->wpUrlBuilder->getSiteUrl('wp-comments-post.php');

                /*Remove below line for live*/
                //$url = str_replace("https","http",$url);

                //$this->_curl->setOption(CURLOPT_POST, count($fields));
                $this->_curl->setOption(CURLOPT_RETURNTRANSFER, 1);
                $this->_curl->setOption(CURLOPT_SSL_VERIFYPEER, false);

                $this->_curl->post($url,$final_fields_string);

                $response = $this->_curl->getBody();

                $result_array = json_decode($response,true);
                $comment_id = $result_array['comment'];
                if(strpos($previous_url,"comment")!== false){
                        $url = explode( '?', $previous_url );
                        $previous_url = $url[0];
                }

                  $redirect_url = $previous_url . "?comment-id=$comment_id";
                $this->messageManager->addSuccess('Your review has been submitted for moderation. It will be displayed as soon as it is approved.');         
                echo $redirect_url;
                exit();
        } 
        else {
                echo "0";
                die;	
        }	   
    }
}
