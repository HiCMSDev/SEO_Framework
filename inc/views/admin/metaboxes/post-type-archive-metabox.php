<?php
/**
 * @package The_SEO_Framework\Views\Admin\Metaboxes
 * @subpackage The_SEO_Framework\Admin\Settings
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

use The_SEO_Framework\Bridges\SeoSettings,
	The_SEO_Framework\Interpreters\HTML,
	The_SEO_Framework\Interpreters\Form,
	The_SEO_Framework\Interpreters\Settings_Input as Input;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and tsf()->_verify_include_secret( $_secret ) or die;

// Fetch the required instance within this file.
switch ( $this->get_view_instance( 'post_type_archive', $instance ) ) :
	case 'post_type_archive_main':
		$_settings_class = SeoSettings::class;
		$post_types      = $this->get_public_post_type_archives();

		$post_types_data = [];
		foreach ( $post_types as $post_type ) {
			$post_types_data[ $post_type ] = [
				'label' => $this->get_generated_post_type_archive_title( $post_type ),
				'url'   => $this->create_canonical_url( [ 'pta' => $post_type ] ),
			];
		}

		printf(
			'<span class=hidden id=tsf-post-type-archive-data %s></span>',
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- This escapes.
			HTML::make_data_attributes( [ 'postTypes' => $post_types_data ] )
		);

		?>
		<div id=tsf-post-type-archive-selector-wrap class="tsf-fields tsf-hide-if-no-js"></div>
		<?php
		foreach ( $post_types as $post_type ) {
			$_generator_args = [
				'id'       => '',
				'taxonomy' => '',
				'pta'      => $post_type,
			];

			// Create `[ 'doctitle' => [ 'pta', $post_type ] ];`
			$_option_map = array_fill_keys(
				[
					'doctitle',
					'title_no_blog_name',
					'description',
					'og_title',
					'og_description',
					'tw_title',
					'tw_description',
					'social_image_url',
					'social_image_id',
					'canonical',
					'noindex',
					'nofollow',
					'noarchive',
					'redirect',
				],
				[ 'pta', $post_type ]
			);
			// Create: `[ 'doctitle' => [ 'pta', $post_type, 'doctitle' ] ];`
			array_walk(
				$_option_map,
				static function( &$input_id, $key ) {
					$input_id = array_merge( $input_id, [ $key ] );
				}
			);

			$tabs = [
				'general'    => [
					'name'     => __( 'General', 'autodescription' ),
					'callback' => [ $_settings_class, '_post_type_archive_metabox_general_tab' ],
					'dashicon' => 'admin-generic',
					'args'     => compact( 'post_type', '_generator_args', '_option_map' ),
				],
				'social'     => [
					'name'     => __( 'Social', 'autodescription' ),
					'callback' => [ $_settings_class, '_post_type_archive_metabox_social_tab' ],
					'dashicon' => 'share',
					'args'     => compact( 'post_type', '_generator_args', '_option_map' ),
				],
				'visibility' => [
					'name'     => __( 'Visibility', 'autodescription' ),
					'callback' => [ $_settings_class, '_post_type_archive_metabox_visibility_tab' ],
					'dashicon' => 'visibility',
					'args'     => compact( 'post_type', '_generator_args', '_option_map' ),
				],
			];

			printf(
				'<div class=tsf-post-type-archive-wrap %s>',
				// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- This escapes.
				HTML::make_data_attributes( [ 'post_type' => $post_type ] )
			);
			?>
				<div class=tsf-post-type-archive-if-excluded style=display:none>
					<?php
					HTML::attention_description(
						__( "This post type is excluded, so these settings won't have any effect.", 'autodescription' )
					)
					?>
				</div>
				<div class=tsf-post-type-archive-if-not-excluded>
					<?php
					// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- it is.
					echo HTML::get_header_title(
						$this->convert_markdown(
							vsprintf(
								/* translators: 1 = Post Type Archive name, Markdown. 2 = Post Type code, also markdown! 3 = Post Type Archive link, also markdown. Preserve the Markdown as-is! */
								esc_html__( 'Archive of %1$s &ndash; `%2$s` ([View archive](%3$s))', 'autodescription' ),
								[
									$post_types_data[ $post_type ]['label'],
									$post_type,
									$post_types_data[ $post_type ]['url'],
								]
							),
							[ 'code', 'a' ],
							[ 'a_internal' => false ] // open in new window.
						)
					);
					SeoSettings::_nav_tab_wrapper(
						"post_type_archive_{$post_type}",
						/**
						 * @since 4.2.0
						 * @param array   $tabs      The default tabs.
						 * @param strring $post_type The post type archive's name.
						 */
						(array) apply_filters_ref_array(
							'the_seo_framework_post_type_archive_settings_tabs',
							[
								$tabs,
								$post_type,
							]
						)
					);
					?>
				</div>
			</div>

			<hr class=hide-if-tsf-js>
			<?php
		}
		break;

	case 'post_type_archive_general_tab':
		?>
		<p>
			<label for="<?php Input::field_id( $_option_map['doctitle'] ); ?>" class=tsf-toblock>
				<strong><?php esc_html_e( 'Meta Title', 'autodescription' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						__( 'The meta title can be used to determine the title used on search engine result pages.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/appearance/good-titles-snippets#page-titles'
					);
				?>
			</label>
		</p>
		<?php
		// Output these unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( $_option_map['doctitle'] ), (bool) $this->get_option( 'display_character_counter' ) );
		Form::output_pixel_counter_wrap( Input::get_field_id( $_option_map['doctitle'] ), 'title', (bool) $this->get_option( 'display_pixel_counter' ) );
		?>
		<p class=tsf-title-wrap>
			<input type="text" name="<?php Input::field_name( $_option_map['doctitle'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['doctitle'] ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $this->get_post_type_archive_meta_item( 'doctitle', $post_type ) ); ?>" autocomplete=off />
			<?php
			$this->output_js_title_data(
				Input::get_field_id( $_option_map['doctitle'] ),
				[
					'state' => [
						'defaultTitle'      => $this->get_filtered_raw_generated_title( $_generator_args ),
						'addAdditions'      => $this->use_title_branding( $_generator_args ),
						'useSocialTagline'  => $this->use_title_branding( $_generator_args, true ),
						'additionPlacement' => 'left' === $this->get_title_seplocation() ? 'before' : 'after',
					],
				]
			);
			?>
		</p>

		<div class=tsf-title-tagline-toggle>
		<?php
			$info = HTML::make_info(
				__( 'Use this when you want to rearrange the title parts manually.', 'autodescription' ),
				'',
				false
			);

			HTML::wrap_fields(
				Input::make_checkbox( [
					'id'     => $_option_map['title_no_blog_name'],
					'label'  => esc_html__( 'Remove the site title?', 'autodescription' ) . ' ' . $info,
					'escape' => false,
				] ),
				true
			);
		?>
		</div>

		<hr>

		<p>
			<label for="<?php Input::field_id( $_option_map['description'] ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Meta Description', 'autodescription' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/appearance/good-titles-snippets#meta-descriptions'
					);
				?>
			</label>
		</p>
		<?php
		// Output these unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( $_option_map['description'] ), (bool) $this->get_option( 'display_character_counter' ) );
		Form::output_pixel_counter_wrap( Input::get_field_id( $_option_map['description'] ), 'description', (bool) $this->get_option( 'display_pixel_counter' ) );
		?>
		<p>
			<textarea name="<?php Input::field_name( $_option_map['description'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['description'] ); ?>" rows="3" cols="70"><?php echo esc_attr( $this->get_option( $_option_map['description'] ) ); ?></textarea>
			<?php
			$this->output_js_description_elements(); // legacy
			$this->output_js_description_data(
				Input::get_field_id( $_option_map['description'] ),
				[
					'state' => [
						'defaultDescription' => $this->get_generated_description( $_generator_args ),
					],
				]
			);
			?>
		</p>
		<?php
		break;
	case 'post_type_archive_social_tab':
		$this->output_js_social_data(
			"pta_social_settings_{$post_type}",
			[
				'og' => [
					'state' => [
						'defaultTitle' => $this->s_title( $this->get_generated_open_graph_title( $_generator_args, false ) ),
						'addAdditions' => $this->use_title_branding( $_generator_args, 'og' ),
						'defaultDesc'  => $this->s_description( $this->get_generated_open_graph_description( $_generator_args, false ) ),
					],
				],
				'tw' => [
					'state' => [
						'defaultTitle' => $this->s_title( $this->get_generated_twitter_title( $_generator_args, false ) ),
						'addAdditions' => $this->use_title_branding( $_generator_args, 'twitter' ),
						'defaultDesc'  => $this->s_description( $this->get_generated_twitter_description( $_generator_args, false ) ),
					],
				],
			]
		);

		?>
		<p>
			<label for="<?php Input::field_id( $_option_map['og_title'] ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Open Graph Title', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( $_option_map['og_title'] ), (bool) $this->get_option( 'display_character_counter' ) );
		?>
		<p>
			<input type="text" name="<?php Input::field_name( $_option_map['og_title'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['og_title'] ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $this->get_option( $_option_map['og_title'] ) ); ?>" autocomplete=off data-tsf-social-group=<?php echo esc_attr( "pta_social_settings_{$post_type}" ); ?> data-tsf-social-type=ogTitle />
		</p>

		<p>
			<label for="<?php Input::field_id( $_option_map['og_description'] ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Open Graph Description', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( $_option_map['og_description'] ), (bool) $this->get_option( 'display_character_counter' ) );
		?>
		<p>
			<textarea name="<?php Input::field_name( $_option_map['og_description'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['og_description'] ); ?>" rows="3" cols="70" autocomplete=off data-tsf-social-group=<?php echo esc_attr( "pta_social_settings_{$post_type}" ); ?> data-tsf-social-type=ogDesc><?php echo esc_attr( $this->get_option( $_option_map['og_description'] ) ); ?></textarea>
		</p>

		<hr>

		<p>
			<label for="<?php Input::field_id( $_option_map['tw_title'] ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Twitter Title', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( $_option_map['tw_title'] ), (bool) $this->get_option( 'display_character_counter' ) );
		?>
		<p>
			<input type="text" name="<?php Input::field_name( $_option_map['tw_title'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['tw_title'] ); ?>" value="<?php echo $this->esc_attr_preserve_amp( $this->get_option( $_option_map['tw_title'] ) ); ?>" autocomplete=off data-tsf-social-group=<?php echo esc_attr( "pta_social_settings_{$post_type}" ); ?> data-tsf-social-type=twTitle />
		</p>

		<p>
			<label for="<?php Input::field_id( $_option_map['tw_description'] ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Twitter Description', 'autodescription' ); ?></strong>
			</label>
		</p>
		<?php
		// Output this unconditionally, with inline CSS attached to allow reacting on settings.
		Form::output_character_counter_wrap( Input::get_field_id( $_option_map['tw_description'] ), (bool) $this->get_option( 'display_character_counter' ) );
		?>
		<p>
			<textarea name="<?php Input::field_name( $_option_map['tw_description'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['tw_description'] ); ?>" rows="3" cols="70" autocomplete=off data-tsf-social-group=<?php echo esc_attr( "pta_social_settings_{$post_type}" ); ?> data-tsf-social-type=twDesc><?php echo esc_attr( $this->get_option( $_option_map['tw_description'] ) ); ?></textarea>
		</p>
		<hr>
		<?php
		HTML::header_title( __( 'Social Image Settings', 'autodescription' ) );
		?>
		<p>
			<label for="<?php echo esc_attr( "tsf_pta_socialimage_{$post_type}" ); ?>-url">
				<strong><?php esc_html_e( 'Social Image URL', 'autodescription' ); ?></strong>
				<?php
				HTML::make_info(
					__( "The social image URL can be used by search engines and social networks alike. It's best to use an image with a 1.91:1 aspect ratio that is at least 1200px wide for universal support.", 'autodescription' ),
					'https://developers.facebook.com/docs/sharing/best-practices#images'
				);
				?>
			</label>
		</p>
		<p>
			<input class="large-text" type="url" name="<?php Input::field_name( $_option_map['social_image_url'] ); ?>" id="<?php echo esc_attr( "tsf_pta_socialimage_{$post_type}" ); ?>-url" placeholder="<?php echo esc_url( current( $this->get_generated_image_details( $_generator_args, true, 'social', true ) )['url'] ?? '' ); ?>" value="<?php echo esc_url( $this->get_option( $_option_map['social_image_url'] ) ); ?>" />
			<input type="hidden" name="<?php Input::field_name( $_option_map['social_image_id'] ); ?>" id="<?php echo esc_attr( "tsf_pta_socialimage_{$post_type}" ); ?>-id" value="<?php echo absint( $this->get_option( $_option_map['social_image_id'] ) ); ?>" disabled class="tsf-enable-media-if-js" />
		</p>
		<p class="hide-if-no-tsf-js">
			<?php
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped.
			echo Form::get_image_uploader_form( [ 'id' => "tsf_pta_socialimage_{$post_type}" ] );
			?>
		</p>
		<?php
		break;
	case 'post_type_archive_visibility_tab':
		?>
		<p>
			<label for="<?php Input::field_id( $_option_map['canonical'] ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( 'Canonical URL', 'autodescription' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						__( 'This urges search engines to go to the outputted URL.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/crawling/consolidate-duplicate-urls'
					);
				?>
			</label>
		</p>
		<p>
			<input type="url" name="<?php Input::field_name( $_option_map['canonical'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['canonical'] ); ?>" placeholder="<?php echo esc_url( $this->create_canonical_url( $_generator_args ) ); ?>" value="<?php echo esc_url( $this->get_option( $_option_map['canonical'] ) ); ?>" autocomplete=off />
		</p>

		<hr>
		<?php
		$robots_settings = [
			'noindex'   => [
				'id'        => Input::get_field_name( $_option_map['noindex'] ),
				'name'      => Input::get_field_id( $_option_map['noindex'] ),
				'force_on'  => 'index',
				'force_off' => 'noindex',
				'label'     => __( 'Indexing', 'autodescription' ),
				'_default'  => empty( $robots_defaults['noindex'] ) ? 'index' : 'noindex',
				'_value'    => $this->get_option( $_option_map['noindex'] ),
				'_info'     => [
					__( 'This tells search engines not to show this term in their search results.', 'autodescription' ),
					'https://developers.google.com/search/docs/advanced/crawling/block-indexing',
				],
			],
			'nofollow'  => [
				'id'        => Input::get_field_name( $_option_map['nofollow'] ),
				'name'      => Input::get_field_id( $_option_map['nofollow'] ),
				'force_on'  => 'follow',
				'force_off' => 'nofollow',
				'label'     => __( 'Link following', 'autodescription' ),
				'_default'  => empty( $robots_defaults['nofollow'] ) ? 'follow' : 'nofollow',
				'_value'    => $this->get_option( $_option_map['nofollow'] ),
				'_info'     => [
					__( 'This tells search engines not to follow links on this term.', 'autodescription' ),
					'https://developers.google.com/search/docs/advanced/guidelines/qualify-outbound-links',
				],
			],
			'noarchive' => [
				'id'        => Input::get_field_name( $_option_map['noarchive'] ),
				'name'      => Input::get_field_id( $_option_map['noarchive'] ),
				'force_on'  => 'archive',
				'force_off' => 'noarchive',
				'label'     => __( 'Archiving', 'autodescription' ),
				'_default'  => empty( $robots_defaults['noarchive'] ) ? 'archive' : 'noarchive',
				'_value'    => $this->get_option( $_option_map['noarchive'] ),
				'_info'     => [
					__( 'This tells search engines not to save a cached copy of this term.', 'autodescription' ),
					'https://developers.google.com/search/docs/advanced/robots/robots_meta_tag#directives',
				],
			],
		];

		HTML::header_title( __( 'Robots Meta Settings', 'autodescription' ) );
		foreach ( $robots_settings as $_s ) :
			// phpcs:disable, WordPress.Security.EscapeOutput -- make_single_select_form() escapes.
			echo Form::make_single_select_form( [
				'id'      => $_s['id'],
				'class'   => 'tsf-toblock tsf-pta-robots-meta',
				'name'    => $_s['name'],
				'label'   => $_s['label'],
				'options' => [
					0  => __( 'Default (unknown)', 'autodescription' ),
					-1 => $_s['force_on'],
					1  => $_s['force_off'],
				],
				'default' => $_s['_value'],
				'info'    => $_s['_info'],
				'data'    => [
					'defaultUnprotected' => $_s['_default'],
					/* translators: %s = default option value */
					'defaultI18n'        => __( 'Default (%s)', 'autodescription' ),
				],
			] );
			// phpcs:enable, WordPress.Security.EscapeOutput
		endforeach;
		?>
		<p>
			<label for="<?php Input::field_id( $_option_map['redirect'] ); ?>" class="tsf-toblock">
				<strong><?php esc_html_e( '301 Redirect URL', 'autodescription' ); ?></strong>
				<?php
					echo ' ';
					HTML::make_info(
						__( 'This will force visitors to go to another URL.', 'autodescription' ),
						'https://developers.google.com/search/docs/advanced/crawling/301-redirects'
					);
				?>
			</label>
		</p>
		<p>
			<input type="url" name="<?php Input::field_name( $_option_map['redirect'] ); ?>" class="large-text" id="<?php Input::field_id( $_option_map['redirect'] ); ?>" value="<?php echo esc_url( $this->get_option( $_option_map['redirect'] ) ); ?>" autocomplete=off />
		</p>
		<?php
		break;
endswitch;
