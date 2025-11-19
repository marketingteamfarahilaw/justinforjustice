<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Log extends SQP_Models_Abstract_Domain {

	protected $_id;
	protected $_url;
	protected $_created;
	protected $_domain;
	protected $_sent_to;
	protected $_agent;
	protected $_referrer;
	protected $_http_code;
	protected $_request_method;
	protected $_request_data;
	protected $_redirect_by;
	protected $_redirection_id;
	protected $_ip;


	const MAX_IP_LENGTH = 45;
	const MAX_DOMAIN_LENGTH = 255;
	const MAX_URL_LENGTH = 2000;
	const MAX_AGENT_LENGTH = 255;
	const MAX_REFERRER_LENGTH = 255;
	protected static $supported_methods = [
		'GET',
		'HEAD',
		'POST',
		'PUT',
		'DELETE',
		'CONNECT',
		'OPTIONS',
		'TRACE',
		'PATCH'
	];

	public function setId( $id ) {
		$this->_id = $id;
	}

	public function setUrl( $url ) {
		$this->_url = $url;
	}

	public function getUrl() {
		if ( isset( $this->_url ) ) {
			$this->_url = substr( sanitize_text_field( $this->_url ), 0, self::MAX_URL_LENGTH );
		}

		return $this->_url;
	}

	public function getDomain() {
		if ( isset( $this->_domain ) ) {
			$this->_domain = substr( sanitize_text_field( $this->_domain ), 0, self::MAX_DOMAIN_LENGTH );
		}

		return $this->_domain;
	}

	public function getIp() {
		if ( isset( $this->_ip ) ) {
			$this->_ip = substr( sanitize_text_field( $this->_ip ), 0, self::MAX_IP_LENGTH );
		}

		return $this->_ip;
	}

	public function getCreated() {
		if ( isset( $this->_created ) ) {
			$this->_created = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $this->_created ) );
		} else {
			$this->_created = current_time( 'mysql' );
		}

		return $this->_created;
	}

	public function getAgent() {
		if ( isset( $this->_agent ) ) {
			$this->_agent = substr( sanitize_text_field( $this->_agent ), 0, self::MAX_AGENT_LENGTH );
		}

		return $this->_agent;
	}

	public function getReferrer() {
		if ( isset( $this->_referrer ) ) {
			$this->_referrer = substr( sanitize_text_field( $this->_referrer ), 0, self::MAX_REFERRER_LENGTH );
		}

		return $this->_referrer;
	}

	public function getRequest_data() {
		if ( isset( $this->_request_data ) ) {
			$this->_request_data = wp_json_encode( $this->_request_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK );
		}

		return $this->_request_data;
	}

	public function getHttp_code() {
		if ( isset( $this->_http_code ) ) {
			$this->_http_code = intval( $this->_http_code, 10 );
		}

		return $this->_http_code;
	}

	public function getRequest_method() {
		if ( isset( $this->_request_method ) && $this->_request_method <> '' ) {
			$this->_request_method = strtoupper( sanitize_text_field( $this->_request_method ) );

			if ( ! in_array( $this->_request_method, static::$supported_methods, true ) ) {
				$this->_request_method = '';
			}
		}

		return $this->_request_method;
	}

	public function toArray() {
		return array(
			'id'             => $this->id,
			'url'            => $this->url,
			'created'        => $this->created,
			'domain'         => $this->domain,
			'sent_to'        => $this->sent_to,
			'agent'          => $this->agent,
			'referrer'       => $this->referrer,
			'http_code'      => (int) $this->http_code,
			'request_method' => $this->request_method,
			'request_data'   => $this->request_data,
			'redirect_by'    => $this->redirect_by,
			'redirection_id' => (int) $this->redirection_id,
			'ip'             => $this->ip,
		);
	}

}
