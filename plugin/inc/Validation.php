<?php

class Validation {
	public static function validate_plugins( $plugins ) {
		$installed_plugins = get_plugins();

		$validated_plugins = array_map( function ( $plugin ) use ( $installed_plugins ) {
			$validation = array_merge([
				'valid'   => false,
				'message' => 'Invalid plugin',
			], (array) $plugin );

			if ( validate_file( $plugin->name ) ) {
				$validation['message'] = 'Invalid plugin path';
				return $validation;
			}

			if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin->name ) ) {
				$validation['message'] = 'Plugin file does not exist';
				return $validation;
			}

			$is_installed = false;

			foreach ( $installed_plugins as $key => $value ) {
				if ( preg_match( "/{$plugin->name}/", $key ) ) {
					$is_installed = true;
					break;
				}
			}

			if ( ! $is_installed ) {
				$validation['message'] = 'The plugin does not have a valid header';
				return $validation;
			}

			$validation['valid']   = true;
			$validation['message'] = 'Valid plugin';

			return $validation;
		}, $plugins );

		usort ( $validated_plugins, function ( $left, $right ) {
			return $left['valid'] - $right['valid'];
		});

		return $validated_plugins;
	}

	public static function validate_theme( $theme ) {
		$validation = [
			'valid'   => false,
			'message' => 'Invalid Theme',
		];

		$existing_themes = wp_get_themes( array( 'errors' => null ) );
		$theme           = $existing_themes[ $theme ];

		if ( ! isset( $theme ) ) {
			return $validation;
		}

		$errors = $theme->errors();

		if ( is_wp_error( $errors ) ) {
			$validation['message'] = $errors->get_error_message();
			return $validation;
		}

		$validation['valid']   = true;
		$validation['message'] = 'Valid theme';

		return $validation;
	}

	public static function active_theme_name() {
		$theme = wp_get_theme();
		return $theme->name;
	}
}
