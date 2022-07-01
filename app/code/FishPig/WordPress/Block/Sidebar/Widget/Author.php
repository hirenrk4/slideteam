<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Sidebar\Widget;

class Author extends AbstractWidget
{
    /**
     * Cache for archive collection
     *
     * @var null|Varien_Data_Collection
     */
    protected $_authorCollection = null;
 
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, 
        \FishPig\WordPress\Model\Context $wpContext, 
        \FishPig\WordPress\Model\ResourceModel\User\Collection $authorCollection,
        array $data = array()) {
        parent::__construct($context, $wpContext, $data);
        $this->_authorCollection=$authorCollection;
    }



    /**
     * Returns a collection of valid archive dates
     *
     * @return Varien_Data_Collection
     */
    public function getAuthors()
    {
        $collection = $this->_authorCollection;
        $collection->getSelect()->joinRight(array('post' => 'wp_posts'), "main_table.ID = post.post_author" ,array('COUNT(post.post_date) AS post_count'));
        $collection->getSelect()->where('post.post_status="publish" AND post.post_title !=""');
        $collection->getSelect()->group('main_table.ID');
        return $collection;

    }
    
    /**
     * Split a date by spaces and translate
     *
     * @param string $date
     * @param string $splitter = ' '
     * @return string
     */
    public function translateDate($date, $splitter = ' ')
    {
        $dates = explode($splitter, $date);
        
        foreach($dates as $it => $part) {
            $dates[$it] = __($part);
        }
        
        return implode($splitter, $dates);
    }
    
    /**
     * Determine whether the archive is the current archive
     *
     * @param Fishpig_Wordpress_Model_Archive $archive
     * @return bool
     */
    public function isCurrentArchive($archive)
    {
        if ($this->getCurrentArchive()) {
            return $archive->getId() == $this->getCurrentArchive()->getId();
        }

        
        return false;
    }
    
    /**
     * Retrieve the current archive
     *
     * @return Fishpig_Wordpress_Model_Archive
     */
    public function getCurrentArchive()
    {
        return $this->_registry->registry('wordpress_user');
    }
    
    /**
     * Retrieve the default title
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return __('Author');
    }
    
    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('sidebar/widget/user.phtml');
        }
        
        return parent::_beforeToHtml();
    }

}
