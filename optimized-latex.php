<?php
/*
Plugin Name: Optimized Latex
Plugin URI: http://www.webfish.se/wp/plugins/
Description: Converts inline latex code into PNG images. Use either [latex]e^{\i \pi} + 1 = 0[/latex] or $latex e^{\i \pi} + 1 = 0$ syntax. Decrease your load time: display them with AJAX. 
Version: 0.7.3
Author: Tobias Nyholm
Author URI: http://www.tnyholm.se

Copyright: Tobias Nyholm 2010
Copyright: Automattic, Inc.
Copyright: Sidney Markowitz.
License: GPL3
*/
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

class Optimized_LaTeX {
	var $options;
	var $OnLoadScript="";

	function init() {
		$this->options = get_option( 'optimized_latex' );
		
		add_action( 'wp_head', array( &$this, 'wp_head' ) );

		add_filter( 'the_content', array( &$this, 'inline_to_shortcode' ) );
		add_shortcode( 'latex', array( &$this, 'shortcode' ) );

		
		// This isn't really correct.  This adds all shortcodes to comments, not just LaTeX
		if ( !has_filter( 'comment_text', 'do_shortcode' ) && $this->options['comments'] ) {
			add_filter( 'comment_text', array( &$this, 'inline_to_shortcode' ) );
			add_filter( 'comment_text', 'do_shortcode', 31 );
		}
		
	}

	function wp_head() {
		if ( !$this->options['css'] )
			return;
?>
<style type="text/css">
/* <![CDATA[ */
<?php echo $this->options['css']; ?>

/* ]]> */
</style>
<?php
	}
	


	/**
	 * Creates image-tags from the latex code. Cache them on the server and display them with ajax. 
	 * [latex size=0 color=000000 background=ffffff]\LaTeX[/latex]
	 * @param $_atts
	 * @param $latex
	 */
	function shortcode( $_atts, $latex ) {
		//load attributes or defualts
		$atts = shortcode_atts( array(
			'size' => '0',
			'color' => $this->options['fg'],
			'background' => $this->options['bg'],
		), $_atts );
		
		    //rename the attr variables. 
			$bg_hex = $atts['background'];
			$fg_hex = $atts['color'];
			$size = $atts['size'];
	
	
		//Replace nasty characters
		$latex = str_replace(
			array( '&lt;', '&gt;', '&quot;', '&#8220;', '&#8221;', '&#039;', '&#8125;', '&#8127;', '&#8217;', '&#038;', '&amp;', "\n", "\r", "\xa0", '&#8211;', '<br />', '<p>', '</p>' ),
			array( '<',    '>',    '"',      '``',       "''",     "'",      "'",       "'",       "'",       '&',      '&',     ' ',  ' ',  ' ',    '-',       ' ',      ' ',    ' ' ),
			$latex
		);

		//get a nice filename
		$formula_hash = md5($latex.$bg_hex.$fg_hex.$size);
		$formula_filename = 'tex_'.$formula_hash.'.png';

		//get some great variables
		$cache_path = WP_CONTENT_DIR.'/cache/latex/';
		$cache_formula_path = $cache_path . $formula_filename;
		$cache_url = WP_CONTENT_URL.'/cache/latex/';
		$cache_formula_url = $cache_url . $formula_filename;
	
		//check if file exists
		if (!is_file($cache_formula_path)) {
     		if (!class_exists('WP_Http')) //load some classes
     			require_once (ABSPATH.'wp-includes/class-http.php');
     		
            $http = new WP_Http;
			//$url=$this->server.rawurlencode(html_entity_decode($formula_text));

    
			//prepare the url for wordpress latex server. 
			$tserver="http://l.wordpress.com/latex.php?bg=$bg_hex&fg=$fg_hex&s=$size&latex=";
			//$url = clean_url( $latex_object->url );
			$url=$tserver.rawurlencode(html_entity_decode($latex));
			//echo "t: <pre>$url</pre> <br>";
		
			//get the url. 
			$res=$http->get($url);//die("<pre>".print_r($res,true));
			//we just want the body, 
			$res=$res['body'];
			
            // this will copy the created tex-image to your cache-folder
            if(strlen($res)){
				$cache_file = @fopen($cache_formula_path,'w');
				if($cache_file==null){//if the file could not be opend
					if(is_writable( WP_CONTENT_DIR . '/cache' )){//check if the cache path is writeble
						@mkdir($cache_path,0777,true);//create the path
						$cache_file = @fopen($cache_formula_path,'w');//open the file again
					}
					else{//if we cant cache it. Then display it on the bad way...
						return "<img src='$url' alt='$latex' title='$latex' class='latex' />"; 
					}
						
				}
				fputs($cache_file, $res); //create the image
				fclose($cache_file); //close the file				
			}
		}
		
		//get the height and width
		list($width, $height)=getimagesize($cache_formula_path);
		
		//Load by ajax or print the image directly
		if($this->options['ajax']==1){
			$rand="tex_".rand(10,10000);
			$this->addOnLoadScript("tex_ajax('".WP_PLUGIN_URL."/optimized-latex/ajax.php?class=latex&image=$formula_filename&alt=".urlencode($latex)."&title=".urlencode($latex)."&width=$width&height=$height&path=".urlencode(WP_PLUGIN_URL."/optimized-latex")."','$rand');");
			return "<span id='$rand'></span>";
		}
		else{
			return "<img src='$cache_formula_url' alt='$latex' title='$latex' class='latex' width='$width' height='$height' />";
		}
		
		
	}
		
	
	function inline_to_shortcode( $content ) {
		if ( false === strpos( $content, '$latex' ) )
			return $content;

		return preg_replace_callback( '#\$latex[= ](.*?[^\\\\])\$#', array( &$this, 'inline_to_shortcode_callback' ), $content );
	}

	/**
	 * Make the inline latex to normal shortcode.  
	 */
	function inline_to_shortcode_callback( $matches ) {
		$r = "[latex";

		if ( preg_match( '/.+((?:&#038;|&amp;)s=(-?[0-4])).*/i', $matches[1], $s_matches ) ) {
			$r .= ' size="' . (int) $s_matches[2] . '"';
			$matches[1] = str_replace( $s_matches[1], '', $matches[1] );
		}

		if ( preg_match( '/.+((?:&#038;|&amp;)fg=([0-9a-f]{6})).*/i', $matches[1], $fg_matches ) ) {
			$r .= ' color="' . $fg_matches[2] . '"';
			$matches[1] = str_replace( $fg_matches[1], '', $matches[1] );
		}
	
		if ( preg_match( '/.+((?:&#038;|&amp;)bg=([0-9a-f]{6})).*/i', $matches[1], $bg_matches ) ) {
			$r .= ' background="' . $bg_matches[2] . '"';
			$matches[1] = str_replace( $bg_matches[1], '', $matches[1] );
		}

		return "$r]{$matches[1]}[/latex]";
	}


	/**
	 * add an other javascript code line
	 * @param unknown_type $str
	 */
	private function addOnLoadScript($str){
		$this->OnLoadScript.="\n".$str;
	}

	/**
	 * return the javascript
	 */
	public function getOnloadScript(){
		if($this->OnLoadScript!="")
			return '<script language="javascript">var $j  = jQuery.noConflict();
		$j(window).load(function(){ '.$this->OnLoadScript."\n".' });</script>';
	}
	
	/**
	 * returns true or false
	 */
	public function useAjax(){return true;
		return $this->options['ajax']==1;
	}
}

/**
 * init the plugin
 */
if ( is_admin() ) {
	require( dirname( __FILE__ ) . '/optimized-latex-admin.php' );
	$optimized_latex = new Optimized_LaTeX_Admin;//init the admin file
	register_activation_hook( __FILE__, array( &$optimized_latex, 'activation_hook' ) );
} else {
	$optimized_latex = new Optimized_LaTeX;//init the latex functions
}
add_action( 'init', array( &$optimized_latex, 'init' ) );



function getLatexAjaxScripts(){
	global $optimized_latex;
	echo  $optimized_latex->getOnloadScript();
}
if($optimized_latex->useAjax()){
	add_action('wp_footer', 'getLatexAjaxScripts');
}


/**
 * Function: Enqueue JavaScripts/CSS
 */ 
add_action('wp_enqueue_scripts', 'optimized_latex_scripts');
function optimized_latex_scripts() {
	wp_enqueue_script('optimized-latex-ajax', plugins_url('/optimized-latex/ajax.js'), array(), '0,50', true);
	wp_enqueue_script("jquery");
	
}