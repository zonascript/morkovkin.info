<?php

return array
(
    'actionPath' => ROOT_DIR . '/app/actions',
    'viewPath' => ROOT_DIR . '/app/pages',
    
    // 'hostInfo' => 'http://morkovkin.info/samples/skinshop',
    'hostInfo' => '/samples/all/template-store/www',
    'homeRoute' => 'stage/index',
    'errorRoute' => false,
    'loginRoute' => false,
    'routeVar' => false,
    'urlStrict' => false,
    'urlSuffix' => '.php',
    'urlScript' => false,
    
    'routeMap' => array
    (
        'error' => 'stage/error',
        // 'order' => 'stage/order',
        // 'status' => 'stage/status',
    ),
    
    'components' => array
    (
        'error' => array
        (
            'class' => 'CErrorHandler',
            'logFile' => ROOT_DIR . '/app/temp/logs/application.log',
        ),
        
        'cache' => array
        (
            'class' => 'CFileCache',
            'cachePath' => ROOT_DIR . '/app/temp/cache',
        ),
        
        'email' => array
        (
            'class' => 'CPostman',
            'templatePath' => ROOT_DIR . '/app/emails',
            'from' => 'support@morkovkin.info',
        ),
        
        'login' => array
        (
            'class' => 'CWebLogin',
        ),
        
        /* 'session' => array
        (
            'class' => 'CFileSession',
            'savePath' => ROOT_DIR . '/app/temp/sessions',
        ), */
        
        'messages' => array
        (
            'class' => 'CMessages',
            'languageFile' => ROOT_DIR . '/app/config.eng.inc',
        ),
        
        'security' => array
        (
            'class' => 'CSecurity',
            'securityKey' => '6CAF420F-3E20-4820-A551-F4C7D9C01531',
        ),
        
        'db' => array
        (
            'class' => 'CDatabase',
            'host' => 'db.luxor.hosted.in',
            'username' => 'waylexru_skin',
            'password' => 'skinshop',
            'schema' => 'waylexru_skinshop',
        ),
        
        'helpdesk' => array
        (
            'class' => 'CHelpDesk',
            'website' => 'helpdesk-account',
            'username' => '#',
            'password' => '#',
            'category' => '#',
            'public' => false,
            'skipSpam' => true,
        ),
        
        'templates' => array
        (
            'class' => 'CTemplates',
            'templatePath' => ROOT_DIR . '/cat',
            'templateFile' => 'template.zip',
            'templateImageFile' => 'image_large.png',
            'templateThumbFile' => 'image_small.png',
            'templateImageSize' => array(488, 456),
            'templateThumbSize' => array(310, 280),
        ),
        
        'pmusd' => array
        (
            'class' => 'CSystemPM',
            'userId' => '',
            'payeeName' => '',
            'account' => '',
            'sciPass' => '',
        ),
        
        'egusd' => array
        (
            'class' => 'CSystemEP', 
            'account' => '',
            'sciId' => '',
            'sciPass' => '',
        ),
        
        'stusd' => array
        (
            'class' => 'CSystemST',
            'account' => '',
            'sciName' => '',
            'sciPass' => '',
        ),
        
        'bcusd' => array
        (
            'class' => 'CSystemBC',
            'address' => '',
            'secret' => '',
        ),
    ),
    
    'controllers' => array
    (
        'stage' => array(),
        'admin' => array(),
    ),
    
);