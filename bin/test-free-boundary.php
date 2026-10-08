<?php
/**
 * Self-check for the Free/Pro boundary.
 *
 * Every Pro feature is meant to be physically absent from this plugin, not switched off.
 * The easy ways to break that are a rebuild from a stale source tree or a file copied
 * across from the Pro plugin. This asserts that neither has happened.
 *
 *   php bin/test-free-boundary.php
 *
 * @package DevDiggers Order via Chat for WooCommerce
 */

$root   = dirname( __DIR__ ) . '/';
$errors = [];

/**
 * Read a file, or return '' when it is absent.
 *
 * @param string $path Absolute path.
 * @return string
 */
function ddwcpvw_read( $path ) {
	return file_exists( $path ) ? (string) file_get_contents( $path ) : '';
}

/**
 * Every PHP file that belongs to this plugin, excluding the bundled framework and tooling.
 *
 * @param string $root Plugin root.
 * @return string[]
 */
function ddwcpvw_plugin_php( $root ) {
	$files    = [];
	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );

	foreach ( $iterator as $file ) {
		$path = $file->getPathname();

		if ( 'php' !== strtolower( $file->getExtension() ) ) {
			continue;
		}

		foreach ( [ '/node_modules/', '/devdiggers-framework/', '/bin/', '/build/' ] as $skip ) {
			if ( false !== strpos( $path, $skip ) ) {
				continue 2;
			}
		}

		$files[ str_replace( $root, '', $path ) ] = ddwcpvw_read( $path );
	}

	return $files;
}

$php = ddwcpvw_plugin_php( $root );

// 1. Pro-only files are gone, not merely unhooked.
foreach ( [
	'helper/twilio-helper.php',
	'helper/messages-helper.php',
	'helper/request-helper.php',
	'helper/broadcast-helper.php',
	'includes/common/common-functions.php',
	'includes/common/common-hooks.php',
	'includes/common/wallet-integration.php',
	'includes/admin/admin-ajax-functions.php',
	'includes/admin/admin-ajax-hooks.php',
	'includes/install.php',
	'templates/admin/messages/messages-list-template.php',
	'templates/admin/messages/broadcast-message-template.php',
	'templates/admin/orders/orders-list-template.php',
	'templates/admin/configuration/license-configuration-template.php',
	'templates/admin/configuration/twilio-configuration-template.php',
	'templates/admin/configuration/chat-configuration-template.php',
	'templates/admin/configuration/notifications-configuration-template.php',
	'templates/admin/configuration/wallet-configuration-template.php',
] as $file ) {
	if ( file_exists( $root . $file ) ) {
		$errors[] = "Pro file {$file} is shipped.";
	}
}

// 2. No licensing, updater or remote notifications. WordPress.org rejects a free plugin
// that phones home for a key, reachable or not.
foreach ( $php as $relative => $contents ) {
	foreach ( [ 'ddfw_is_license_activated', 'ddfw_register_plugin_for_updates', '_ddwcpvw_purchase_code', 'DevDiggers_Notifications', 'DDFW_Plugin_Updater' ] as $needle ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$errors[] = "Licensing reference '{$needle}' is present in {$relative}.";
		}
	}
}

foreach ( [ 'includes/class-ddfw-plugin-updater.php', 'includes/class-devdiggers-notifications.php', 'includes/class-ddfw-plugin-dependency.php', 'templates/layout/license.php', 'assets/js/notifications.js' ] as $file ) {
	if ( file_exists( $root . 'devdiggers-framework/' . $file ) ) {
		$errors[] = "Bundled framework ships {$file}.";
	}
}

// 3. No outbound API, webhook, cron or Pro AJAX. Free only ever links to wa.me; the store
// never sends a message itself, so nothing may call Twilio or listen for it.
$registrations = [
	'wp_remote_',
	'api.twilio.com',
	'register_rest_route',
	'rest_pre_serve_request',
	'wp_schedule_event',
	'wp_schedule_single_event',
	'dbDelta',
	"'wp_ajax_ddwcpvw_broadcast",
	"'wp_ajax_ddwcpvw_test_twilio_connection'",
	"'wp_ajax_ddwcpvw_send_order_message'",
	'ddwcpvw_daily_cleanup',
	'ddwcpvw_hourly_recovery',
	'woocommerce_order_status_changed',
	'woocommerce_checkout_order_processed',
	'woocommerce_store_api_checkout_order_processed',
	'woocommerce_review_order_before_submit',
	'ddwcwm_transaction_saved',
	'fputcsv',
];

foreach ( $php as $relative => $contents ) {
	foreach ( $registrations as $needle ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$errors[] = "Pro registration {$needle} is present in {$relative}.";
		}
	}
}

// 4. No Pro business logic left behind in shared files: chat assistant, notifications,
// recovery, opt-in, message log, wallet.
$pro_logic = [
	'DDWCPVW_Twilio_Helper',
	'DDWCPVW_Messages_Helper',
	'DDWCPVW_Request_Helper',
	'DDWCPVW_Broadcast_Helper',
	'DDWCPVW_Wallet_Integration',
	'ddwcpvw_requests',
	'ddwcpvw_messages',
	'_ddwcpvw_whatsapp_order',
	'_ddwcpvw_opted_out_numbers',
	'ddwcpvw_get_chat_reply_options',
	'ddwcpvw_get_wallet_reply_options',
	'ddwcpvw_replace_placeholders',
	'ddwcpvw_get_template_variables',
	'content_sid',
	'recovery_',
	'notification_order',
	'admin_alert',
	'opt_in',
];

foreach ( $php as $relative => $contents ) {
	foreach ( $pro_logic as $needle ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$errors[] = "Pro logic '{$needle}' is present in {$relative}.";
		}
	}
}

// 5. No Pro-only setting is registered, so a crafted options.php or wizard POST has nothing to write.
$admin_functions = ddwcpvw_read( $root . 'includes/admin/admin-functions.php' );

foreach ( [ '_ddwcpvw_twilio_account_sid', '_ddwcpvw_twilio_auth_token', '_ddwcpvw_twilio_api_key_sid', '_ddwcpvw_twilio_api_key_secret', '_ddwcpvw_enabled_payment_gateways', '_ddwcpvw_chat_keywords_enabled', '_ddwcpvw_message_log_enabled', '_ddwcpvw_notification_statuses', '_ddwcpvw_wallet_enabled', '_ddwcpvw_broadcast_opted_in_only', '_ddwcpvw_support_email' ] as $needle ) {
	if ( false !== strpos( $admin_functions, "'{$needle}'" ) ) {
		$errors[] = "Pro setting '{$needle}' is registered in includes/admin/admin-functions.php.";
	}
}

// 6. The built bundles carry no Pro flow, and still do the Free job.
$admin_bundle = ddwcpvw_read( $root . 'assets/js/admin.js' );
$front_bundle = ddwcpvw_read( $root . 'assets/js/front.js' );

if ( '' === $admin_bundle || '' === $front_bundle ) {
	$errors[] = 'Built bundles are missing. Run `npm run build`.';
}

foreach ( [ 'ddwcpvw_broadcast', 'ddwcpvw_test_twilio_connection', 'ddwcpvw_send_order_message', 'wp.media', 'console.log', 'document.cookie', 'localStorage' ] as $needle ) {
	foreach ( [ 'assets/js/admin.js' => $admin_bundle, 'assets/js/front.js' => $front_bundle ] as $bundle => $contents ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$errors[] = "Pro identifier '{$needle}' is present in {$bundle}.";
		}
	}
}

foreach ( [ 'ddwcpvw_prepare_whatsapp_url', 'found_variation' ] as $needle ) {
	if ( '' !== $front_bundle && false === strpos( $front_bundle, $needle ) ) {
		$errors[] = "Free behaviour '{$needle}' is missing from assets/js/front.js.";
	}
}

// 7. Every built asset maps to a webpack entry.
$webpack = ddwcpvw_read( $root . 'webpack.config.js' );

foreach ( [ 'assets/js', 'assets/css' ] as $dir ) {
	foreach ( (array) glob( $root . $dir . '/*' ) as $path ) {
		$name = basename( $path );

		if ( 'index.php' === $name ) {
			continue;
		}

		if ( false === strpos( $webpack, '"' . pathinfo( $name, PATHINFO_FILENAME ) . '"' ) ) {
			$errors[] = "{$dir}/{$name} has no webpack entry.";
		}
	}
}

// 8. The Free bootstrap yields to Pro and loads the framework on its own pages.
$bootstrap = ddwcpvw_read( $root . 'functions.php' );

foreach ( [ "class_exists( 'DDWCPVW_Init' )", "'ddwcpvw' !== \$prefix", 'DDWCPVW_Free_Init' ] as $needle ) {
	if ( false === strpos( $bootstrap, $needle ) ) {
		$errors[] = "Bootstrap guard '{$needle}' is missing from functions.php.";
	}
}

// 9. No constants or upgrade routine a first release has no use for. Asset versions come
// from filemtime(), and the Pro link is written where it is used.
foreach ( $php as $relative => $contents ) {
	foreach ( [ 'DDWCPVW_VERSION', 'DDWCPVW_PRO_URL', 'ddwcpvw_maybe_upgrade', 'ddwcpvw_db_version', 'wp_unschedule_hook' ] as $needle ) {
		if ( false !== strpos( $contents, $needle ) ) {
			$errors[] = "Unneeded '{$needle}' is present in {$relative}.";
		}
	}
}

if ( $errors ) {
	echo "Free/Pro boundary check FAILED:\n";

	foreach ( $errors as $error ) {
		echo ' - ' . $error . "\n";
	}

	exit( 1 );
}

echo "Free/Pro boundary check passed.\n";
