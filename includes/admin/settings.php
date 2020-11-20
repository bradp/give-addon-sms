<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Example code to show how to add setting page to give settings.
 *
 * @package     Give
 * @subpackage  Classes/Give_SMS_Admin_Settings
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Give_SMS_Admin_Settings extends Give_Settings_Page {

	/**
	 * Give_SMS_Admin_Settings constructor.
	 */
	function __construct() {
		$this->id    = 'give-bd-setting-fields';
		$this->label = esc_html__( 'Give SMS' );

		$this->default_tab = 'twilio';

		parent::__construct();
	}


	/**
	 * Add setting sections.
	 *
	 * @return array
	 */
	function get_sections() {
		$sections = array(
			'twilio'     => __( 'Twilio', 'give' ),
		);

		return $sections;
	}


	/**
	 * Get setting.
	 *
	 * @return array
	 */
	function get_settings() {
		$current_section = give_get_current_setting_section();

		return array(

			/**
			 * Text field setting.
			 */
			array(
				'name' => esc_html__( 'Account', 'give' ),
				'desc' => '',
				'id'   => 'text_field_setting',
				'type' => 'title',
			),
			array(
				'name' => esc_html__( 'Account SID', 'give' ),
				'desc' => '',
				'id'   => 'give_sms_sid',
				'type' => 'text',
			),
			array(
				'name' => esc_html__( 'Auth Token', 'give' ),
				'desc' => '',
				'id'   => 'give_sms_auth',
				'type' => 'text',
			),
			array(
				'id'   => 'text_field_setting',
				'type' => 'sectionend',
			),

			/**
			 * Number field setting.
			 */
			array(
				'name' => esc_html__( 'Phone Number', 'give' ),
				'desc' => '',
				'id'   => 'number_field_setting',
				'type' => 'title',
			),
			array(
				'name' => esc_html__( 'Number to Send Messages', 'give' ),
				'desc' => '',
				'id'   => 'give_sms_number_from',
				'type' => 'number',
				'css'  => 'width:12em;',
			),
			array(
				'name' => esc_html__( 'Number to Recieve Messages', 'give' ),
				'desc' => '',
				'id'   => 'give_sms_number_to',
				'type' => 'number',
				'css'  => 'width:12em;',
			),
			array(
				'id'   => 'give_number_field_settings',
				'type' => 'sectionend',
			),
		);
	}
}

