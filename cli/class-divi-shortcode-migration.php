<?php
/**
 * To migrates the Divi shortcodes to gutenberg blocks.
 *
 * @since             1.0.0
 * @package           divi-migration-tools
 */

WP_CLI::add_command( 'divi-cli', 'Divi_Shortcode_Migration' );

class Divi_Shortcode_Migration extends WP_CLI_Command {

	private $dry_run   = true;
	private $post_type = 'post';

	private $migratable_shortcodes = array( 'et_pb_video', 'et_pb_button', 'et_pb_image', 'et_pb_fullwidth_image', 'et_pb_post_title', 'et_pb_divider', 'et_pb_blurb' );
	private $clearable_shortcodes  = array( 'et_pb_section', 'et_pb_row', 'et_pb_column', 'et_pb_text', 'et_pb_fullwidth_header', 'et_pb_code', 'et_pb_cta', 'et_pb_row_inner', 'et_pb_column_inner', 'et_pb_sidebar', 'et_pb_slider', 'et_pb_slide', 'et_pb_line_break_holder', 'et_pb_toggle', 'et_pb_fullwidth_code' );
	private $skippable_shortcodes  = array( 'et_social_follow', 'embed', 'caption', 'toc', 'Sarcastic', 'gallery', 'Tweet', 'Proof', 'et_pb_social_media_follow', 'et_pb_social_media_follow_network', 'et_pb_testimonial', 'et_pb_contact_form', 'et_pb_contact_field', 'et_pb_blog', 'et_pb_pricing_tables', 'et_pb_video_slider', 'et_pb_video_slider_item', 'et_pb_team_member', 'et_pb_tabs', 'et_pb_tab' );

	/**
	 * To reset Divi post Content.
	 *
	 * ## EXAMPLES
	 *
	 *   wp divi-cli reset-divi-content
	 *
	 * @subcommand reset-divi-content
	 *
	 * @param array $args Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 *
	 * @throws \Exception If any errors.
	 */
	public function reset_divi_post_content( $args, $assoc_args ) {

		if ( ! empty( $assoc_args['post-type'] ) && ! in_array( $assoc_args['post-type'], get_post_types( array( 'public' => true ) ), true ) ) {
			$this->error( 'You have called the command divi-cli:migrate-shortcodes with wrong/unsupported post-type.' . "\n" );
		}

		// Starting time of the script.
		$start_time = time();

		// To enable WP_IMPORTING.
		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		if ( ! empty( $assoc_args['dry-run'] ) && 'false' === $assoc_args['dry-run'] ) {

			$this->dry_run = false;
		}

		if ( ! empty( $assoc_args['post-type'] ) ) {

			$this->post_type = $assoc_args['post-type'];
		}

		if ( $this->dry_run ) {

			$this->write_log( '' );
			$this->warning( 'You have called the command divi-cli:reset-post-content in dry run mode.' . "\n" );
		}

		$limit = -1;

		if ( ! empty( $assoc_args['limit'] ) ) {
			$limit = intval( $assoc_args['limit'] );
		}

		$post_status = 'publish';

		if ( ! empty( $assoc_args['status'] ) && 'draft' === $assoc_args['status'] ) {
			$post_status = 'draft';
		}

		$this->write_log( '' );
		$this->write_log( sprintf( 'Migrating the shortcodes from %s post type.', $this->post_type ) . "\n" );

		$args = array(
			'numberposts' => $limit,
			'orderby'     => 'ID',
			'order'       => 'ASC',
			'post_type'   => $this->post_type,
			'post_status' => $post_status,
		);

		$posts = get_posts( $args ); // @codingStandardsIgnoreLine: No need to maintain the caching here, so get_posts is okay to use.

		$total_found   = count( $posts );
		$success_count = 0;
		$fail_count    = 0;

		$this->write_log( sprintf( 'Found %d posts to be pass through migration', $total_found ) . "\n" );

		foreach ( $posts as $post ) {
			$post = (array) $post;

			if ( ! $this->dry_run ) {

				$new_post = array(
					'ID'                => $post['ID'],
					'post_content'      => get_post_meta( $post['ID'], '_divi_post_content', true ),
					'post_modified'     => $post['post_modified'],
					'post_modified_gmt' => $post['post_modified_gmt'],
				);

				$result = wp_update_post( $new_post, true );
				if ( is_wp_error( $result ) ) {
					$fail_count++;
				} else {
					$success_count++;
				}
			}
		}

		if ( $this->dry_run ) {

			$this->success( sprintf( 'Total %d posts will be processed.', $total_found ) );
			$this->warning( sprintf( 'Total %d posts will be failed to process.', $fail_count ) );

		} else {

			$this->success( sprintf( 'Total %d posts have been processed.', $success_count ) );
			$this->warning( sprintf( 'Total %d posts have been failed to process.', $fail_count ) );
		}

		WP_CLI::line( '' );
		WP_CLI::success( sprintf( 'Total time taken by this script: %s', human_time_diff( $start_time, time() ) ) . PHP_EOL . PHP_EOL );
	}

	/**
	 * To convert the Divi shortcodes in post content.
	 *
	 * ## EXAMPLES
	 *
	 *   wp divi-cli migrate-shortcodes
	 *
	 * @subcommand migrate-shortcodes
	 *
	 * @param array $args Store all the positional arguments.
	 * @param array $assoc_args Store all the associative arguments.
	 *
	 * @throws \Exception If any errors.
	 */
	public function divi_migrate_shortcodes( $args, $assoc_args ) {

		if ( ! empty( $assoc_args['post-type'] ) && ! in_array( $assoc_args['post-type'], get_post_types( array( 'public' => true ) ), true ) ) {
			$this->error( 'You have called the command divi-cli:migrate-shortcodes with wrong/unsupported post-type.' . "\n" );
		}

		// Starting time of the script.
		$start_time = time();

		// To enable WP_IMPORTING.
		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		if ( ! empty( $assoc_args['dry-run'] ) && 'false' === $assoc_args['dry-run'] ) {

			$this->dry_run = false;
		}

		if ( ! empty( $assoc_args['post-type'] ) ) {

			$this->post_type = $assoc_args['post-type'];
		}

		if ( $this->dry_run ) {

			$this->write_log( '' );
			$this->warning( 'You have called the command divi-cli:migrate-shortcodes in dry run mode.' . "\n" );
		}

		$limit = -1;

		if ( ! empty( $assoc_args['limit'] ) ) {
			$limit = intval( $assoc_args['limit'] );
		}

		$post_status = 'publish';

		if ( ! empty( $assoc_args['status'] ) && 'draft' === $assoc_args['status'] ) {
			$post_status = 'draft';
		}

		$this->write_log( '' );
		$this->write_log( sprintf( 'Migrating the shortcodes from %s post type.', $this->post_type ) . "\n" );

		$args = array(
			'numberposts' => $limit,
			'orderby'     => 'ID',
			'order'       => 'ASC',
			'post_type'   => $this->post_type,
			'post_status' => $post_status,
		);

		$posts = get_posts( $args ); // @codingStandardsIgnoreLine: No need to maintain the caching here, so get_posts is okay to use.

		$total_found   = count( $posts );
		$success_count = 0;
		$fail_count    = 0;
		$detail_log    = array(
			array( 'post_id', 'shortcode_name', 'full_shortcode', 'status' ),
		);

		$this->write_log( sprintf( 'Found %d posts to be pass through migration', $total_found ) . "\n" );

		foreach ( $posts as $post ) {
			$post           = (array) $post;
			$migrate_status = $this->divi_migrate_single_post_shortcode( $post, $detail_log );

			if ( $migrate_status ) {
				$success_count++;
			} else {
				$fail_count++;
			}
		}

		WP_CLI::line( '' );
		$this->create_log_file( sprintf( 'divi-shortcode-logs-%s-%s.csv', $post_status, $this->post_type ), $detail_log );
		WP_CLI::line( '' );

		if ( $this->dry_run ) {

			$this->success( sprintf( 'Total %d posts will be processed.', $total_found ) );
			$this->warning( sprintf( 'Total %d posts will be failed to process.', $fail_count ) );

		} else {

			$this->success( sprintf( 'Total %d posts have been processed.', $success_count ) );
			$this->warning( sprintf( 'Total %d posts have been failed to process.', $fail_count ) );
		}

		WP_CLI::line( '' );
		WP_CLI::success( sprintf( 'Total time taken by this script: %s', human_time_diff( $start_time, time() ) ) . PHP_EOL . PHP_EOL );
	}

	/**
	 * To migrate the divi shortcodes for single post.
	 *
	 * @param array $post Post object.
	 * @param array $logs Detailed logs.
	 *
	 * @return bool
	 */
	private function divi_migrate_single_post_shortcode( $post, &$logs ) {
		$post_content = $post['post_content'];

		$old_content = get_post_meta( $post['ID'], '_divi_post_content', true );
		if ( ! empty( $old_content ) ) {
			$post_content = $old_content;
		} else {
			update_post_meta( $post['ID'], '_divi_post_content', $post_content );
		}

		$regex = '/\[([a-zA-Z0-9_-]+) ?([^\]]+)?/';

		preg_match_all( $regex, $post_content, $matches, PREG_SET_ORDER );

		$matches = array_values( array_filter( $matches ) );

		foreach ( $matches as $match ) {
			$shortcode_name = $match[1];
			$shortcode      = $match[0] . '][/' . $shortcode_name . ']';
			$status         = 'failed';
			$result         = false;

			if ( in_array( $shortcode_name, $this->skippable_shortcodes, true ) ) {
				$status = 'skipped';
			} elseif ( in_array( $shortcode_name, $this->clearable_shortcodes, true ) ) {
				// Clear the shortcode.
				$status       = 'cleared';
				$post_content = str_replace( $match[0] . ']', '', $post_content );
				$post_content = str_replace( '[/' . $shortcode_name . ']', '', $post_content );
			} elseif ( in_array( $shortcode_name, $this->migratable_shortcodes, true ) ) {

				// Migrate the shortcodes.
				$attributes = shortcode_parse_atts( $match[0] );

				if ( 'et_pb_blurb' === $shortcode_name ) {

					$gb_img_block = '';
					if ( ! empty( $attributes['image'] ) ) {
						$att_id = attachment_url_to_postid( $attributes['image'] );

						if ( $att_id ) {
							$gb_attr   = sprintf( '"id":%s,"sizeSlug":"medium",', $att_id );
							$style_str = '';

							if ( empty( $attributes['icon_alignment'] ) ) {
								$attributes['align'] = 'left';
							}
							$gb_attr .= sprintf( '"align":"%s",', $attributes['icon_alignment'] );

							$gb_img_block  = sprintf( '<!-- wp:image {%s} -->', trim( $gb_attr, ',' ) );
							$gb_img_block .= PHP_EOL;
							$gb_img_block .= sprintf( '<figure class="wp-block-image align%s size-medium"><img src="%s" class="wp-image-%s"/></figure>', $attributes['icon_alignment'], $attributes['image'], $att_id );
							$gb_img_block .= PHP_EOL;
							$gb_img_block .= '<!-- /wp:image -->';

						} else {
							$gb_attr = sprintf( 'sizeSlug":"medium",' );

							if ( empty( $attributes['align'] ) ) {
								$attributes['align'] = 'left';
							}
							$gb_attr .= sprintf( '"align":"%s",', $attributes['align'] );

							$gb_img_block  = sprintf( '<!-- wp:image {%s} -->', trim( $gb_attr, ',' ) );
							$gb_img_block .= PHP_EOL;
							$gb_img_block .= sprintf( '<figure class="wp-block-image align%s size-medium"><img src="%s"/></figure>', $attributes['icon_alignment'], $attributes['image'] );
							$gb_img_block .= PHP_EOL;
							$gb_img_block .= '<!-- /wp:image -->';
						}
					}

					$gb_heading_block = '';
					if ( ! empty( $attributes['title'] ) ) {
						// Title block
						$gb_attr   = '';
						$style_str = '';
						$h_classes = '';
						$h_level   = 'h4';
						$h_text    = $attributes['title'];

						if ( ! empty( $attributes['header_level'] ) && in_array( $attributes['header_level'], array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ) {
							$h_level = $attributes['header_level'];

							if ( 'h2' !== $attributes['header_level'] ) {
								$gb_attr = sprintf( '"level":%s,', str_replace( 'h', '', $attributes['header_level'] ) );
							}
						}

						if ( ! empty( $attributes['url'] ) ) {
							$target_url = '';
							if ( ! empty( $attributes['url_new_window'] ) && 'on' === $attributes['url_new_window'] ) {
								$target_url = 'target="_blank"';
							}
							$h_text = sprintf( '<a href="%s" %s>%s</a>', $attributes['url'], $target_url, $h_text );
						}

						$gb_heading_block  = sprintf( '<!-- wp:heading %s -->', $gb_attr );
						$gb_heading_block .= PHP_EOL;
						$gb_heading_block .= sprintf( '<%s>', $h_level, $h_classes, $style_str );
						$gb_heading_block .= PHP_EOL;
						$gb_heading_block .= $h_text;
						$gb_heading_block .= PHP_EOL;
						$gb_heading_block .= sprintf( '</%s>', $h_level );
						$gb_heading_block .= PHP_EOL;
						$gb_heading_block .= '<!-- /wp:heading -->';
					}

					// Bind blurb block
					$gb_blurb_block = '';
					if ( ! empty( $gb_img_block ) ) {
						$gb_blurb_block = $gb_img_block;
					}

					if ( ! empty( $gb_heading_block ) ) {

						if ( ! empty( $gb_blurb_block ) ) {
							$gb_blurb_block .= PHP_EOL;
						}

						$gb_blurb_block .= $gb_heading_block;
						$gb_blurb_block .= PHP_EOL;
					}

					if ( ! empty( $gb_blurb_block ) ) {

						if ( false === strpos( $post_content, $shortcode ) ) {
							$shortcode = $match[0] . ']';
						}

						$post_content = str_replace( $shortcode, $gb_blurb_block, $post_content );
						$post_content = str_replace( '[/et_pb_blurb]', '', $post_content );

						$status = 'migrated';
					}
				} elseif ( 'et_pb_divider' === $shortcode_name ) {
					$class_str = 'wp-block-separator is-style-wide';
					$gb_attr   = '{"className":"is-style-wide"}';
					$hr_style  = '';

					if ( ! empty( $attributes['color'] ) ) {
						$class_str .= ' has-text-color has-background';
						$hr_style   = sprintf( 'style="background-color:%s;color:%s"', $attributes['color'], $attributes['color'] );
						$gb_attr    = sprintf( '{"customColor":"%s","className":"is-style-wide"}', $attributes['color'] );
					}

					$gb_divider_block  = sprintf( '<!-- wp:separator %s -->', $gb_attr );
					$gb_divider_block .= PHP_EOL;
					$gb_divider_block .= sprintf( '<hr class="%s" %s/>', $class_str, $hr_style );
					$gb_divider_block .= PHP_EOL;
					$gb_divider_block .= '<!-- /wp:separator -->';

					if ( false === strpos( $post_content, $shortcode ) ) {
						$shortcode = $match[0] . ']';
					}
					$post_content = str_replace( $shortcode, $gb_divider_block, $post_content );

					$status = 'migrated';

				} elseif ( 'et_pb_post_title' === $shortcode_name ) {

					$gb_title_block = '';
					if ( 'off' !== $attributes['title'] ) {
						// Title block
						$gb_attr   = '';
						$style_str = '';
						$h_classes = '';
						$h_level   = 'h2';
						$h_text    = $post['post_title'];

						if ( ! empty( $attributes['title_level'] ) && in_array( $attributes['title_level'], array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ) {
							$h_level = $attributes['title_level'];

							if ( 'h2' !== $attributes['title_level'] ) {
								$gb_attr = sprintf( '"level":%s,', str_replace( 'h', '', $attributes['title_level'] ) );
							}
						}

						if ( ! empty( $attributes['title_font'] ) ) {
							$font_split = explode( '|', $attributes['title_font'] );

							$attributes['title_font'] = $font_split[0];

							$gb_attr   .= sprintf( '"fontFamily":"%s",', trim( $attributes['title_font'], '|' ) );
							$h_classes .= 'has-custom-font ';
							$style_str .= sprintf( 'font-family:%s;', trim( $attributes['title_font'], '|' ) );
						}
						if ( ! empty( $attributes['title_text_color'] ) ) {
							$gb_attr   .= sprintf( '"customTextColor":"%s",', $attributes['title_text_color'] );
							$h_classes .= 'has-text-color ';
							$style_str .= sprintf( 'color:%s;', $attributes['title_text_color'] );
						}
						if ( ! empty( $attributes['title_font_size'] ) ) {
							$gb_attr   .= sprintf( '"customFontSize":%s,', str_replace( 'px', '', $attributes['title_font_size'] ) );
							$style_str .= sprintf( 'font-size:%s;', $attributes['title_font_size'] );
						}
						if ( ! empty( $attributes['title_line_height'] ) ) {
							$gb_attr   .= sprintf( '"lineHeight":%s,', str_replace( 'em', '', $attributes['title_line_height'] ) );
							$h_classes .= 'has-custom-lineheight ';
							$style_str .= sprintf( 'line-height:%s;', $attributes['title_line_height'] );
						}

						if ( ! empty( $gb_attr ) ) {
							$gb_attr .= '"className":"' . trim( $h_classes ) . '",';
							$gb_attr  = '{' . trim( $gb_attr, ',' ) . '}';
						}

						if ( ! empty( $h_classes ) ) {
							$h_classes = 'class="' . trim( $h_classes ) . '"';
						}

						if ( ! empty( $style_str ) ) {
							$style_str = 'style="' . trim( $style_str, ';' ) . '"';
						}

						if ( ! empty( $attributes['link_option_url'] ) ) {
							$target_url = '';
							if ( ! empty( $attributes['link_option_url_new_window'] ) && 'on' === $attributes['link_option_url_new_window'] ) {
								$target_url = 'target="_blank"';
							}
							$h_text = sprintf( '<a href="%s" %s>%s</a>', $attributes['link_option_url'], $target_url, $h_text );
						}

						$gb_title_block  = sprintf( '<!-- wp:heading %s -->', $gb_attr );
						$gb_title_block .= PHP_EOL;
						$gb_title_block .= sprintf( '<%s %s %s>', $h_level, $h_classes, $style_str );
						$gb_title_block .= PHP_EOL;
						$gb_title_block .= $h_text;
						$gb_title_block .= PHP_EOL;
						$gb_title_block .= sprintf( '</%s>', $h_level );
						$gb_title_block .= PHP_EOL;
						$gb_title_block .= '<!-- /wp:heading -->';
					}

					$gb_author_line_block = '';
					if ( 'off' !== $attributes['meta'] ) {
						// Aithor by line: paragraph block

						$author_line = '';
						if ( 'off' !== $attributes['author'] ) {
							$author      = get_user_by( 'id', $post['post_author'] );
							$author_line = sprintf( 'by <a href="%s">%s</a>', get_author_posts_url( $post['post_author'] ), $author->display_name );
						}
						if ( 'off' !== $attributes['date'] ) {
							if ( ! empty( $author_line ) ) {
								$author_line .= ' | ';
							}
							$author_line .= date( 'M j, Y', strtotime( $post['post_date'] ) );
						}
						if ( 'off' !== $attributes['categories'] ) {
							if ( ! empty( $author_line ) ) {
								$author_line .= ' | ';
							}

							$terms_string = '';

							$terms = get_the_terms( $post['ID'], 'category' );
							if ( ! empty( $terms ) ) {
								foreach ( $terms as $term ) {
									$terms_string .= sprintf( '<a href="%s">%s</a>, ', get_term_link( $term->term_id ), $term->name );
								}
							}

							$author_line .= $terms_string;
						}
						if ( 'off' !== $attributes['comments'] ) {
							if ( ! empty( $author_line ) ) {
								$author_line .= ' | ';
							}
							$comment_status = '';
							$comment_count  = get_comment_count( $post['ID'] );

							if ( $comment_count['approved'] > 0 ) {
								$comment_status = $comment_count['approved'] . ' Comments';
							}

							$author_line .= $comment_status;
						}

						if ( ! empty( $author_line ) ) {
							$gb_author_line_block  = sprintf( '<!-- wp:paragraph -->' );
							$gb_author_line_block .= PHP_EOL;
							$gb_author_line_block .= '<p>' . $author_line . '</p>';
							$gb_author_line_block .= PHP_EOL;
							$gb_author_line_block .= '<!-- /wp:paragraph -->';
						}
					}

					$gb_img_block = '';
					if ( 'off' !== $attributes['featured_image'] ) {
						// Featured image block
						$att_id = get_post_meta( $post['ID'], '_thumbnail_id', true );

						$gb_attr   = sprintf( '"id":%s,"sizeSlug":"medium",', $att_id );
						$style_str = '';

						$gb_img_block  = sprintf( '<!-- wp:image {%s} -->', trim( $gb_attr, ',' ) );
						$gb_img_block .= PHP_EOL;
						$gb_img_block .= sprintf( '<figure class="wp-block-image align-left size-medium"><img src="%s" alt="" class="wp-image-%s"/></figure>', wp_get_attachment_url( $att_id ), $att_id );
						$gb_img_block .= PHP_EOL;
						$gb_img_block .= '<!-- /wp:image -->';
					}

					// Bind group block
					$gb_attr   = '';
					$style_str = '';
					$div_class = 'class="wp-block-group"';

					if ( ! empty( $attributes['background_color'] ) ) {
						$gb_attr   .= sprintf( '{"customBackgroundColor":"%s"} ', $attributes['background_color'] );
						$style_str .= sprintf( 'style="background-color:%s;"', $attributes['background_color'] );
						$div_class  = 'class="wp-block-group has-background"';
					}

					$gb_group_block  = sprintf( '<!-- wp:group %s-->', $gb_attr );
					$gb_group_block .= PHP_EOL;
					$gb_group_block .= sprintf( '<div %s %s><div class="wp-block-group__inner-container">', $div_class, $style_str );
					$gb_group_block .= PHP_EOL;

					if ( ! empty( $gb_img_block ) && 'above' === $attributes['featured_placement'] ) {
						$gb_group_block .= $gb_img_block;
						$gb_group_block .= PHP_EOL;
					}

					if ( ! empty( $gb_title_block ) ) {
						$gb_group_block .= $gb_title_block;
						$gb_group_block .= PHP_EOL;
					}

					if ( ! empty( $gb_author_line_block ) ) {
						$gb_group_block .= $gb_author_line_block;
						$gb_group_block .= PHP_EOL;
					}

					if ( ! empty( $gb_img_block ) && 'above' !== $attributes['featured_placement'] ) {
						$gb_group_block .= $gb_img_block;
						$gb_group_block .= PHP_EOL;
					}

					$gb_group_block .= '</div></div>';
					$gb_group_block .= PHP_EOL;
					$gb_group_block .= '<!-- /wp:group -->';

					if ( false === strpos( $post_content, $shortcode ) ) {
						$shortcode = $match[0] . ']';
					}
					$post_content = str_replace( $shortcode, $gb_group_block, $post_content );

					$status = 'migrated';

				} elseif ( 'et_pb_video' === $shortcode_name ) {
					// Youtube embeds.
					$src = $attributes['src'];
					if ( ! empty( $src ) && false !== strpos( $src, 'youtube.com' ) ) {
						$gb_youtube_block  = sprintf( '<!-- wp:core-embed/youtube {"url":"%s","type":"video","providerNameSlug":"youtube","className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->', $src );
						$gb_youtube_block .= PHP_EOL;
						$gb_youtube_block .= '<figure class="wp-block-embed-youtube wp-block-embed is-type-video is-provider-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">';
						$gb_youtube_block .= PHP_EOL;
						$gb_youtube_block .= $src;
						$gb_youtube_block .= PHP_EOL;
						$gb_youtube_block .= '</div></figure>';
						$gb_youtube_block .= PHP_EOL;
						$gb_youtube_block .= '<!-- /wp:core-embed/youtube -->';

						if ( false === strpos( $post_content, $shortcode ) ) {
							$shortcode = $match[0] . ']';
						}
						$post_content = str_replace( $shortcode, $gb_youtube_block, $post_content );

						$status = 'migrated';
					} elseif ( empty( $src ) && ! empty( $attributes['src_webm'] ) ) {
						$src   = $attributes['src_webm'];
						$thumb = ( empty( $attributes['image_src'] ) ) ? '' : 'poster="' . $attributes['image_src'] . '"';

						$att_id = attachment_url_to_postid( $src );

						if ( ! empty( $att_id ) ) {
							$gb_video_block = sprintf( '<!-- wp:video {"id":%s} -->', $att_id );
						} else {
							$gb_video_block = '<!-- wp:video -->';
						}

						$gb_video_block .= PHP_EOL;
						$gb_video_block .= '<figure class="wp-block-video">';
						$gb_video_block .= PHP_EOL;
						$gb_video_block .= sprintf( '<video controls src="%s" %s></video>', $src, $thumb );
						$gb_video_block .= PHP_EOL;
						$gb_video_block .= '</figure>';
						$gb_video_block .= PHP_EOL;
						$gb_video_block .= '<!-- /wp:video -->';

						if ( false === strpos( $post_content, $shortcode ) ) {
							$shortcode = $match[0] . ']';
						}
						$post_content = str_replace( $shortcode, $gb_video_block, $post_content );

						$status = 'migrated';
					}
				} elseif ( 'et_pb_button' === $shortcode_name ) {

					$target_url = '';
					if ( ! empty( $attributes['url_new_window'] ) && 'on' === $attributes['url_new_window'] ) {
						$target_url = 'target="_blank"';
					}

					/**
					 * @todo: @devik check if we can implement disable based on view point. i.e. for mobile,tablet,desktop
					 * Divi example : ["disabled_on"]=> "off|off|off" [M|T|D]
					 */
					if ( ! empty( $attributes['button_text_color'] ) && ! empty( $attributes['button_bg_color'] ) ) {
						$gb_button_block  = sprintf( '<!-- wp:button {"customBackgroundColor":"%s","customTextColor":"%s"} -->', $attributes['button_bg_color'], $attributes['button_text_color'] );
						$gb_button_block .= PHP_EOL;
						$gb_button_block .= sprintf( '<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-background" href="%s" %s style="background-color:%s;color:%s">%s</a></div>', $attributes['button_url'], $target_url, $attributes['button_bg_color'], $attributes['button_text_color'], $attributes['button_text'] );
					} elseif ( ! empty( $attributes['button_text_color'] ) ) {
						$gb_button_block  = sprintf( '<!-- wp:button {"customTextColor":"%s"} -->', $attributes['button_text_color'] );
						$gb_button_block .= PHP_EOL;
						$gb_button_block .= sprintf( '<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-background" href="%s" %s style="color:%s">%s</a></div>', $attributes['button_url'], $target_url, $attributes['button_text_color'], $attributes['button_text'] );
					} elseif ( ! empty( $attributes['button_bg_color'] ) ) {
						$gb_button_block  = sprintf( '<!-- wp:button {"customBackgroundColor":"%s"} -->', $attributes['button_bg_color'] );
						$gb_button_block .= PHP_EOL;
						$gb_button_block .= sprintf( '<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-background" href="%s" %s style="background-color:%s;">%s</a></div>', $attributes['button_url'], $target_url, $attributes['button_bg_color'], $attributes['button_text'] );
					} else {
						$gb_button_block  = '<!-- wp:button -->';
						$gb_button_block .= PHP_EOL;
						$gb_button_block .= sprintf( '<div class="wp-block-button"><a class="wp-block-button__link " href="%s" %s>%s</a></div>', $attributes['button_url'], $target_url, $attributes['button_text'] );
					}
					$gb_button_block .= PHP_EOL;
					$gb_button_block .= '<!-- /wp:button -->';

					if ( false === strpos( $post_content, $shortcode ) ) {
						$shortcode = $match[0] . ']';
					}
					$post_content = str_replace( $shortcode, $gb_button_block, $post_content );
					$status       = 'migrated';
				} elseif ( 'et_pb_image' === $shortcode_name || 'et_pb_fullwidth_image' === $shortcode_name ) {

					$att_id = attachment_url_to_postid( $attributes['src'] );

					/**
					 * Divi attributes: Skipped because of less support in Core gutenberg block.
					 * force_fullwidth="on" positioning="absolute"
					 * disabled_on="off|off|on"
					 * module_id="img-test-ID" module_class="img-test-class"
					 * border_color_all="#000000" border_width_all="3px"
					 * border_color_right="#000000" border_width_right="9px"
					 * custom_css_main_element="background:red;" custom_css_before="background:green;" custom_css_after="background:blue;"
					 */

					if ( $att_id ) {
						$gb_attr   = sprintf( '"id":%s,"sizeSlug":"medium",', $att_id );
						$style_str = '';

						if ( empty( $attributes['align'] ) ) {
							$attributes['align'] = 'left';
						}
						if ( empty( $attributes['alt'] ) ) {
							$attributes['alt'] = '';
						}
						$gb_attr .= sprintf( '"align":"%s",', $attributes['align'] );
						if ( ! empty( $attributes['max_width'] ) ) {
							$gb_attr   .= sprintf( '"width":"%s",', $attributes['max_width'] );
							$style_str .= sprintf( 'max-width:%s;', $attributes['max_width'] );
						}
						if ( ! empty( $attributes['max_height'] ) ) {
							$gb_attr   .= sprintf( '"height":"%s",', $attributes['max_height'] );
							$style_str .= sprintf( 'max-height:%s;', $attributes['max_height'] );
						}
						$gb_img_block  = sprintf( '<!-- wp:image {%s} -->', trim( $gb_attr, ',' ) );
						$gb_img_block .= PHP_EOL;
						$gb_img_block .= sprintf( '<figure class="wp-block-image align%s size-medium"><img src="%s" alt="%s" class="wp-image-%s"/></figure>', $attributes['align'], $attributes['src'], $attributes['alt'], $att_id );
						$gb_img_block .= PHP_EOL;
						$gb_img_block .= '<!-- /wp:image -->';
						$status        = 'migrated';

						if ( false === strpos( $post_content, $shortcode ) ) {
							$shortcode = $match[0] . ']';
						}
						$post_content = str_replace( $shortcode, $gb_img_block, $post_content );

					} else {
						$gb_attr = sprintf( 'sizeSlug":"medium",' );

						if ( empty( $attributes['align'] ) ) {
							$attributes['align'] = 'left';
						}
						$gb_attr .= sprintf( '"align":"%s",', $attributes['align'] );

						$gb_img_block  = sprintf( '<!-- wp:image {%s} -->', trim( $gb_attr, ',' ) );
						$gb_img_block .= PHP_EOL;
						$gb_img_block .= sprintf( '<figure class="wp-block-image align%s size-medium"><img src="%s" alt="%s"/></figure>', $attributes['align'], $attributes['src'], $attributes['alt'] );
						$gb_img_block .= PHP_EOL;
						$gb_img_block .= '<!-- /wp:image -->';
						$status        = 'migrated';

						if ( false === strpos( $post_content, $shortcode ) ) {
							$shortcode = $match[0] . ']';
						}
						$post_content = str_replace( $shortcode, $gb_img_block, $post_content );
					}
				}
			}
			$post_content = str_replace( '<!-- wp:divi/placeholder -->', '', $post_content );
			$post_content = str_replace( '<!-- /wp:divi/placeholder -->', '', $post_content );

			if ( $this->dry_run ) {
				$status = 'to be ' . $status;
			}

			if ( 'to be cleared' !== $status ) {
				$logs[] = array( $post['ID'], $shortcode_name, $shortcode, $status );
			}
		}

		if ( ! $this->dry_run ) {

			$new_post = array(
				'ID'                => $post['ID'],
				'post_content'      => $post_content,
				'post_modified'     => $post['post_modified'],
				'post_modified_gmt' => $post['post_modified_gmt'],
			);

			$result = wp_update_post( $new_post, true );
		} else {
			$result = true;
		}

		return $result;
	}

	/**
	 * Hook callback to alter the post modification time.
	 * This needs to be added to update the post_modified time while inserting or updating the post.
	 *
	 * @param array $data    Data.
	 * @param array $postarr Post array.
	 *
	 * @return mixed
	 */
	private function alter_post_modification_time( $data, $postarr ) {

		if ( ! empty( $postarr['post_modified'] ) && ! empty( $postarr['post_modified_gmt'] ) ) {
			$data['post_modified']     = $postarr['post_modified'];
			$data['post_modified_gmt'] = $postarr['post_modified_gmt'];
		}

		return $data;
	}

	/**
	 * Create log files.
	 *
	 * @param string $file_name File name.
	 * @param array  $logs      Log array.
	 */
	private function create_log_file( $file_name, $logs ) {

		$uploads     = wp_get_upload_dir();
		$source_file = $uploads['basedir'] . '/divi-migration-logs/';

		if ( ! file_exists( $source_file ) ) {
			mkdir( $source_file, 0777, true );
		}

		$file = fopen( $source_file . $file_name, 'w' ); // @codingStandardsIgnoreLine

		foreach ( $logs as $row ) {
			fputcsv( $file, $row );
		}

		$csv_generated = fclose( $file ); // @codingStandardsIgnoreLine

		$source_file_url  = str_replace( $uploads['subdir'], '', $uploads['url'] );
		$source_file_url .= '/divi-migration-logs/' . $file_name;

		if ( $csv_generated ) {
			$this->write_log( sprintf( 'Log created successfully - %s', $source_file_url ) );
		} else {
			$this->warning( sprintf( 'Failed to write the logs - %s', $source_file_url ) );
		}
	}

	/**
	 * Method to add a log entry and to output message on screen
	 *
	 * @param string $msg             Message to add to log and to outout on screen.
	 * @param int    $msg_type        Message type - 0 for normal line, -1 for error, 1 for success, 2 for warning.
	 * @param bool   $suppress_stdout If set to TRUE then message would not be shown on screen.
	 * @return void
	 */
	protected function write_log( $msg, $msg_type = 0, $suppress_stdout = false ) {

		// backward compatibility.
		if ( true === $msg_type ) {
			// its an error
			$msg_type = -1;
		} elseif ( true === $msg_type ) {
			// normal message
			$msg_type = 0;
		}

		$msg_type = intval( $msg_type );

		$msg_prefix = '';

		// Message prefix for use in log file
		switch ( $msg_type ) {

			case -1:
				$msg_prefix = 'Error: ';
				break;

			case 1:
				$msg_prefix = 'Success: ';
				break;

			case 2:
				$msg_prefix = 'Warning: ';
				break;

		}

		// If we don't want output shown on screen then
		// bail out.
		if ( true === $suppress_stdout ) {
			return;
		}

		switch ( $msg_type ) {

			case -1:
				WP_CLI::error( $msg );
				break;

			case 1:
				WP_CLI::success( $msg );
				break;

			case 2:
				WP_CLI::warning( $msg );
				break;

			case 0:
			default:
				WP_CLI::line( $msg );
				break;

		}

	}

	/**
	 * Method to log an error message and stop the script from running further
	 *
	 * @param string $msg Message to add to log and to outout on screen
	 * @return void
	 */
	protected function error( $msg ) {
		$this->write_log( $msg, -1 );
	}

	/**
	 * Method to log a success message
	 *
	 * @param string $msg Message to add to log and to outout on screen
	 * @return void
	 */
	protected function success( $msg ) {
		$this->write_log( $msg, 1 );
	}

	/**
	 * Method to log a warning message
	 *
	 * @param string $msg Message to add to log and to outout on screen
	 * @return void
	 */
	protected function warning( $msg ) {
		$this->write_log( $msg, 2 );
	}
}
