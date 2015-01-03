<?php
/**
 * Various tags and things
 */

add_action( 'wp_head', 'jf_head', 10 );
function jf_head() {
	// Add the tag provided by analytics.twitter.com, mostly to find out why.
	?>
	<meta property="twitter:account_id" content="1741681" /><?php

	// Add the tag that connects me to Google+
	?>
	<link rel="me" type="text/html" href="http://www.google.com/profiles/jeremy.felt"/>
	<?php

	// Add the tag that validates with Microsoft!
	?>
	<meta name="msvalidate.01" content="7F45AEBECE60DA943AD0E712BD3BDBD6" />
	<?php

	// Add the tag that validates with Google Webmaster Tools
	?>
	<meta name="google-site-verification" content="rPLtSyhW-8YGAT9US8ogGbuLnSPzOfoXfZar6n6t1zA" />
	<?php
}