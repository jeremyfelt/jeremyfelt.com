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

add_action( 'wp_head', 'jf_ga_head', 999 );
function jf_ga_head() {
	?>
	<!-- Google Analytics -->
	<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

	ga('create', 'UA-39564638-1', 'auto');
	ga('send', 'pageview');
	</script>
	<!-- End Google Analytics -->
	<?php
}
