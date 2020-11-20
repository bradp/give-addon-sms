<?php
/**
 * SMS Functionality
 *
 */

interface Give_SMS_Interface {
	/**
	 * Get instance.
	 *
	 * @return Give_SMS_Twilio
	 *
	 */
	public static function get_instance();

	/**
	 * Get the auth tokens for sending SMS.
	 *
	 * @return array Array of auth tokens.
	 */
    public function get_auth();

    /**
     * Build the message into the format required to send it via SMS.
     *
     * @param string $message Message to send.
     *
     * @return array Array of data required to send.
     */
    public function build_message( string $message );

    /**
     * Send the SMS through a gateway.
     *
     * @param array $message Message to send.
     * @param array $auth    Auth data.
     */
    public function send_sms( array $message, array $auth );

    /**
     * Wrapper to do all the SMS sending.
     */
    public function sms_notification();

    /**
     * Add our hooks into WP.
     */
    public function add_hooks();
}

use Twilio\Rest\Client;
class Give_SMS_Twilio implements Give_SMS_Interface {
	/**
	 * Instance.
	 *
	 * @since
	 * @access private
	 * @var Give_SMS_Twilio
	 */
	private static $instance;

	/**
	 * Get instance.
	 *
	 * @return Give_SMS_Twilio
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Give_SMS_Twilio ) ) {
			self::$instance = new Give_SMS_Twilio();
			self::$instance->add_hooks();
		}

		return self::$instance;
	}

	/**
	 * Get the auth tokens for sending SMS.
	 *
	 * @return array Array of auth tokens.
	 */
	public function get_auth() {
		return [
			'sid'        => give_get_option( 'give_sms_sid' ),
			'auth_token' => give_get_option( 'give_sms_auth' ),
		];
	}

	/**
     * Build the message into the format required to send it via SMS.
     *
     * @param string $message Message to send.
     *
     * @return array Array of data required to send.
     */
	public function build_message ( string $message ) {
		return [
			'to'   => (int) give_get_option( 'give_sms_number_to' ),
			'body' => [
		        'from' => (int) give_get_option( 'give_sms_number_from' ),
		        'body' => $message,
		    ]
		];
	}

	/**
     * Send the SMS through a gateway.
     *
     * @param array $message Message to send.
     * @param array $auth    Auth data.
     */
	public function send_sms( array $message, array $auth ) {
		// Init new twilio client.
		$client = new Client( $auth['sid'], $auth['auth_token'] );

		// Send the message.
		$client->messages->create( $message['to'], $message['body'] );
	}

	/**
     * Wrapper to do all the SMS sending.
     */
    public function sms_notification() {
    	$message = __( "You've got a new donation!", 'give' );

    	$this->send_sms( $this->build_message( $message ), $this->get_auth() );
    }


	 /**
     * Add our hooks into WP.
     */
	public function add_hooks() {
		add_action( 'give_donation-receipt_email_notification', [ $this, 'sms_notification' ] );
	}
}

// Fire off our SMS stuff.
Give_SMS_Twilio::get_instance();
