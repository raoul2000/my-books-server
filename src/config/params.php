<?php
// DEV
return [
    'app' => [
        /**
         * Name and Email of the sender of all email
         */
        'senderEmail'  => 'Raoul@ass-team.fr',
        'senderName'   => 'Raoul',
        'saveBookPing' => true,
        /**
         * recipient of contact email
         */
        'contactEmail' => 'mes.livres@exdata.info',
        /**
         * When TRUE, user must validate account after registration (email registration).
         * Otherwise, account is immediately active and user can login.
         */
        'enableAccountActivation' => false,
        /**
         * URL of the app "Mes Livres" (mobile and desktop)
         */        
        'bookAppUrl'  => 'http://localhost:3000/#/signin-key', 
        /**
         * URL of the book ping page.
         * 
         * Used by  the redirection defined in '/web/ping-redirect/index.php'.
         * This URL should match the URL set in the QR code content on BookTicket creation
         * (see src\models\BookTicket.php - createQrCode() )
         */
        'bookPingUrl'        => 'http://localhost:8080/index.php?r=book-ping',
        /**
         * URL of the checkpoint as it appears on the book ticket.
         * 
         * This URL must point to the redirection page defined in '/web/ping-redirect/index.php'
         * and where final URL (the book ping page) is configured in 'bookPingUrl'
         */ 
        'checkpointUrl' => 'http://localhost:8080/ping-redirect',            
        /**
         * Captcha Policy settings
         */
        'enableVerifyCodeOnLogin'           => false,
        'enableVerifyCodeOnCreateAccount'   => false,   
        /**
         * Alias for the path where QR codes image files are stored.
         * Used to create alias '@qrcodePath' in 'web/index.php'
         */      
        'qrcodePathAlias'=> '@app/../assets/files/qr-codes',        

        /**
         * Parameters related to cron jobs
         */
        'cron' => [
            /**
             * API Key to perform DB backup
             */
            'dbBackupKey' => 'e49d8ddd-bd3e-420d-bfc8-f04cd96fa26a'
        ]        
    ],

    'mailer' => [
        'class'            => 'yii\swiftmailer\Mailer',
        'useFileTransport' => true          
    ],
    'mailer_smtp' => [
        'class' => 'yii\swiftmailer\Mailer',
        'useFileTransport' => false,
        'transport' => [
            'class'    => 'Swift_SmtpTransport',
            'encryption' => 'tls',
            'host'     => 'smtp.ionos.fr',
            'port'     => '587',
            'username' => 'Raoul@ass-team.fr',
            'password' => 'XXXXXXX',
        ],           
    ]
];
