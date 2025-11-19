<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Schemas_Newsarticle' );

class SQP_Models_Domain_Schemas_Article extends SQP_Models_Domain_Schemas_Newsarticle {

	public function getType() {

		if ( empty( $this->_type ) ) {
			$this->_type = 'Article';
		}

		return $this->_type;
	}

}
