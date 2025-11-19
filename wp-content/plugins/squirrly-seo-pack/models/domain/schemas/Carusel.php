<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Carusel extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_itemListElement;
	protected $_schema;

	public function getItemListElement() {
		if ( ! empty( $this->_itemListElement ) ) {
			foreach ( $this->_itemListElement as &$element ) {
				if ( isset( $element['url'] ) && isset( $element['schema'] ) ) {
					$element['url'] = ( $element['url'] == '{{url}}' || $element['url'] == '' ) ? $this->post->url : $element['url'];
					$element['url'] .= '#' . $element['schema'];
					unset( $element['schema'] );
				}
			}
		}

		return $this->_itemListElement;
	}

	public function toArray() {

		$array = array(
			'type'            => $this->type,
			'@id'             => $this->post->url . "#" . strtolower( $this->type ),
			'itemListElement' => $this->itemListElement,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );
	}


}
