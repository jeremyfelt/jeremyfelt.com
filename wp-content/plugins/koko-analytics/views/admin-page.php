<?php defined( 'ABSPATH' ) or exit; ?>
<div class="wrap" id="koko-analytics-admin">
	<noscript>
		<?php echo esc_html__( 'Please enable JavaScript for this page to work.', 'koko-analytics' ); ?>
	</noscript>

	<div id="koko-analytics-mount"></div>
</div>
<script>
	<?php
	echo 'var koko_analytics = ' . json_encode(
		array(
			'root'          => rest_url(),
			'nonce'         => wp_create_nonce( 'wp_rest' ),
			'i18n'          => array(
				'Automatically delete data older than how many months?' => __( 'Automatically delete data older than how many months?', 'koko-analytics' ),
				'Database size:'                          => __( 'Database size:', 'koko-analytics' ),
				'Date range'                              => __( 'Date range', 'koko-analytics' ),
				'Exclude pageviews from these user roles' => __( 'Exclude pageviews from these user roles', 'koko-analytics' ),
				'Last week'                               => __( 'Last week', 'koko-analytics' ),
				'Last month'                              => __( 'Last month', 'koko-analytics' ),
				'Last year'                               => __( 'Last year', 'koko-analytics' ),
				'months'                                  => __( 'months', 'koko-analytics' ),
				'Next'                                    => __( 'Next', 'koko-analytics' ),
				'No'                                      => __( 'No', 'koko-analytics' ),
				'Pages'                                   => __( 'Pages', 'koko-analytics' ),
				'Pageviews'                               => __( 'Pageviews', 'koko-analytics' ),
				'Previous'                                => __( 'Previous', 'koko-analytics' ),
				'Referrers'                               => __( 'Referrers', 'koko-analytics' ),
				'Save Changes'                            => __( 'Save Changes', 'koko-analytics' ),
				'Saving - please wait'                    => __( 'Saving - please wait', 'koko-analytics' ),
				'Saved!'                                  => __( 'Saved!', 'koko-analytics' ),
				'Settings'                                => __( 'Settings', 'koko-analytics' ),
				'Stats'                                   => __( 'Stats', 'koko-analytics' ),
				'Statistics older than the number of months configured here will automatically be deleted. Set to 0 to disable.' => __( 'Statistics older than the number of months configured here will automatically be deleted. Set to 0 to disable.', 'koko-analytics' ),
				'There\'s nothing here, yet!'             => __( 'There\'s nothing here, yet!', 'koko-analytics' ),
				'This week'                               => __( 'This week', 'koko-analytics' ),
				'This month'                              => __( 'This month', 'koko-analytics' ),
				'This year'                               => __( 'This year', 'koko-analytics' ),
				'Today'                                   => __( 'Today', 'koko-analytics' ),
				'Total visitors'                          => __( 'Total visitors', 'koko-analytics' ),
				'Total pageviews'                         => __( 'Total pageviews', 'koko-analytics' ),
				'Use cookie to determine unique visitors and pageviews?' => __( 'Use cookie to determine unique visitors and pageviews?', 'koko-analytics' ),
				'Visitors'                                => __( 'Visitors', 'koko-analytics' ),
				'Visits and pageviews from users with any of the selected roles will be ignored.' => __( 'Visits and pageviews from users with any of the selected roles will be ignored.', 'koko-analytics' ),
				'Yes'                                     => __( 'Yes', 'koko-analytics' ),
				'Set to "no" if you do not want to use a cookie. Without the use of a cookie, Koko Analytics can not reliably detect returning visitors.' => __( 'Set to "no" if you do not want to use a cookie. Without the use of a cookie, Koko Analytics can not reliably detect returning visitors.', 'koko-analytics' ),
			),
			'start_of_week' => $start_of_week,
			'user_roles'    => $user_roles,
			'settings'      => $settings,
			'showSettings'  => current_user_can( 'manage_koko_analytics' ),
			'dbSize' => $this->get_database_size(),
		)
	);
	?>
</script>
<script src="<?php echo plugins_url( 'assets/dist/js/admin.js', KOKO_ANALYTICS_PLUGIN_FILE ); ?>?ver=<?php echo KOKO_ANALYTICS_VERSION; ?>"></script>
