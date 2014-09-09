<?php
/**
 * Plugin class
 */
namespace Phile\Plugin;

/**
 * the AbstractPlugin class for implementing a plugin for PhileCMS
 *
 * @author  Frank Nägler
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin
 */
abstract class AbstractPlugin {
	/**
	 * @var array the plugin settings
	 */
	protected $settings;

	/**
	 * inject settings
	 *
	 * @param array $settings
	 */
	public function injectSettings(array $settings = null) {
		$this->settings = ($settings === null) ? array() : $settings;
	}
}
