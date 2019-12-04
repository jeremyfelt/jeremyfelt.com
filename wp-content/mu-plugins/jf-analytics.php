<?php
/**
 * Various tags and things
 */

add_action( 'wp_head', 'jf_ga_head', 999 );
function jf_ga_head() {
	?>
	<!-- Site traffic tracking through a self-hosted instance of Matomo. IP addresses
         are "anonymized" to 2 octets so that 192.168.123.345 becomes 192.168.0.0 in
	     my dashboard view.
	-->
	<script type="text/javascript">
	var _paq = window._paq || [];
	/* tracker methods like "setCustomDimension" should be called before "trackPageView" */
	_paq.push(['trackPageView']);
	_paq.push(['enableLinkTracking']);
	(function() {
		var u="//jeremyfelt.com/";
		_paq.push(['setTrackerUrl', u+'matomo.php']);
		_paq.push(['setSiteId', '1']);
		var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
		g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
	})();
	</script>
	<?php
}
