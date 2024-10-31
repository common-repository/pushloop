<?php
if ( ! class_exists( 'PushloopLogger' ) ) {
	/**
	 * Class for managing the logging of PushLoopWP activities.
	 *
	 * This class handles all logging operations for PushLoopWP,
	 * including the logging of information, errors and debug messages.
	 *
	 * @package PushLoop
	 * @subpackage Logger
	 * @since 1.4.0
	 */
	class PushloopLogger {

		/**
		 * Obtains the path to the log file.
		 *
		 * @param boolean $to_send If it is to be sent.
		 * @return string
		 */
		private static function get_log_file_path( $to_send = false ) {
			$directory = PUSHLOOP_PATH . 'logs/';
			if ( $to_send ) {
				$filename = gmdate( 'Y-m-d', strtotime( '-1 days' ) ) . '_' . sanitize_file_name( get_bloginfo( 'name' ) ) . '.log';
			} else {
				$filename = gmdate( 'Y-m-d' ) . '_' . sanitize_file_name( get_bloginfo( 'name' ) ) . '.log';
			}

			return $directory . $filename;
		}

		/**
		 * Log an informative message.
		 *
		 * @param string $message Message.
		 * @return void
		 */
		public static function info( $message ) {
			self::write_log( 'INFO', $message );
		}

		/**
		 * Log a warning message.
		 *
		 * @param string $message Message.
		 * @return void
		 */
		public static function warning( $message ) {
			self::write_log( 'WARNING', $message );
		}

		/**
		 * Log an error message.
		 *
		 * @param string $message Message.
		 * @return void
		 */
		public static function error( $message ) {
			self::write_log( 'ERROR', $message );
		}

		/**
		 * It writes a message in the log file.
		 *
		 * @param string $level Log level.
		 * @param string $message Message.
		 * @return void
		 */
		private static function write_log( $level, $message ) {
			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				WP_Filesystem();
			}
			$log_file = self::get_log_file_path();
			if ( ! file_exists( dirname( $log_file ) ) ) {
				wp_mkdir_p( dirname( $log_file ) );
			}

			$formatted_message = sprintf( '[%s] %s: %s%s', gmdate( 'Y-m-d H:i:s' ), $level, $message, PHP_EOL );

			$old_contents = $wp_filesystem->get_contents( $log_file );
			if ( false === $old_contents ) {
				$old_contents = '';
			}
			$content = $old_contents . $formatted_message;
			$wp_filesystem->put_contents( $log_file, $content );
		}


		/**
		 * Send the log file of the previous day.
		 *
		 * @return void
		 */
		public static function send_file() {
			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				WP_Filesystem();
			}
			$log_file = self::get_log_file_path( true );
			if ( ! file_exists( $log_file ) ) {
				self::error( 'File Not Found' );
				return;
			}
			$post_fields = array(
				'siteName' => sanitize_file_name( get_bloginfo( 'name' ) ),
			);

			$url      = 'https://api.pushloop.io/api/v2/wordpressLog';
			$boundary = wp_generate_password( 24, false );
			$eol      = "\r\n";
			$body     = '';

			foreach ( $post_fields as $name => $value ) {
				$body .= '--' . $boundary . $eol;
				$body .= 'Content-Disposition: form-data; name="' . $name . '"' . $eol . $eol;
				$body .= $value . $eol;
			}

			$body .= '--' . $boundary . $eol;
			$body .= 'Content-Disposition: form-data; name="file"; filename="' . $log_file . '"' . $eol;
			$body .= 'Content-Type: ' . mime_content_type( $log_file ) . $eol . $eol;
			$body .= $wp_filesystem->get_contents( $log_file );
			( $log_file ) . $eol;
			$body .= '--' . $boundary . '--' . $eol;

			$headers = array(
				'ApiKey'       => get_option( 'pushloop_api_key' ),
				'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
			);

			$args = array(
				'headers'     => $headers,
				'body'        => $body,
				'timeout'     => 15,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'cookies'     => array(),
			);

			$response = wp_remote_post( $url, $args );

			if ( is_wp_error( $response ) ) {
				self::error( 'SendLogResponse ' . wp_json_encode( $response ) );
			} else {
				self::info( 'SendLogResponse ' . wp_json_encode( $response ) );
				wp_delete_file( $log_file );
			}
		}

		/**
		 * Cleans up the logs folder.
		 */
		public static function clean_log_folder() {
			$directory = PUSHLOOP_PATH . 'logs/';
			$files     = glob( $directory . '/*' );

			if ( count( $files ) > 2 ) {
				foreach ( $files as $file ) {
					if ( is_file( $file ) ) {
						wp_delete_file( $file );
					}
				}
				self::warning( 'clean_log_folder: delete all files' );
			}
		}
	}
}
