<?php

namespace Tatva\Notification\Block\Adminhtml\Index\Edit;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    protected $systemStore;
    protected $notificationTypeOptions;
    protected $customerTypeOptions;
    protected $paidDurationOptions;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $current_date;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        TimezoneInterface $date,
        \Tatva\Notification\Model\Config\Source\NotificationType $notificationTypeOptions,
        \Tatva\Notification\Model\Config\Source\CustomerType $customerTypeOptions,
        \Tatva\Notification\Model\Config\Source\PaidDuration $paidDurationOptions,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->current_date =  $date;
        $this->notificationTypeOptions = $notificationTypeOptions;
        $this->customerTypeOptions = $customerTypeOptions;
        $this->paidDurationOptions = $paidDurationOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('notification_store');
        $this->setTitle(__('Notification Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('notification_form');
        
        $wysiwygData = $this->wysiwygConfig->getConfig(
            [
                'enabled' => true,
                'hidden' => false
            ]);

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'enctype' => 'multipart/form-data',
            'action' => $this->getData('action'), 'method' => 'post']]
        );
        $form->setHtmlIdPrefix('form_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                    'legend'    => __('Notification Information'),
                    'class'     => 'fieldset-wide'
                ]
        );
        if ($model->getId()) {
            $fieldset->addField('notification_id', 'hidden', ['name' => 'notification_id']);
            $durationOption = [
                              ['value'=>'0','label'=>'Monthly'],
                              ['value'=>'1','label'=>'Semi Annual'],
                              ['value'=>'2','label'=>'Annual'],
                              ['value'=>'3','label'=>'Annual + Custom Design'],
                              ['value'=>'4','label'=>'Please select duration']
                            ];
             $model->setPublisheAt($this->converToTz($model->getPublisheAt(),'America/Los_Angeles','GMT'));
        } else {
            $durationOption=[
                              ['value'=>'0','label'=>'Monthly'],
                              ['value'=>'1','label'=>'Semi Annual'],
                              ['value'=>'2','label'=>'Annual'],
                              ['value'=>'3','label'=>'Annual + Custom Design']
                            ];                     
        }
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => __('status'),
                'required' => true,
                'values' => [
                              ['value'=>'1','label'=>'Enabled'],
                              ['value'=>'0','label'=>'Disabled'],
                            ],
                'required' => true
            ]
        );
        $fieldset->addField(
            'publishe_at',
            'date',
            [
                'name' => 'publishe_at',
                'label' => __('Publish Date'),
                'title' => __('Publish Date'),
                'date_format' => 'yyyy-MM-dd',
                'class' => 'required-entry validate-date validate-date-range date-range-attribute-from',
                'time_format' => 'HH:mm:ss',
                'min_date' => $this->current_date->date()->format('Y-m-d'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'type',
            'select',
            [
                'name'  => 'type',
                'label' => __('Type'),
                'title' => __('Type'),
                'values' => $this->notificationTypeOptions->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'customer_type',
            'select',
            [
                'name'  => 'customer_type',
                'label' => __('Customer Type'),
                'title' => __('Customer Type'),
                'values' => $this->customerTypeOptions->toOptionArray(),
            ]
        )->setAfterElementHtml(
            '
            <script>
                require([
                     "jquery",
                ], function($){

                    $(document).ready(function () {
                        $(".field-paid_duration").hide();
                        $(function() {                          
                           if ($("#form_customer_type").val() == 2) {
                                $(".field-paid_duration").show();
                           }                           
                        });   
                        $("#form_customer_type").on("change", function() {
                            console.log($("#form_paid_duration").val());
                            console.log($("#form_customer_type").attr("value"));
                            if($("#form_customer_type").attr("value") == 2){
                                console.log("if");
                                $(".field-paid_duration").show();
                            }else{
                                console.log("else");
                                $(".field-paid_duration").hide();
                            }
                        });                     
                    });
                });
            </script>
        '
        );
        $fieldset->addField(
            'paid_duration',
            'select',
            [
                'name'  => 'paid_duration',
                'label' => __('Duration'),
                'title' => __('Duration'),
                'values' => $durationOption
            ]
        );
        $fieldset->addField(
            'content',
            'editor',
            [
                'name' => 'content',
                'label' => __('Content'),
                'title' => __('Content'),
                'rows' => '3',
                'cols' => '100',
                'wysiwyg' => true,
                // 'config' => $this->wysiwygConfig->getConfig(),
                'config' => $wysiwygData,
                'required' => true
            ]
        );
    
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    protected function converToTz($dateTime="", $toTz='', $fromTz='')
    {
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('Y-m-d H:i:s');
        return $dateTime;
    }
}
