<?php

// Database configuration
define( 'DB_NAME',     'jeremyfelt' );
define( 'DB_USER',     'wp'         );
define( 'DB_PASSWORD', 'wp'         );
define( 'DB_HOST',     'localhost'  );
define( 'DB_CHARSET',  'utf8'       );
define( 'DB_COLLATE',  ''           );

// Home URL (http://mydomain.com)
define( 'WP_HOME',        'http://dev.jeremyfelt.com' );
// WordPress URL (http://mydomain.com);
define( 'WP_SITEURL',     'http://dev.jeremyfelt.com' );
// Content URL (http://content.mydomain.com)
define( 'WP_CONTENT_URL', 'http://dev.content.jeremyfelt.com' );
// Content Directory
define( 'WP_CONTENT_DIR', dirname( dirname( __FILE__ ) ) . '/content' );

// Enable debugging in development
define( 'WP_DEBUG',    true );
define( 'SAVEQUERIES', true );

// Salts and keys for cookies!
define('AUTH_KEY',         't|f0k+<Mwrv#4qhat^A|O_ygONtbwN#gq+[%TCe`5*t!-WSnje^E)R!:C:ga<S^v');
define('SECURE_AUTH_KEY',  'FZn!<<(N]/3?5IZjZ-L8hB+Mvv6poJDAdZxOrIpUtWrTtmau*7Q$`f+ccrKpJ#Z<');
define('LOGGED_IN_KEY',    'urT,Vrf-6^1B&BP[0YT[U|dWTfF|z3m}:KRb>[m}*K@xllHjVf0iYO5@yi7U|fvl');
define('NONCE_KEY',        'Lv+v|+wuaLDUA/OR+rW,/i^foM`{x0x]H0C;{5h0E6w~2HcxAIMk%v//*c>YY{pr');
define('AUTH_SALT',        '9JKicb3.j5sP>3_U dzEJQv`4;@`%}uD]xzI+M]HKG$?Q4nl+?v$x=CA2vjWDcYZ');
define('SECURE_AUTH_SALT', 'NHJzrFsO/+rn2@/*)DvrZd^wV3_6BW7)bh@K&FYH#>z.yTr7Pe_ix 5W{fr3Io0p');
define('LOGGED_IN_SALT',   '4Q2/:@a%-C[|dvmnVYLe]Ff-7N0f-pwM~%B|-7}T0J+do)yUYUZHBp$V^y_f)]d(');
define('NONCE_SALT',       '`[zkR}hh-6SUTx4Q$-+<A!E^E,=67oE#TJ[Y8>j_hcTI<-+(pD*0x]0SAW[o:Gwm');
