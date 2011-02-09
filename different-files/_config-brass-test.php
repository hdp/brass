<?php

define( 'DB_USER'     , '' );
define( 'DB_PASSWORD' , '' );
define( 'DB_DATABASE' , '' );

define( 'EMAIL_HOST'     , 'orderofthehammer.com'           );
define( 'EMAIL_USERNAME' , 'automated@orderofthehammer.com' );
define( 'EMAIL_PASSWORD' , ''                               );
define( 'EMAIL_FROM'     , 'automated@orderofthehammer.com' );
define( 'EMAIL_ENABLED'  , true                             );

if ( !defined('TEST_MODE') ) {
    define('TEST_MODE', true);
}

define( 'TEST_ENVIRONMENT_NOTICE'     , '<font color="#FF0000"><b>TEST ENVIRONMENT</b></font> --- ' );
define( 'SITE_ADDRESS'                , 'http://brass-test.orderofthehammer.com/'                   );
define( 'HIDDEN_FILES_PATH_NS'        , '/home/orderoft/hf-brass-t'                                 );
define( 'GFX_DIR_NS'                  , '/home/orderoft/public_html/brass-test/gfx'                 );
define( 'NUM_MOVES_MADE_DIR_NS'       , '/home/orderoft/public_html/brass-test/nummovesmade'        );
define( 'SPEC_DIR_NS'                 , '/home/orderoft/public_html/brass-test/specs'               );
define( 'TRANSLATION_CACHE_PREFIX_NS' , '/home/orderoft/trans-cache-brass-test'                     );
session_save_path('/home/orderoft/sessions-brass-test/');
session_set_cookie_params(0, '/', '.brass-test.orderofthehammer.com');

define( 'NUM_FOREIGN_LANGUAGES'  , 6 );
define( 'MAINTENANCE_DISABLED'   , 0 );
define( 'LOGIN_DISABLED'         , 0 );
define( 'REGISTRATION_DISABLED'  , 1 );
define( 'DISPLAY_SYSTEM_MESSAGE' , 0 );

define('SYSTEM_MESSAGE', '');

require('common.php');

?>