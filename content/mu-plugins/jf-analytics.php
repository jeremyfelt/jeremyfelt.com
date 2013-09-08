<?php
/**
 * Add tracking code to the footer on every page load.
 */

add_action( 'wp_footer', 'jf_analytics', 998 );
function jf_analytics() {
    ?>
    <!-- Googla Analytics Tracking -->
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-39564638-1', 'jeremyfelt.com');
        ga('send', 'pageview');

    </script>
    <!-- end Google Analytics tracking -->
    <!-- Piwik -->
    <script type="text/javascript">
        var pkBaseURL = (("https:" == document.location.protocol) ? "https://stats.foghlaim.com/" : "http://stats.foghlaim.com/");
        document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
    </script><script type="text/javascript">
        try {
            var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);
            piwikTracker.trackPageView();
            piwikTracker.enableLinkTracking();
        } catch( err ) {}
    </script><noscript><p><img src="http://stats.foghlaim.com/piwik.php?idsite=1" style="border:0" alt="" /></p></noscript>
    <!-- End Piwik Tracking Code -->
    <?php
}
