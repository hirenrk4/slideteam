<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Post;

use \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper;
use \FishPig\WordPress\Model\Post as PostCollection;

class Featured extends \FishPig\WordPress\Block\Post
{
  /**
   * Cache for post collection
   *
   * @var PostCollection
   */
  protected $_postCollection = null;

  protected $_resourceConnection;
  
  /*
   * Returns the collection of posts
   *
   * @return 
   */
  public function __construct(
          \Magento\Framework\View\Element\Template\Context $context,
          \FishPig\WordPress\Model\Context $wpContext,
          PostCollection $postCollection,
          \FishPig\WordPress\Model\ResourceConnection $resourceConnection,
          \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
          array $data = array()
    ) {
      parent::__construct($context, $wpContext, $data);
        $this->_postCollection = $postCollection;
        $this->_resourceConnection = $resourceConnection;
        $this->_scopeConfig = $scopeConfig;
  }

  public function configData($path){
        return $this->_scopeConfig->getValue($path,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
  }
  
  public function getFeaturedPostId()
  {
    $params = $this->getRequest()->getParams();
    $table = $this->_resourceConnection->getTable('options');
    
      $sql = "SELECT `option_value`  FROM `" . $table . "` AS `main_table` WHERE (`main_table`.`option_name`='custom_featured_option')";
      $data = $this->_resourceConnection->getConnection()->fetchRow($sql);
      $featured_post_id = null;
      if($data)
      {
        $unserialized_data = unserialize($data['option_value']);

        if(isset($params['lang'])){
          $params['lang'] = strtolower($params['lang']);
          if($params['lang'] == 'spanish')
          {
            $featured_post_id = $unserialized_data['list_of_feat_post_ids']['0'];
          }elseif ($params['lang'] == 'german') {
            $featured_post_id = $unserialized_data['list_of_feat_post_ids']['1'];
          }elseif ($params['lang'] == 'french') {
            $featured_post_id = $unserialized_data['list_of_feat_post_ids']['2'];
          }elseif ($params['lang'] == 'portuguese') {
            $featured_post_id = $unserialized_data['list_of_feat_post_ids']['3'];
          }else{
            $featured_post_id = end($unserialized_data['list_of_feat_post_ids']);
          }
        }else{
          $featured_post_id = end($unserialized_data['list_of_feat_post_ids']);
        }
      }
      return $featured_post_id;
  }


    public function getFeaturedPost()
    {   
      $collection = $this->_postCollection->load($this->getFeaturedPostId());
      return $collection;
    }
  /*
   * Sets the parent block of this block
   * This block can be used to auto generate the post list
   *
   * @param AbstractWrapper $wrapper
   * @return $this
   */
  
}
