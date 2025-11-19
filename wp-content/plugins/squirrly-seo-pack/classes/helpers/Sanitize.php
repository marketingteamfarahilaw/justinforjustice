<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Classes_Helpers_Sanitize {

	/**
	 * Escape the parameter for html
	 *
	 * @param callable $function
	 * @param array $array
	 *
	 * @return array
	 */
	public static function sanitizeArrayFields( $function, $array ) {
		if ( is_array( $array ) ) {
			if ( ! empty( $array ) ) {
				foreach ( $array as &$value ) {
					$value = self::sanitizeArrayFields( $function, $value );
				}
			}
		} elseif ( $function == 'sanitizeField' ) {
			return self::sanitizeField( $array );
		} else {
			return $function( $array );
		}

		return $array;
	}

	/**
	 * Clear the field string
	 *
	 * @param  $value
	 *
	 * @return mixed|null|string|string[]
	 */
	public static function sanitizeField( $value ) {

		if ( is_array( $value ) ) {
			return array_map( array( 'SQP_Classes_Helpers_Sanitize', 'sanitizeField' ), $value );
		}

		if ( is_string( $value ) && $value <> '' ) {

			$search = array(
				"'<!--(.*?)-->'is",
				"'<script[^>]*?>.*?<\/script>'si", // strip out javascript
				"'<style[^>]*?>.*?<\/style>'si", // strip out styles
				"'<form.*?<\/form>'si",
				"'<iframe.*?<\/iframe>'si",
				"'&lt;!--(.*?)--&gt;'is",
				"'&lt;script&gt;.*?&lt;\/script&gt;'si", // strip out javascript
				"'&lt;style&gt;.*?&lt;\/style&gt;'si", // strip out styles
			);
			$value  = preg_replace( $search, "", $value );

			$search = array(
				"/&nbsp;/i",
				"/\s{2,}/",
			);
			$value  = preg_replace( $search, " ", $value );

			//more sanitization
			$value = wp_strip_all_tags( $value );
			$value = ent2ncr( $value );
			$value = trim( $value );

			$value = SQP_Classes_Helpers_Sanitize::i18n( $value );

		}

		return $value;
	}

	/**
	 * Replace language-specific characters by ASCII-equivalents.
	 *
	 * @param string $s
	 *
	 * @return string
	 */
	public static function normalizeChars( $s ) {
		$replace = array(
			'ъ' => '-',
			'Ь' => '-',
			'Ъ' => '-',
			'ь' => '-',
			'Ă' => 'A',
			'Ą' => 'A',
			'À' => 'A',
			'Ã' => 'A',
			'Á' => 'A',
			'Æ' => 'A',
			'Â' => 'A',
			'Å' => 'A',
			'Ä' => 'Ae',
			'Þ' => 'B',
			'Ć' => 'C',
			'ץ' => 'C',
			'Ç' => 'C',
			'È' => 'E',
			'Ę' => 'E',
			'É' => 'E',
			'Ë' => 'E',
			'Ê' => 'E',
			'Ğ' => 'G',
			'İ' => 'I',
			'Ï' => 'I',
			'Î' => 'I',
			'Í' => 'I',
			'Ì' => 'I',
			'Ł' => 'L',
			'Ñ' => 'N',
			'Ń' => 'N',
			'Ø' => 'O',
			'Ó' => 'O',
			'Ò' => 'O',
			'Ô' => 'O',
			'Õ' => 'O',
			'Ö' => 'Oe',
			'Ş' => 'S',
			'Ś' => 'S',
			'Ș' => 'S',
			'Š' => 'S',
			'Ț' => 'T',
			'Ù' => 'U',
			'Û' => 'U',
			'Ú' => 'U',
			'Ü' => 'Ue',
			'Ý' => 'Y',
			'Ź' => 'Z',
			'Ž' => 'Z',
			'Ż' => 'Z',
			'â' => 'a',
			'ǎ' => 'a',
			'ą' => 'a',
			'á' => 'a',
			'ă' => 'a',
			'ã' => 'a',
			'Ǎ' => 'a',
			'а' => 'a',
			'А' => 'a',
			'å' => 'a',
			'à' => 'a',
			'א' => 'a',
			'Ǻ' => 'a',
			'Ā' => 'a',
			'ǻ' => 'a',
			'ā' => 'a',
			'ä' => 'ae',
			'æ' => 'ae',
			'Ǽ' => 'ae',
			'ǽ' => 'ae',
			'б' => 'b',
			'ב' => 'b',
			'Б' => 'b',
			'þ' => 'b',
			'ĉ' => 'c',
			'Ĉ' => 'c',
			'Ċ' => 'c',
			'ć' => 'c',
			'ç' => 'c',
			'ц' => 'c',
			'צ' => 'c',
			'ċ' => 'c',
			'Ц' => 'c',
			'Č' => 'c',
			'č' => 'c',
			'Ч' => 'ch',
			'ч' => 'ch',
			'ד' => 'd',
			'ď' => 'd',
			'Đ' => 'd',
			'Ď' => 'd',
			'đ' => 'd',
			'д' => 'd',
			'Д' => 'D',
			'ð' => 'd',
			'є' => 'e',
			'ע' => 'e',
			'е' => 'e',
			'Е' => 'e',
			'Ə' => 'e',
			'ę' => 'e',
			'ĕ' => 'e',
			'ē' => 'e',
			'Ē' => 'e',
			'Ė' => 'e',
			'ė' => 'e',
			'ě' => 'e',
			'Ě' => 'e',
			'Є' => 'e',
			'Ĕ' => 'e',
			'ê' => 'e',
			'ə' => 'e',
			'è' => 'e',
			'ë' => 'e',
			'é' => 'e',
			'ф' => 'f',
			'ƒ' => 'f',
			'Ф' => 'f',
			'ġ' => 'g',
			'Ģ' => 'g',
			'Ġ' => 'g',
			'Ĝ' => 'g',
			'Г' => 'g',
			'г' => 'g',
			'ĝ' => 'g',
			'ğ' => 'g',
			'ג' => 'g',
			'Ґ' => 'g',
			'ґ' => 'g',
			'ģ' => 'g',
			'ח' => 'h',
			'ħ' => 'h',
			'Х' => 'h',
			'Ħ' => 'h',
			'Ĥ' => 'h',
			'ĥ' => 'h',
			'х' => 'h',
			'ה' => 'h',
			'î' => 'i',
			'ï' => 'i',
			'í' => 'i',
			'ì' => 'i',
			'į' => 'i',
			'ĭ' => 'i',
			'ı' => 'i',
			'Ĭ' => 'i',
			'И' => 'i',
			'ĩ' => 'i',
			'ǐ' => 'i',
			'Ĩ' => 'i',
			'Ǐ' => 'i',
			'и' => 'i',
			'Į' => 'i',
			'י' => 'i',
			'Ї' => 'i',
			'Ī' => 'i',
			'І' => 'i',
			'ї' => 'i',
			'і' => 'i',
			'ī' => 'i',
			'ĳ' => 'ij',
			'Ĳ' => 'ij',
			'й' => 'j',
			'Й' => 'j',
			'Ĵ' => 'j',
			'ĵ' => 'j',
			'я' => 'ja',
			'Я' => 'ja',
			'Э' => 'je',
			'э' => 'je',
			'ё' => 'jo',
			'Ё' => 'jo',
			'ю' => 'ju',
			'Ю' => 'ju',
			'ĸ' => 'k',
			'כ' => 'k',
			'Ķ' => 'k',
			'К' => 'k',
			'к' => 'k',
			'ķ' => 'k',
			'ך' => 'k',
			'Ŀ' => 'l',
			'ŀ' => 'l',
			'Л' => 'l',
			'ł' => 'l',
			'ļ' => 'l',
			'ĺ' => 'l',
			'Ĺ' => 'l',
			'Ļ' => 'l',
			'л' => 'l',
			'Ľ' => 'l',
			'ľ' => 'l',
			'ל' => 'l',
			'מ' => 'm',
			'М' => 'm',
			'ם' => 'm',
			'м' => 'm',
			'ñ' => 'n',
			'н' => 'n',
			'Ņ' => 'n',
			'ן' => 'n',
			'ŋ' => 'n',
			'נ' => 'n',
			'Н' => 'n',
			'ń' => 'n',
			'Ŋ' => 'n',
			'ņ' => 'n',
			'ŉ' => 'n',
			'Ň' => 'n',
			'ň' => 'n',
			'о' => 'o',
			'О' => 'o',
			'ő' => 'o',
			'õ' => 'o',
			'ô' => 'o',
			'Ő' => 'o',
			'ŏ' => 'o',
			'Ŏ' => 'o',
			'Ō' => 'o',
			'ō' => 'o',
			'ø' => 'o',
			'ǿ' => 'o',
			'ǒ' => 'o',
			'ò' => 'o',
			'Ǿ' => 'o',
			'Ǒ' => 'o',
			'ơ' => 'o',
			'ó' => 'o',
			'Ơ' => 'o',
			'œ' => 'oe',
			'Œ' => 'oe',
			'ö' => 'oe',
			'פ' => 'p',
			'ף' => 'p',
			'п' => 'p',
			'П' => 'p',
			'ק' => 'q',
			'ŕ' => 'r',
			'ř' => 'r',
			'Ř' => 'r',
			'ŗ' => 'r',
			'Ŗ' => 'r',
			'ר' => 'r',
			'Ŕ' => 'r',
			'Р' => 'r',
			'р' => 'r',
			'ș' => 's',
			'с' => 's',
			'Ŝ' => 's',
			'š' => 's',
			'ś' => 's',
			'ס' => 's',
			'ş' => 's',
			'С' => 's',
			'ŝ' => 's',
			'Щ' => 'sch',
			'щ' => 'sch',
			'ш' => 'sh',
			'Ш' => 'sh',
			'ß' => 'ss',
			'т' => 't',
			'ט' => 't',
			'ŧ' => 't',
			'ת' => 't',
			'ť' => 't',
			'ţ' => 't',
			'Ţ' => 't',
			'Т' => 't',
			'ț' => 't',
			'Ŧ' => 't',
			'Ť' => 't',
			'™' => 'tm',
			'ū' => 'u',
			'у' => 'u',
			'Ũ' => 'u',
			'ũ' => 'u',
			'Ư' => 'u',
			'ư' => 'u',
			'Ū' => 'u',
			'Ǔ' => 'u',
			'ų' => 'u',
			'Ų' => 'u',
			'ŭ' => 'u',
			'Ŭ' => 'u',
			'Ů' => 'u',
			'ů' => 'u',
			'ű' => 'u',
			'Ű' => 'u',
			'Ǖ' => 'u',
			'ǔ' => 'u',
			'Ǜ' => 'u',
			'ù' => 'u',
			'ú' => 'u',
			'û' => 'u',
			'У' => 'u',
			'ǚ' => 'u',
			'ǜ' => 'u',
			'Ǚ' => 'u',
			'Ǘ' => 'u',
			'ǖ' => 'u',
			'ǘ' => 'u',
			'ü' => 'ue',
			'в' => 'v',
			'ו' => 'v',
			'В' => 'v',
			'ש' => 'w',
			'ŵ' => 'w',
			'Ŵ' => 'w',
			'ы' => 'y',
			'ŷ' => 'y',
			'ý' => 'y',
			'ÿ' => 'y',
			'Ÿ' => 'y',
			'Ŷ' => 'y',
			'Ы' => 'y',
			'ž' => 'z',
			'З' => 'z',
			'з' => 'z',
			'ź' => 'z',
			'ז' => 'z',
			'ż' => 'z',
			'ſ' => 'z',
			'Ж' => 'zh',
			'ж' => 'zh'
		);

		return strtr( $s, $replace );
	}

	/**
	 * Support for i18n with wpml, polyglot or qtrans
	 *
	 * @param string $in
	 *
	 * @return string $in localized
	 */
	public static function i18n( $in ) {

		if ( function_exists( 'langswitch_filter_langs_with_message' ) ) {
			$in = langswitch_filter_langs_with_message( $in );
		}
		if ( function_exists( 'polyglot_filter' ) ) {
			$in = polyglot_filter( $in );
		}
		if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		}

		return apply_filters( 'localization', $in );
	}
	/**
	 * Sanitize the search
	 * @param string $string
	 *
	 * @return array|string|string[]|null
	 */
	public static function sanitizeSearch( $string ) {

		if( ! is_string( $string ) ) {
			return '';
		}

		$string = preg_replace("/[\,\!\@\#\$\^\&\*\~\;\<\>\`\'\"\[\]\{\}\(\)\=\%]/","",$string);
		$string = preg_replace("/\+/"," ",$string);

		return $string;
	}
}
