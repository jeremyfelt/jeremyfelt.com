<?php
/**
 * Various tags and things
 */

add_action( 'wp_head', 'jf_head', 10 );
function jf_head() {
	// Add the tag provided by analytics.twitter.com, mostly to find out why.
	?><meta property="twitter:account_id" content="1741681" /><?php
}

/**
 * Add tracking code to the footer on every page load.
 */

add_action( 'wp_footer', 'jf_analytics', 998 );
function jf_analytics() {
    // Add the Google Analytics tracking script
	?><script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
        ga('create', 'UA-39564638-1', 'jeremyfelt.com');
        ga('send', 'pageview');
	</script><?php

	// Add the Piwik tracking script
	?>
	<script type="text/javascript">
		var pkBaseURL = (("https:" == document.location.protocol) ? "https://stats.foghlaim.com/" : "http://stats.foghlaim.com/");
		document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
		try {
			var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);
			piwikTracker.trackPageView();
			piwikTracker.enableLinkTracking();
		} catch( err ) {}
	</script>
	<noscript><p><img src="http://stats.foghlaim.com/piwik.php?idsite=1" style="border:0" alt="" /></p></noscript>
	<?php
}
