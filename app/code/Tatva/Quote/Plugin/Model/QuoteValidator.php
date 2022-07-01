<?php
namespace Tatva\Quote\Plugin\Model;

use Magento\Quote\Model\Quote as QuoteEntity;

/**
 *  Need to set $quote->getBillingAddress()->setShouldIgnoreValidation()
 *  To skip billing address validation
 */
class QuoteValidator 
{
	public function beforeValidateBeforeSubmit(
		\Magento\Quote\Model\QuoteValidator $subject,
		 QuoteEntity $quote){
		$quote->getBillingAddress()->setShouldIgnoreValidation(true);
    }
}