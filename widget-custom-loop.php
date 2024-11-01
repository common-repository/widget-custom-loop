<?php

/**
 * Copyright (c) 2010 Marcelo Mesquita
 *
 * Written by Marcelo Mesquita <stallefish@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the
 * Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 * Public License can be found at http://www.gnu.org/copyleft/gpl.html
 *
 * Plugin Name: Widget Custom Loop
 * Plugin URI: http://marcelomesquita.com/
 * Description: Allow the creation of a custom loop.
 * Author: Marcelo Mesquita
 * Version: 0.8
 * Author URI: http://marcelomesquita.com/
 */

class Widget_Custom_Loop extends WP_Widget
{
	// ATRIBUTES /////////////////////////////////////////////////////////////////////////////////////
	var $path = '';

	// METHODS ///////////////////////////////////////////////////////////////////////////////////////
	/**
	 * load widget
	 *
	 * @name    widget
	 * @author  Marcelo Mesquita <stallefish@gmail.com>
	 * @since   2009-07-17
	 * @updated 2011-11-22
	 * @param   array $args - widget structure
	 * @param   array $instance - widget data
	 * @return  void
	 */
	function widget( $args, $instance )
	{
		global $post;

		// load posts
		$custom_loop = new WP_Query( "showposts={$instance[ 'showposts' ]}&cat={$instance[ 'category' ]}&orderby={$instance[ 'orderby' ]}&order={$instance[ 'order' ]}" );

		// show posts
		if( $custom_loop->have_posts() )
		{
			print $args[ 'before_widget' ];

			if( !empty( $instance[ 'title' ] ) ) print $args[ 'before_title' ] . $instance[ 'title' ] . $args[ 'after_title' ];

			// está pegando o link do loop padrão
			//$before_loop = preg_replace_callback( '/\{next ?(text=[\'\"]([^\}]+)[\'\"])?\}/', create_function( '$matches', 'return get_next_posts_link( $matches[ 2 ] );' ), $before_loop );
			//$before_loop = preg_replace_callback( '/\{prev ?(text=[\'\"]([^\}]+)[\'\"])?\}/', create_function( '$matches', 'return get_previous_posts_link( $matches[ 2 ] );' ), $before_loop );

			print $instance[ 'before_loop' ];

			while( $custom_loop->have_posts() )
			{
				$custom_loop->the_post();

				$loop = $instance[ 'loop' ];

				//$loop = str_replace( '{title}', get_the_title(), $loop );
				$loop = preg_replace_callback( '/\{title ?(length=[\'\"]([0-9]+)[\'\"])?\}/U', create_function( '$matches', 'return limit_chars( get_the_title(), $matches[ 2 ] );' ), $loop );
				//$loop = str_replace( '{excerpt}', get_the_excerpt(), $loop );
				$loop = preg_replace_callback( '/\{excerpt ?(length=[\'\"]([0-9]+)[\'\"])?\}/U', create_function( '$matches', 'return limit_chars( get_the_excerpt(), $matches[ 2 ] );' ), $loop );
				$loop = str_replace( '{permalink}', get_permalink(), $loop );
				$loop = str_replace( '{content}', get_the_content(), $loop );
				$loop = str_replace( '{author}', get_the_author(), $loop );
				$loop = str_replace( '{author-permalink}', get_author_posts_url( $post->post_author ), $loop );
				$loop = str_replace( '{categories}', get_the_category_list( ', ' ), $loop );
				$loop = str_replace( '{tags}', get_the_tag_list( '', ', ', '' ), $loop );
				$loop = str_replace( '{date}', get_the_time( get_option( 'date_format' ) ), $loop );
				$loop = str_replace( '{time}', get_the_time( get_option( 'time_format' ) ), $loop );
				$loop = preg_replace_callback( '/\{thumb ?(size=[\'\"]([^\'\"]+)[\'\"])? ?(attr=[\'\"]([^\}]*)[\'\"])?\}/U', create_function( '$matches', 'return get_the_post_thumbnail( NULL, $matches[ 2 ], $matches[ 4 ] );' ), $loop );
				$loop = preg_replace_callback( '/\{meta ?(key=[\'\"]([^\'\"]+)[\'\"])?\}/U', create_function( '$matches', 'return get_post_meta( ' . $post->ID . ', $matches[ 2 ], true );' ), $loop );

				print $loop;
			}

			// está pegando o link do loop padrão
			//$after_loop = preg_replace_callback( '/\{next ?(text=[\'\"]([^\}]+)[\'\"])?\}/', create_function( '$matches', 'return get_next_posts_link( $matches[ 2 ] );' ), $before_loop );
			//$after_loop = preg_replace_callback( '/\{prev ?(text=[\'\"]([^\}]+)[\'\"])?\}/', create_function( '$matches', 'return get_previous_posts_link( $matches[ 2 ] );' ), $after_loop );

			print $instance[ 'after_loop' ];

			print $args[ 'after_widget' ];
		}
	}

	/**
	 * update data
	 *
	 * @name    update
	 * @author  Marcelo Mesquita <stallefish@gmail.com>
	 * @since   2009-07-17
	 * @updated 2009-12-04
	 * @param   array $new_instance - new values
	 * @param   array $old_instance - old values
	 * @return  array
	 */
	function update( $new_instance, $old_instance )
	{
		if( empty( $new_instance[ 'showposts' ] ) or !is_numeric( $new_instance[ 'showposts' ] ) )
			$new_instance[ 'showposts' ] = 5;

		if( empty( $new_instance[ 'loop' ] ) )
		{
			$loop_model = get_option( 'loop_model' );

			if( empty( $loop_model ) )
			{
				$loop_model = '<p class="post-meta">{categories}</p><p class="post-meta">{date} - {time}</p>{thumb}<h3 class="post-title"><a href="{permalink}" title="{title}">{title}</a></h3><div class="entry">{excerpt}</div><p class="post-meta">por <a href="{author-permalink}" title="{author}">{author}</a></p>';

				update_option( 'loop_model', $loop_model );
			}

			$new_instance[ 'loop' ] = $loop_model;
		}

		return $new_instance;
	}

	/**
	 * widget options form
	 *
	 * @name    form
	 * @author  Marcelo Mesquita <stallefish@gmail.com>
	 * @since   2009-07-17
	 * @updated 2011-11-22
	 * @param   array $instance - widget data
	 * @return  void
	 */
	function form( $instance )
	{
		?>
			<p>
				<label for="<?php print $this->get_field_id( 'title' ); ?>"><?php _e( 'Title' ); ?>:</label>
				<input type="text" id="<?php print $this->get_field_id( 'title' ); ?>" name="<?php print $this->get_field_name( 'title' ); ?>" maxlength="26" value="<?php print $instance[ 'title' ]; ?>" class="widefat" />
			</p>

			<p>
				<label for="<?php print $this->get_field_id( 'category' ); ?>"><?php _e( 'Category' ); ?>:</label>
				<?php wp_dropdown_categories( "id" . $this->get_field_id( 'category' ) . "&name=" . $this->get_field_name( 'category' ) . "&show_option_all=" . __( 'all' ) . "&hide_empty=0&selected={$instance[ 'category' ]}&class=widefat" ); ?>
			</p>

			<p>
				<label for="<?php print $this->get_field_id( 'showposts' ); ?>"><?php _e( 'Showposts' ); ?>:</label><br />
				<input type="text" id="<?php print $this->get_field_id( 'showposts' ); ?>" name="<?php print $this->get_field_name( 'showposts' ); ?>" size="2" maxlength="2" value="<?php print $instance[ 'showposts' ]; ?>" />
			</p>

			<p>
				<label for="<?php print $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order By' ); ?>:</label><br />
				<select id="<?php print $this->get_field_id( 'orderby' ); ?>" name="<?php print $this->get_field_name( 'orderby' ); ?>">
					<option value="ID" <?php if( 'ID' == $instance[ 'orderby' ] ) print 'selected="selected"'; ?>"><?php _e( 'ID' ); ?></option>
					<option value="author" <?php if( 'author' == $instance[ 'orderby' ] ) print 'selected="selected"'; ?>"><?php _e( 'Author' ); ?></option>
					<option value="title" <?php if( 'title' == $instance[ 'orderby' ] ) print 'selected="selected"'; ?>"><?php _e( 'Title' ); ?></option>
					<option value="date" <?php if( 'date' == $instance[ 'orderby' ] ) print 'selected="selected"'; ?>"><?php _e( 'Date' ); ?></option>
					<option value="modified" <?php if( 'modified' == $instance[ 'orderby' ] ) print 'selected="selected"'; ?>"><?php _e( 'Modified' ); ?></option>
					<option value="comment_count" <?php if( 'comment_count' == $instance[ 'orderby' ] ) print 'selected="selected"'; ?>"><?php _e( 'Popular' ); ?></option>
					<option value="rand" <?php if( 'rand' == $instance[ 'orderby' ] ) print 'selected="selected"'; ?>"><?php _e( 'Random' ); ?></option>
				</select>
				<select id="<?php print $this->get_field_id( 'order' ); ?>" name="<?php print $this->get_field_name( 'order' ); ?>">
					<option value="desc" <?php if( 'desc' == $instance[ 'order' ] ) print 'selected="selected"'; ?>"><?php _e( 'Descendant' ); ?></option>
					<option value="asc" <?php if( 'asc' == $instance[ 'order' ] ) print 'selected="selected"'; ?>"><?php _e( 'Ascendant' ); ?></option>
				</select>
			</p>

			<p>
				<label for="<?php print $this->get_field_id( 'before_loop' ); ?>"><?php _e( 'Before Loop' ); ?>:</label>
				<textarea id="<?php print $this->get_field_id( 'before_loop' ); ?>" name="<?php print $this->get_field_name( 'before_loop' ); ?>" cols="23" rows="2" class="widefat"><?php print $instance[ 'before_loop' ]; ?></textarea>
				<!--<small><?php _e( 'You can use any of this shortcodes:' ); ?> {next} {prev}</small>-->
			</p>

			<p>
				<label for="<?php print $this->get_field_id( 'loop' ); ?>"><?php _e( 'Loop' ); ?>:</label>
				<textarea id="<?php print $this->get_field_id( 'loop' ); ?>" name="<?php print $this->get_field_name( 'loop' ); ?>" cols="23" rows="5" class="widefat"><?php print $instance[ 'loop' ]; ?></textarea>
				<small><?php _e( 'You can use any of this shortcodes:' ); ?> {title [length='100']} {excerpt [length='100']} {permalink} {content} {author} {author-permalink} {categories} {tags} {date} {time} {thumb [size='thumbnail']} {meta key='meta'}</small>
			</p>

			<p>
				<label for="<?php print $this->get_field_id( 'after_loop' ); ?>"><?php _e( 'After Loop' ); ?>:</label>
				<textarea id="<?php print $this->get_field_id( 'after_loop' ); ?>" name="<?php print $this->get_field_name( 'after_loop' ); ?>" cols="23" rows="2" class="widefat"><?php print $instance[ 'after_loop' ]; ?></textarea>
				<!--<small><?php _e( 'You can use any of this shortcodes:' ); ?> {next} {prev}</small>-->
			</p>
		<?php
	}

	// CONSTRUCTOR ///////////////////////////////////////////////////////////////////////////////////
	/**
	 * @name    Widget_Custom_Loop
	 * @author  Marcelo Mesquita <stallefish@gmail.com>
	 * @since   2009-07-17
	 * @updated 2010-05-06
	 * @return  void
	 */
	function Widget_Custom_Loop()
	{
		// define plugin path
		$this->path = dirname( __FILE__ ) . '/';

		// register widget
		$this->WP_Widget( 'custom-loop', 'Custom Loop', array( 'classname' => 'widget_custom_loop', 'description' => __( 'Allow the creation of a custom loop', 'widget-custom-loop' ) ), array( 'width' => 400 ) );

		// includes
		if( !function_exists( 'limit_chars' ) )
			include( $this->path . 'inc/limit-chars.php' );

		include_once( ABSPATH . WPINC . '/post-thumbnail-template.php' );
	}

	// DESTRUCTOR ////////////////////////////////////////////////////////////////////////////////////

}

// register widget
add_action( 'widgets_init', create_function( '', 'return register_widget( "Widget_Custom_Loop" );' ) );

?>
