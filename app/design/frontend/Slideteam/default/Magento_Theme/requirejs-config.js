/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

 var config = {
    paths: {
        'jquery/jquery-migrate': 'Magento_Theme/js/empty'
    },
    map: {
        '*': {
            Emarsysjs:'Emarsys_Emarsys/js/emarsys',
            EmarsysWidgetJs:'Emarsys_Emarsys/js/emarsys',
            jQdotdot:'Magento_Theme/js/jquery.dotdotdot',
            Ddimgtooltip:'Magento_Theme/js/ddimgtooltip',
            magnific_popup:'Magento_Theme/js/magnific-popup/jquery.magnific-popup',
            aboutus_general:'Magento_Cms/js/aboutus/general',
            shave_master: 'Magento_Theme/js/jquery.shave'
        }
    },
    shim:{
        EmarsysWidgetJs: {deps: ['jquery','owl_carausel','jQdotdot']},
        Emarsysjs: {deps: ['jquery']},
        Ddimgtooltip:{deps: ['jquery']},
        jQdotdot:{deps:['jquery']}     
    }
 };
