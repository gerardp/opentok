<?php
/*!
* OpenTok PHP Library
* http://www.tokbox.com/
*
* Copyright 2010, TokBox, Inc.
*
*/

        require_once 'API_Config.php';
        require_once 'OpenTokSDK.php';

        $apiObj = new OpenTokSDK(API_Config::API_KEY, API_Config::API_SECRET);

        $session = $apiObj->create_session($_SERVER["REMOTE_ADDR"]);

        echo $session->getSessionId();
        echo "<br/>";
        echo $apiObj->generate_token();
?>
