<?php
/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 */

if ( !defined('ABSPATH') ) exit;

class Optimized_LaTeX_Admin extends Optimized_LaTeX {
	var $errors;

	function init() {
		parent::init();
		$this->errors = new WP_Error;

		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
	}

	function admin_menu() {
		$hook = add_options_page( 'Optimized LaTeX', 'Optimized LaTeX', 'manage_options', 'optimized-latex', array( &$this, 'admin_page' ) );
		add_action( "load-$hook", array( &$this, 'admin_page_load' ) );

		if (!is_writable( WP_CONTENT_DIR . '/cache' ) )
			add_action( 'admin_notices', create_function('$a', 'echo "<div id=\'latex-chmod\' class=\'error fade\'><p><code>wp-content/cache/</code> must be writeable for Optimized LaTeX to work.</p></div>";') );
		if ( !empty( $this->options['activated'] ) ) {
			add_action( 'admin_notices', create_function('$a', 'echo "<div id=\'latex-config\' class=\'updated fade\'><p>Make sure to check the <a href=\'options-general.php?page=optimized-latex\'>Optimized LaTeX Options</a> for any errors.</p></div>";') );
			unset( $this->options['activated'] );
			update_option( 'optimized_latex', $this->options );
		}

		add_filter( 'plugin_action_links_' . plugin_basename( dirname( __FILE__ ) . '/optimized-latex.php' ), array( &$this, 'plugin_action_links' ) );
	}
	
	function plugin_action_links( $links ) {
		array_unshift( $links, '<a href="options-general.php?page=optimized-latex">' . __( 'Settings' ) . "</a>" );
		return $links;
	}

	/**
	 * load the admin page
	 */
	function admin_page_load() {
		if ( !current_user_can( 'manage_options' ) )
			wp_die( __( 'Insufficient LaTeX-fu', 'optimized-latex' ) );
	
		add_action( 'admin_head', array( &$this, 'admin_head' ) );

		//if the post is empty, then you are done here. 
		if ( empty( $_POST['optimized_latex'] ) ) {
			return;
		}
		
		check_admin_referer( 'optimized-latex' );
		
		if ( $this->update( stripslashes_deep( $_POST['optimized_latex'] ) ) ) {
			wp_redirect( add_query_arg( 'updated', '', wp_get_referer() ) );
			exit;
		}
	}
	
	/**
	 * this handles the post
	 * @param $new
	 */
	function update( $new ) {
		if ( !is_array( $this->options ) )
			$this->options = array();
		extract( $this->options, EXTR_SKIP );
	
		
		if ( isset( $new['fg'] ) ) {
			$fg = strtolower( substr( preg_replace( '/[^0-9a-f]/i', '', $new['fg'] ), 0, 6 ) );
			if ( 6 > $l = strlen( $fg ) ) {
				$this->errors->add( 'fg', __( 'Invalid text color', 'optimized-latex' ), $new['fg'] );
				$fg .= str_repeat( '0', 6 - $l );
			}
		}
	
		if ( isset( $new['bg'] ) ) {
			if ( 'transparent' == trim( $new['bg'] ) ) {
				$bg = 'transparent';
			} else {
				$bg = substr( preg_replace( '/[^0-9a-f]/i', '', $new['bg'] ), 0, 6 );
				if ( 6 > $l = strlen( $bg ) ) {
					$this->errors->add( 'bg', __( 'Invalid background color', 'optimized-latex' ), $new['bg'] );
					$bg .= str_repeat( '0', 6 - $l );
				}
			}
		}
		$comments=0;
		if ( isset( $new['comments'] ) ) {
			$comments = intval( $new['comments'] != 0 );
		}
		$ajax=0;
		if ( isset( $new['ajax'] ) ) {
			$ajax = intval( $new['ajax'] != 0 );
		}
		if ( isset( $new['css'] ) ) {
			$css = str_replace( array( "\n", "\r" ), "\n", $new['css'] );
			$css = trim( preg_replace( '/[\n]+/', "\n", $css ) );
		}
	


		$this->options = compact( 'bg', 'fg', 'comments', 'css', 'ajax');
		update_option( 'optimized_latex', $this->options );
		return !count( $this->errors->get_error_codes() );
	}
	
	
	
	function admin_head() {
?>

<style type="text/css">
/* <![CDATA[ */
p.test-image {
	text-align: center;
	font-size: 1.4em;
}
img.test-image {
	display: block;
	margin: 0 auto 1em;
}
.syntax p {
	margin-top: 0;
}
.syntax code {
	white-space: nowrap;
}

/* ]]> */
</style>
<?php
	}

	function admin_page() {
		if ( !current_user_can( 'manage_options' ) )
			wp_die( __( 'Insufficient LaTeX-fu', 'optimized-latex' ) );
	
				
		if ( !is_array( $this->options ) )
			$this->options = array();

		$values = $this->options;
	
		$errors = array();
		if ( $errors = $this->errors->get_error_codes() ) :
			foreach ( $errors as $e )
				$values[$e] = $this->errors->get_error_data( $e );
	?>
	<div id='latex-config-errors' class='error'>
		<ul>
		<?php foreach ( $this->errors->get_error_messages() as $m ) : ?>
			<li><?php echo $m; ?></li>
		<?php endforeach; ?>
		</ul>
	</div>
	<?php	endif; ?>
	
	<div class='wrap'>
	<h2><?php _e( 'Optimized LaTeX Options', 'optimized-latex' ); ?></h2>
	
	
	<form action="<?php echo esc_url( remove_query_arg( 'updated' ) ); ?>" method="post">

	<table class="form-table">
	<tbody>
		<?php if ( empty( $errors ) ): ?>
		<tr>
			<th scope="row"><?php _e( 'Syntax' ); ?></th>
			<td class="syntax">
				<p><?php printf( __( 'You may use either the shortcode syntax %s<br /> or the &#8220;inline&#8221; syntax %s<br /> to insert LaTeX into your posts.', 'optimized-latex' ),
					'<code>[latex]e^{\i \pi} + 1 = 0[/latex]</code>',
					'<code>$latex e^{\i \pi} + 1 = 0$</code>'
				); ?></p>
				<p><?php _e( 'For more information, see the <a href="http://www.webfish.se/wp/plugins/optimized-latex#faq/">Webfish FAQ</a>' ); ?></p>
			</td>
		</tr>
		<?php endif; ?>
	

		<tr<?php if ( in_array( 'fg', $errors ) ) echo ' class="form-invalid"'; ?>>
			<th scope="row"><label for="optimized-latex-fg"><?php _e( 'Default text color', 'optimized-latex' ); ?></label></th>
			<td>
				<input type='text' name='optimized_latex[fg]' value='<?php echo esc_attr( $values['fg'] ); ?>' id='optimized-latex-fg' />
				<?php _e( 'A six digit hexadecimal number like <code>000000</code> or <code>ffffff</code>' ); ?>
			</td>
		</tr>
		<tr<?php if ( in_array( 'bg', $errors ) ) echo ' class="form-invalid"'; ?>>
			<th scope="row"><label for="optimized-latex-bg"><?php _e( 'Default background color', 'optimized-latex' ); ?></label></th>
			<td>
				<input type='text' name='optimized_latex[bg]' value='<?php echo esc_attr( $values['bg'] ); ?>' id='optimized-latex-bg' />
				<?php _e( 'A six digit hexadecimal number like <code>000000</code> or <code>ffffff</code>, or <code>transparent</code>' ); ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for='optimized-latex-comments'><?php _e( 'Comments', 'optimized-latex' ); ?></label></th>
			<td>
				<input type='checkbox' name='optimized_latex[comments]' value='1'<?php checked( $values['comments'], 1 ); ?> id='optimized-latex-comments' />
				<?php _e( 'Parse LaTeX in comments?', 'optimized-latex' ); ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for='optimized-latex-ajax'><?php _e( 'Use ajax', 'optimized-latex' ); ?></label></th>
			<td>
				<input type='checkbox' name='optimized_latex[ajax]' value='1'<?php checked( $values['ajax'], 1 ); ?> id='optimized-latex-ajax' />
				<?php _e( 'Use ajax to display the latex-images? (It will make the page load faster.)', 'optimized-latex' ); ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="optimized-latex-css"><?php _e( 'Custom CSS to use with the LaTeX images', 'optimized-latex' ); ?></label></th>
			<td>
				<textarea name='optimized_latex[css]' id='optimized-latex-css' rows="8" cols="50"><?php echo esc_html( $values['css'] ); ?></textarea>
			</td>
		</tr>


	
	</tbody>
	</table>
	
	
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php echo esc_attr( __( 'Update LaTeX Options', 'optimized-latex' ) ); ?>" />
		<?php wp_nonce_field( 'optimized-latex' ); ?>
	</p>
	</form>
	</div>
	<?php
	}
	
	// Sets up default options
	function activation_hook() {
		if ( is_array( $this->options ) )
			extract( $this->options, EXTR_SKIP );
	
		global $themecolors;
	
		if ( empty($bg) )
			$bg = isset( $themecolors['bg'] ) ? $themecolors['bg'] : 'ffffff';
		if ( empty($fg) )
			$fg = isset( $themecolors['text'] ) ? $themecolors['text'] : '000000';
	
		if ( empty( $comments ) )
			$comments = 0;
			
		if ( empty( $ajax ) )
			$ajax = 1;
	
		if ( empty( $css ) )
			$css = 'img.latex { vertical-align: middle; border: none; }';
		
		$activated = true;

		$this->options = compact( 'bg', 'fg',  'comments', 'css',  'ajax', 'activated' );
		update_option( 'optimized_latex', $this->options );
	}
}
