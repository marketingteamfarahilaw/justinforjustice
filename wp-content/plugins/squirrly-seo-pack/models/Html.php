<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Html {

	const field_div_class = 'sq-col-12 sq-row sq-m-2 sq-p-2 sq-text-left sq_field';
	const label_div_class = 'sq-col-3 sq-p-0 sq-pr-3 sq-text-left sq-font-weight-bold';
	const input_div_class = 'sq-col-8 sq-p-0 sq-text-left sq-input-group';
	const input_class = 'sq-form-control';
	const radio_input_class = 'sq-form-check-input';
	const select_input_class = 'sq-form-control sq-pr-5';
	const pattern_class = 'sq_pattern_field';
	const advanced_class = 'sq_advanced';
	const require_div_class = 'sq_required';
	const recommended_div_class = 'sq_recommended';

	/**
	 * Generate the Modal Dialog
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	public function generateModal( $html, $title ) {
		return '
            <div tabindex="-1" class="sq-modal" role="dialog">
                    <div class="sq-modal-dialog sq-modal-lg">
                        <div class="sq-modal-content bg-white rounded-0">
                            <div class="sq-modal-header border-0 px-5 pt-5 pb-0">
                                <h4 class="sq-modal-title sq-p-1 sq-m-0">' . $title . ':</h4>
                                <button type="button" class="sq-close" data-dismiss="sq-modal">&times;</button>
                            </div>
                            <div class="sq-modal-body px-5 pt-2 pb-5">' . $html . '</div>
                        </div>
                    </div>
            </div>';
	}

	/**
	 * @param string $html
	 * @param string $name
	 * @param string $action
	 * @param SQP_Models_Domain_Jsonld $jsonldDomain
	 *
	 * @return string
	 */
	public function createForm( $html, $name, $action, $jsonldDomain ) {
		$form = '<form class="' . $name . '" method="post">
			' . SQP_Classes_Helpers_Tools::setNonce( $action, 'sq_nonce', true, false ) . '
			<input type="hidden" name="action" value="' . $action . '"/>
			<input type="hidden" name="post_id" value="' . $jsonldDomain->post_id . '">
	        <input type="hidden" name="post_type" value="' . $jsonldDomain->post_type . '">
	        <input type="hidden" name="term_id" value="' . $jsonldDomain->term_id . '">
	        <input type="hidden" name="taxonomy" value="' . $jsonldDomain->taxonomy . '">
	        <input type="hidden" name="jsonld_type" value="' . $jsonldDomain->jsonld_type . '">
	        ' . $html . '
	        ' . $this->createButton( esc_html__( 'Save', 'squirrly-seo-pack' ), 'submit', $this->getClassAttribute( [ 'sq-btn sq-btn-primary sq-m-3 sq-px-5' ] ) ) . '
	        </form>';

		return $form;
	}

	/**
	 * Create a group for the current row
	 *
	 * @param array $row
	 *
	 * @return string
	 */
	public function createGroup( $row ) {
		$addButton = $deleteButton = '';

		//if it's array of values
		if ( isset( $row['index'] ) && $row['index'] !== false ) {

			if ( $row['index'] > 0 ) { //from the second index
				$attributes   = array();
				$attributes[] = $this->getConfirmAttribute( esc_html__( 'Are you sure?', 'squirrly-seo-pack' ) );
				$attributes[] = $this->getClassAttribute( [ 'sq_jsonld_delete_item sq-float-right sq-btn sq-btn-link sq-btn-sm sq-m-0 sq-p-0' ] );

				$deleteButton = $this->createButton( $this->createIcon( 'delete' ), 'button', join( " ", $attributes ) );
			}

		}

		//Prepare the Header
		$attributes   = array();
		$attributes[] = $this->getClassAttribute( [ 'sq_group_header sq-text-left' ] );
		$attributes[] = $this->getDependency( $row );
		$header       = $this->createFieldDiv( $row['title'] . $deleteButton, join( " ", $attributes ) );


		//Prepare the group
		$attributes   = array();
		$group        = ( empty( $row['parents'] ) ? 'sq_group' : end( $row['parents'] ) . ' ' . 'sq_subgroup' );
		$attributes[] = $this->getClassAttribute( [ $group ] );

		return $this->createFieldDiv( $header . $row['html'] . $addButton, join( " ", $attributes ) );
	}

	public function createSearchBar() {
		$field = array(
			'field' => array(
				'label' => esc_html__( 'Search Schema', 'squirrly-seo-pack' ),
				'id'    => 'search_name',
				'name'  => 'search',
			)
		);

		return $this->createFieldDiv( $this->createField( $field ), $this->getClassAttribute( [ 'sq_search_schema sq-col-12 sq-py-0 sq-px-4 sq-m-0 sq-border-bottom' ] ) );
	}

	/**
	 * @param string $html
	 * @param string $attributes
	 *
	 * @return string
	 */
	public function createFieldDiv( $html, $attributes = '', $help = '' ) {
		return '<div ' . $attributes . '>' . $html . $help . '</div>';
	}

	/**
	 * Create notification for a field
	 *
	 * @param $notification
	 *
	 * @return string|void
	 */
	public function createNotification( $notification ) {
		if ( isset( $notification['status'] ) && isset( $notification['content'] ) ) {

			return '<div class="' . 'sq-alert sq-alert-' . $notification['status'] . ' sq-my-3 sq-p-3">' . $notification['content'] . '</div>';

		}
	}

	/**
	 * Create the filed and return the HTML code
	 *
	 * @param array $row
	 *
	 * @return string
	 */
	public function createField( $row ) {

		$fields = $help = array();

		$field                  = $row['field'];
		$field['isRequired']    = ( $row['isRequired'] ?? false );
		$field['isRecommended'] = ( $row['isRecommended'] ?? false );

		if ( isset( $field['multiple'] ) && $field['multiple'] ) {
			$field['name'] .= '[]'; //add the array name for multiple values
		}

		// set field type
		if ( ! isset( $field['type'] ) ) {
			$field['type'] = 'text';
		}

		//set the value
		if ( ! isset( $field['value'] ) && isset( $field['default'] ) ) {
			$field['value'] = $field['default'];
		}
		if ( ! isset( $field['value'] ) && isset( $field['placeholder'] ) ) {
			$field['value'] = $field['placeholder'];
		}

		$field_array = array();
		if ( isset( $field['label'] ) ) {
			$classes   = array();
			$classes[] = self::label_div_class;
			if ( isset( $field['isRequired'] ) && $field['isRequired'] ) {
				$classes[] = self::require_div_class;
			}
			if ( isset( $field['isRecommended'] ) && $field['isRecommended'] ) {
				$classes[] = self::recommended_div_class;
			}
			if ( isset( $field['id'] ) ) {
				$field_array[] = $this->createFieldDiv( $this->createLabel( $field['id'], $field['label'] ), $this->getClassAttribute( $classes ) );
			}
		}

		$classes   = array();
		$classes[] = self::input_div_class;


		if ( in_array( $field['type'], array(
				"text",
				"datetimepicker",
				"datepicker"
			) ) && ! in_array( $field['name'], array( "name", "search" ) ) ) {
			$row['isPattern'] = true;
		}

		//add patterns if exists
		if ( isset( $row['isPattern'] ) && $row['isPattern'] ) {
			$classes[] = self::pattern_class;
		}

		//Add the field class in the parent div
		if ( isset( $field['classes'] ) ) {
			$classes[]        = $field['classes'];
			$field['classes'] = false;
		}

		//add field help id exists
		if ( isset( $field['help'] ) ) {
			$help[] = $this->createFieldDiv( $field['help'], $this->getClassAttribute( [ 'sq-help' ] ) );
		}

		switch ( $field['type'] ) {
			case 'text':
			case 'number':
			case 'hidden':
				$input = $this->createTextField( $field );
				break;
			case 'textarea':
				$input = $this->createTextAreaField( $field );
				break;
			case 'timepicker':
				$classes[] = 'timepicker';
				$input     = $this->createDateTimePickerField( $field );
				break;
			case 'datetimepicker':
				$classes[] = 'datetimepicker';
				$input     = $this->createDateTimePickerField( $field );
				break;
			case 'datepicker':
				$classes[] = 'datepicker';
				$input     = $this->createDateTimePickerField( $field );
				break;
			case 'radio':
				$input = $this->createRadioField( $field );
				break;
			case 'select':
				$input = $this->createSelectField( $field );
				break;
		}

		//add the field in class
		$field_array[] = $this->createFieldDiv( $input, $this->getClassAttribute( $classes ), join( ' ', $help ) );

		//add notice if exists
		if ( isset( $field['notice'] ) ) {
			$field_array[] = $this->createNotification( $field['notice'] );
		}

		$classes   = $attributes = array();
		$classes[] = self::field_div_class;

		//Check if it's an advanced field
		if ( isset( $row['isAdvanced'] ) && $row['isAdvanced'] ) {
			$classes[] = self::advanced_class;
		}

		$attributes[] = $this->getDependency( $row );
		$attributes[] = $this->getClassAttribute( $classes );

		$fields[] = $this->createFieldDiv( join( PHP_EOL, $field_array ), join( ' ', $attributes ) );

		return join( PHP_EOL, $fields );

	}

	/**
	 * Create button
	 *
	 * @param $name
	 * @param $attributes
	 *
	 * @return string
	 */
	public function createButton( $name, $type, $attributes = '' ) {
		return '<button type="' . $type . '" ' . $attributes . ' >' . $name . '</button>';
	}

	/**
	 * Create an favicon for delete/add/edit
	 *
	 * @param $name
	 * @param $attributes
	 *
	 * @return string
	 */
	public function createIcon( $name, $type = 'delete' ) {

		switch ( $type ) {
			case 'add':
				$type = 'plus';
				break;
			case 'edit':
				$type = 'fa-pencil-square-o';
				break;
			case 'delete':
				$type = 'times';
				break;
		}

		return '<i class="fa-solid fa-' . $type . '" ></i>';
	}

	/**
	 * Create a text/number field
	 *
	 * @param array $field
	 *
	 * @return string
	 */
	public function createTextField( $field ) {

		$input        = $attributes = array();
		$attributes[] = $this->getIdAttribute( $field );
		$attributes[] = $this->getNameAttribute( $field );
		$attributes[] = $this->getClasses( $field );
		$attributes[] = $this->getPlaceholderAttribute( $field );
		$attributes[] = $this->getValueAttribute( $field );
		$attributes[] = $this->getIsRequired( $field );

		$input[] = '<input type="' . $field['type'] . '" ' . join( ' ', $attributes ) . ' />';

		return join( PHP_EOL, $input );

	}

	/**
	 * Create a textarea field
	 *
	 * @param array $field
	 *
	 * @return string
	 */
	public function createTextAreaField( $field ) {

		$input        = $attributes = array();
		$attributes[] = $this->getIdAttribute( $field );
		$attributes[] = $this->getNameAttribute( $field );
		$attributes[] = $this->getClasses( $field );
		$attributes[] = $this->getPlaceholderAttribute( $field );
		$attributes[] = $this->getIsRequired( $field );

		$input[] = '<textarea ' . join( ' ', $attributes ) . ' >' . $this->getValueAttribute( $field ) . '</textarea>';

		return join( PHP_EOL, $input );
	}

	/**
	 * Create a datetimepicker field
	 *
	 * @param array $field
	 *
	 * @return string
	 */
	public function createDateTimePickerField( $field ) {

		$input        = $attributes = array();
		$attributes[] = $this->getIdAttribute( $field );
		$attributes[] = $this->getNameAttribute( $field );
		$attributes[] = $this->getClasses( $field );
		$attributes[] = $this->getPlaceholderAttribute( $field );
		$attributes[] = $this->getValueAttribute( $field );
		$attributes[] = $this->getIsRequired( $field );

		$input[] = '<input type="text" autocomplete="off" ' . join( ' ', $attributes ) . ' />';

		return join( PHP_EOL, $input );
	}

	/**
	 * Create an input field
	 *
	 * @param array $field
	 *
	 * @return string
	 */
	private function createRadioField( $field ) {

		$options      = $attributes = array();
		$attributes[] = $this->getNameAttribute( $field );
		$attributes[] = $this->getClasses( $field );
		$attributes[] = $this->getIsRequired( $field );

		if ( ! isset( $field['value'] ) ) {
			$field['value'] = current( array_keys( $field['options'] ) );
		}

		if ( isset( $field['options'] ) && ! empty( $field['options'] ) ) {
			foreach ( $field['options'] as $value => $title ) {
				$option    = array();
				$option[]  = '<input type="radio" id="' . $field['id'] . $value . '" ' . join( ' ', $attributes ) . $this->getIsChecked( $field, $value ) . ' value="' . $value . '" >';
				$option[]  = $this->createLabel( $field['id'] . $value, $title, $this->getClassAttribute( [ 'sq-form-check-label' ] ) );
				$options[] = $this->createFieldDiv( join( PHP_EOL, $option ), $this->getClassAttribute( [
					'sq_option',
					'sq-form-check'
				] ) );

			}
		}

		return join( PHP_EOL, $options );
	}

	public function createSelectField( $field ) {

		$select = $attributes = array();

		if ( isset( $field['multiple'] ) && $field['multiple'] ) {
			$attributes[] = 'multiple';
			$attributes[] = 'style="height: auto;max-height: none;"';
		}

		$attributes[] = $this->getNameAttribute( $field );
		$attributes[] = $this->getClasses( $field );
		$attributes[] = $this->getIsRequired( $field );

		if ( ! isset( $field['value'] ) ) {
			$field['value'] = current( array_keys( $field['options'] ) );
		}

		if ( isset( $field['options'] ) && ! empty( $field['options'] ) ) {
			$select[] = '<select ' . join( ' ', $attributes ) . '>';
			foreach ( $field['options'] as $value => $title ) {
				$select[] = '<option value="' . $value . '" ' . $this->getIsSelect( $field, $value ) . ' >' . $title . '</option>';
			}
			$select[] = '<select>';
		}

		return $this->createFieldDiv( join( PHP_EOL, $select ), $this->getClassAttribute( [
			'sq_select',
			'sq-form-select'
		] ) );
	}

	/**
	 * Create a label for the current field
	 *
	 * @param string $id
	 * @param string $label
	 * @param string $attributes
	 *
	 * @return string
	 */
	public function createLabel( $id, $label, $attributes = '' ) {

		if ( $label <> '' ) {
			return '<label ' . $attributes . ' for="' . $id . '">' . $label . '</label>';
		}

		return '';
	}

	/**
	 * @param array $field
	 *
	 * @return string
	 */
	public function getIdAttribute( $field ) {

		if ( isset( $field['id'] ) && $field['id'] <> '' ) {
			return 'id="' . $field['id'] . '"';
		}

		return '';
	}

	/**
	 * @param array $field
	 *
	 * @return string
	 */
	public function getNameAttribute( $field ) {

		if ( isset( $field['name'] ) && $field['name'] <> '' ) {
			return 'name="' . $field['name'] . '"';
		}

		return '';
	}

	/**
	 * @param $classes
	 *
	 * @return string
	 */
	public function getClassAttribute( $classes ) {
		$title = '';

		if ( ! is_array( $classes ) ) {
			return '';
		}

		if ( in_array( self::require_div_class, $classes ) ) {
			$title = esc_attr__( 'Required Field', 'squirrly-seo-pack' ) . ' ';
		}

		if ( in_array( self::recommended_div_class, $classes ) ) {
			$title = esc_attr__( 'Recommended Field', 'squirrly-seo-pack' ) . ' ';
		}

		return 'class="' . join( ' ', $classes ) . '" ' . ( $title ? 'title="' . $title . '"' : '' );
	}

	/**
	 * @param $text
	 *
	 * @return string
	 */
	public function getConfirmAttribute( $text ) {
		return 'data-confirm="' . $text . '"';
	}

	/**
	 * @param array $field
	 *
	 * @return string
	 */
	public function getPlaceholderAttribute( $field ) {

		if ( isset( $field['placeholder'] ) && $field['placeholder'] <> '' ) {
			return 'placeholder="' . $field['placeholder'] . '"';
		}

		return '';
	}

	/**
	 * @param array $field
	 *
	 * @return string
	 */
	public function getValueAttribute( $field ) {

		if ( ! isset( $field['value'] ) ) {
			return '';
		}

		if ( $field['type'] == 'textarea' ) {
			return $field['value'];
		}

		return 'value="' . SQP_Classes_Helpers_Sanitize::sanitizeField( $field['value'] ) . '"';
	}

	/**
	 * @param array $field
	 *
	 * @return string
	 */
	public function getIsRequired( $field ) {

		if ( isset( $field['isRequired'] ) && $field['isRequired'] ) {
			return 'required="required"';
		}

		return '';
	}


	/**
	 * Get the check option if values match
	 *
	 * @param $field
	 * @param $value
	 *
	 * @return string
	 */
	public function getIsChecked( $field, $value ) {

		if ( ! isset( $field['value'] ) ) {
			return '';
		}

		return checked( $field['value'], $value, false );
	}

	/**
	 * Get the check option if values match
	 *
	 * @param $field
	 * @param $value
	 *
	 * @return string
	 */
	public function getIsSelect( $field, $value ) {

		if ( ! isset( $field['value'] ) ) {
			return '';
		}

		if ( is_array( $field['value'] ) && in_array( $value, $field['value'] ) ) {
			$field['value'] = $value;
		}

		return selected( $field['value'], $value, false );
	}

	/**
	 * @param array $field
	 *
	 * @return string
	 */
	public function getClasses( $field ) {
		$classes = array();

		switch ( $field['type'] ) {
			case 'radio':
				$classes[] = self::radio_input_class;
				break;
			case 'select':
				$classes[] = self::select_input_class;
				break;
			default:
				$classes[] = self::input_class;
		}

		if ( isset( $field['classes'] ) ) {
			$classes[] = $field['classes'];
		}

		return $this->getClassAttribute( $classes );
	}

	/**
	 * Set the dependency
	 *
	 * @param $row
	 *
	 * @return string
	 */
	public function getDependency( $row ) {
		$attributes = array();

		if ( isset( $row['dependency'] ) ) {
			foreach ( $row['dependency'] as $dependency ) {
				if ( isset( $dependency['field'] ) && isset( $dependency['value'] ) ) {
					$attributes[] = 'data-dependency="' . $dependency['field'] . '"';
					$attributes[] = 'data-value="' . join( ',', (array) $dependency['value'] ) . '"';
				}
			}
		}

		return join( ' ', $attributes );
	}

}
