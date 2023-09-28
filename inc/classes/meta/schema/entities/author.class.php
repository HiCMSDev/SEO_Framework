<?php
/**
 * @package The_SEO_Framework\Classes\Meta\Schema\Entities\Webpage
 * @subpackage The_SEO_Framework\Meta\Schema
 */

namespace The_SEO_Framework\Meta\Schema\Entities;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\Utils\{
	normalize_generation_args,
	clamp_sentence,
};

use \The_SEO_Framework\Data,
	\The_SEO_Framework\Meta,
	\The_SEO_Framework\Helper\Query;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Holds Author generator for Schema.org structured data.
 * Not to be confused with "Person". This one represents a post.
 *
 * @since 4.3.0
 * @access protected
 */
final class Author extends Reference {

	/**
	 * @since 4.3.0
	 * @var string|string[] $type The Schema @type.
	 */
	public static $type = 'Person';

	/**
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 *                         Also accepts 'uid', which is now.
	 * @return int The author ID. 0 on failure.
	 */
	private static function get_author_id_from_args( $args ) {

		if ( null === $args ) {
			$author_id = Query::get_post_author_id();
		} else {
			if ( isset( $args['uid'] ) ) {
				$author_id = $args['uid'];
			} else {
				normalize_generation_args( $args );

				if ( empty( $args['tax'] ) && empty( $args['pta'] ) ) {
					$author_id = Query::get_post_author_id( $args['id'] );
				}
			}
		}

		return $author_id ?? 0;
	}

	/**
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 *                         Also accepts 'uid';
	 * @return string The entity ID for $args.
	 */
	public static function get_id( $args = null ) {

		$author_id = static::get_author_id_from_args( $args );

		if ( empty( $author_id ) )
			return '';

		return Meta\URI::get_bare_front_page_canonical_url()
			. '#/schema/' . current( (array) static::$type ) . '/' . \wp_hash( "tsf+$author_id" );
	}

	/**
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 *                         Also accepts 'uid';
	 * @return ?array $entity The Schema.org graph entity.
	 */
	public static function build( $args = null ) {

		$author_id = static::get_author_id_from_args( $args );

		if ( empty( $author_id ) ) return null;

		$user_data = \get_userdata( $author_id );
		$user_meta = Data\Plugin\User::get_user_meta( $author_id );

		$entity = [
			'@type' => static::$type,
			'@id'   => static::get_id( [ 'uid' => $author_id ] ),
			'name'  => $user_data->display_name ?? '',
			// Let's not; may invoke bad bots. Let's do this via sameas.
			// 'url'   => Meta\URI::get_bare_author_canonical_url( $author_id ),
		];

		if ( $user_meta['facebook_page'] )
			$entity['sameAs'][] = \sanitize_url( $user_meta['facebook_page'], [ 'https', 'http' ] );
		if ( $user_meta['twitter_page'] )
			$entity['sameAs'][] = \sanitize_url( 'https://twitter.com/' . ltrim( $user_meta['twitter_page'], '@' ) );

		if ( ! empty( $user_data->description ) )
			$entity['description'] = clamp_sentence( \wp_strip_all_tags( $user_data->description ), 1, 250 );

		return $entity;
	}
}
