<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

abstract class SQP_Models_Abstract_Post extends SQP_Models_Abstract_Domain {
	protected $_post_id;
	protected $_term_id;
	protected $_taxonomy;
	protected $_post_type;

	/** @var SQ_Models_Domain_Post */
	protected $_post;

	public function getPost() {
		global $sq_post;

		if ( ! empty( $sq_post ) ) {
			//set the current post once is set
			$this->_post = $sq_post;
		}

		if ( empty( $this->_post ) ) {

			if ( class_exists( 'SQ_Classes_ObjController' ) ) {
				/** @var SQ_Models_Snippet $snippet */
				$snippet = SQ_Classes_ObjController::getClass( 'SQ_Models_Snippet' );
				if ( ! $this->_post = $sq_post = $snippet->getCurrentSnippet( $this->_post_id, $this->_term_id, $this->_taxonomy, $this->_post_type ) ) {

					//Set the post as an empty post model to avoid any error
					$this->_post     = SQ_Classes_ObjController::getDomain( 'SQ_Models_Domain_Post' );
					$this->_post->sq = SQ_Classes_ObjController::getDomain( 'SQ_Models_Domain_Sq' );
				}
			}
		}

		return $this->_post;
	}


	/**
	 * Get the image from post
	 *
	 * @return array|false
	 */
	public function getPostImage() {
		if ( ! $this->post && ! isset( $this->post->ID ) && (int) $this->post->ID == 0 ) {
			return false;
		}

		if ( has_post_thumbnail( $this->post->ID ) ) {
			$attachment = get_post( get_post_thumbnail_id( $this->post->ID ) );
		}

		if ( isset( $attachment->ID ) ) {
			$url = wp_get_attachment_image_src( $attachment->ID, 'full' );

			if ( isset( $url[0] ) ) {
				return array(
					'url'    => esc_url( $url[0] ),
					'width'  => $url[1],
					'height' => $url[2],
				);
			}
		}


		$post = get_post( $this->post->ID );

		if ( isset( $post->post_content ) ) {
			preg_match( '/<img[^>]*src="([^"]*)"[^>]*>/i', $post->post_content, $match );

			if ( ! empty( $match ) ) {

				if ( strpos( $match[1], '//' ) === false && strpos( $match[1], 'data:image' ) === false ) {
					$match[1] = get_bloginfo( 'url' ) . $match[1];
				} elseif ( strpos( $match[1], '//' ) === 0 ) {
					$match[1] = ( is_ssl() ? 'https:' : 'http:' ) . $match[1];
				}

				return array(
					'url'    => esc_url( $match[1] ),
					'width'  => 500,
					'height' => 500,
				);
			}
		}

		return false;

	}

	/**
	 * Get the video from content
	 *
	 * @return array|false
	 */
	public function getPostVideo() {
		$image = '';
		if ( ! $this->post && ! isset( $this->post->ID ) && (int) $this->post->ID == 0 ) {
			return false;
		}

		if ( $image = $this->getPostImage() ) {
			if ( isset( $image['url'] ) ) {
				$image = $image['url'];
			}
		}

		if ( SQP_Classes_Helpers_Tools::isPluginInstalled( 'advanced-custom-fields/acf.php' ) ) {
			if ( isset( $this->post->ID ) && $this->post->ID ) {
				if ( $_sq_video = get_post_meta( $this->post->ID, '_sq_video', true ) ) {

					//get the image from the YouTube video
					preg_match( '/(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:watch\?v=))([^&\"\'<>\s]+)/si', $_sq_video, $match );
					if ( isset( $match[1] ) && $match[1] <> '' ) {
						$image = 'https://img.youtube.com/vi/' . $match[1] . '/hqdefault.jpg';
					}

					return array(
						'image' => $image,
						'url'   => esc_url( $_sq_video ),
					);
				}
			}
		}

		if ( isset( $this->post->post_content ) ) {

			preg_match( '/(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed)\/)([^\?&\"\'<>\s]+)/si', $this->post->post_content, $match );

			if ( isset( $match[0] ) ) {
				if ( strpos( $match[0], '//' ) !== false && strpos( $match[0], 'http' ) === false ) {
					$match[0] = 'https:' . $match[0];
				}

				return array(
					'image' => $image,
					'url'   => esc_url( $match[0] ),
				);
			}


			preg_match_all( '/(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:watch\?v=))([^&\"\'\<>\s]+)/si', $this->post->post_content, $matches, PREG_SET_ORDER );

			if ( ! empty( $matches ) ) {
				foreach ( $matches as $match ) {
					if ( isset( $match[0] ) ) {

						if ( isset( $match[1] ) && $match[1] <> '' ) {
							$image = 'https://img.youtube.com/vi/' . $match[1] . '/hqdefault.jpg';

							return array(
								'image' => $image,
								'url'   => 'https://www.youtube.com/embed/' . $match[1],
							);
						}
					}
				}

			}


			preg_match( '/(?:http(?:s)?:\/\/)?(?:fwd4\.wistia\.com\/(?:medias)\/)([^\?&\"\'<>\s]+)/si', $this->post->post_content, $match );

			if ( isset( $match[0] ) ) {
				return array(
					'image' => $image,
					'url'   => esc_url( 'https://fast.wistia.net/embed/iframe/' . $match[1] ),
				);
			}

			preg_match( '/class=["|\']([^"\']*wistia_async_([^\?&\"\'<>\s]+)[^"\']*["|\'])/si', $this->post->post_content, $match );

			if ( isset( $match[0] ) ) {
				return array(
					'image' => $image,
					'url'   => esc_url( 'https://fast.wistia.net/embed/iframe/' . $match[2] ),
				);
			}

			preg_match( '/src=["|\']([^"\']*(.mpg|.mpeg|.mp4|.mov|.wmv|.asf|.avi|.ra|.ram|.rm|.flv)["|\'])/i', $this->post->post_content, $match );

			if ( isset( $match[1] ) ) {
				return array(
					'image' => $image,
					'url'   => esc_url( $match[1] ),
				);
			}

		}

		return false;

	}

	public function isPattern( $value ) {
		return preg_match( '/{{([^\%]+)}}/s', $value ) || preg_match( '/%([^\%]+)%/s', $value ) || preg_match( '/{{([^\%]+)}}/s', $value );
	}

	public function cleanText( $text ) {
		return str_replace( array( '&#034;', '&#8220;', '&#8221;' ), '"', $text );
	}

	public function truncate( $text, $min = 100, $max = 110 ) {
		return SQ_Classes_Helpers_Sanitize::truncate( $text, $min, $max );
	}

}
