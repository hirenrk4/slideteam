<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also availabel through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\RecurringPayment\Model;

class PeriodUnits implements \Magento\Framework\Option\ArrayInterface
{
    const DAY = 'day';
    const WEEK = 'week';
    const SEMI_MONTH = 'semi_month';
    const MONTH = 'month';
    const YEAR = 'year';

    
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value'=> null, 'label' => __('--Select Period--')],
            ['value'=> self::DAY, 'label' => __('Day')],
            ['value'=> self::WEEK ,'label'=> __('Week')],
            ['value'=> self::SEMI_MONTH, 'label' => __('Two Weeks')],
            ['value'=> self::MONTH, 'label' => __('Month')],
            ['value'=> self::YEAR, 'label' => __('Year')]
        ];
        
        return $options;
    }


    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return[
            0 => __('--Select Period--'),
            self::DAY => __('Day'),
            self::WEEK => __('Week'),
            self::SEMI_MONTH => __('Two Weeks'),
            self::MONTH => __('Month'),
            self::YEAR => __('Year'),
        ];
    }

}
