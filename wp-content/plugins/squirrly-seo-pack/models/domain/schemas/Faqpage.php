<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Faqpage extends SQP_Models_Domain_Schema {

	protected $_mainEntity;

	public function getType() {
		return 'FAQPage';
	}

	public function getMainEntity() {

		if ( $this->post->ID > 0 ) {
			$jsonModel = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld' );
			$classes   = $jsonModel->getJsonLdClasses( $this->getType() );

			//get data from post meta
			$mainEntity = $records = array();
			foreach ( $classes as $class => $item ) {
				if ( $data = get_post_meta( $this->post->ID, $class, true ) ) {
					$records[ $item ] = $data;
				}
			}

			//if there are records in post meta
			if ( ! empty( $records ) && count( $records ) == 2 ) {
				$records = $this->prepareArray( $records );

				foreach ( $records as &$record ) {
					$schema['type']                   = 'Question';
					$schema['name']                   = $record['name'];
					$schema['acceptedAnswer']['type'] = 'Answer';
					$schema['acceptedAnswer']['text'] = $record['text'];

					$record = $schema;
				}

				$mainEntity = $records;

			}


			if ( ! empty( $mainEntity ) ) {
				$this->_mainEntity = $mainEntity;
			}

		}

		return $this->_mainEntity;
	}

	private function prepareArray( $records ) {
		$outputArray = [];
		foreach ( $records as $key => $values ) {
			if ( ! empty( $values ) ) {
				foreach ( $values as $index => $value ) {
					if ( ! isset( $outputArray[ $index ] ) ) {
						$outputArray[ $index ] = [];
					}
					$outputArray[ $index ][ $key ] = $value;
				}
			}
		}

		return $outputArray;
	}

	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array|mixed|null
	 */
	public function toArray() {

		$array = array(
			'type'       => $this->type,
			'@id'        => $this->post->url . '#' . $this->type,
			'mainEntity' => $this->mainEntity,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}

}
