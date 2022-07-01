<?php
namespace Tatva\Paypalrec\Model;

class Info extends \Magento\Paypal\Model\Info
{
   public function getPaymentInfo(\Magento\Payment\Model\InfoInterface $payment, $labelValuesOnly = false)
   {
      // collect paypal-specific info
      $result = $this->_getFullInfo(array_values($this->_paymentMap), $payment, $labelValuesOnly);

      // add last_trans_id
      $label = __('Last Transaction ID');
      $value = $payment->getLastTransId();
      if ($labelValuesOnly) {
         $result[(string)$label] = $value;
      } else {
         $result['last_trans_id'] = ['label' => $label, 'value' => $value];
      }

      $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
      $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
      $connection = $resource->getConnection();
      $tableName = $resource->getTableName('recurring_payment_order');
      $sql = "Select paypal_id  FROM " . $tableName . " rpo inner join recurring_payment rp on rp.payment_id=rpo.payment_id inner join paypal_result pr on pr.increment_id =
         rp.internal_reference_id where order_id = " . $payment->getParentID() . " limit 1";
      $paypalid = $connection->fetchAll($sql);
     
     
      if(isset($result['Payer ID']))
      {
         $payerId = $result['Payer ID']->getText();
         if(!empty($payerId))
         {
            $tableName = $resource->getTableName('paypal_result');
         
            $sql = "Select paypal_id from ".$tableName." where payers_id = '".$payerId ."' Order By id desc limit 1";
            $paypalid = $connection->fetchAll($sql);
         }
      }

      $label = __('Paypal ID');
      //$value = $payment->getLastTransId();
      if ($paypalid) {
         $result[(string)$label] = $paypalid[0];
      } else {
         $result['Paypal ID'] = ['label' => false, 'value' => false];
      }
     
      return $result;
   }
}
