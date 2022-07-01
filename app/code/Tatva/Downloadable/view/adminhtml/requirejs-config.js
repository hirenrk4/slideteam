var config = {
   	map: {
		'*': {
        	'Magento_Downloadable/js/components/is-downloadable-handler': 'Tatva_Downloadable/js/components/is-downloadable-handler'
      	}
   	},
   	paths: {
        'Ddimgtooltip': 'Tatva_Customer/js/ddimgtooltip'
    },
    shim: {
        'Ddimgtooltip': {
            deps: ['jquery']
        }
    }
};