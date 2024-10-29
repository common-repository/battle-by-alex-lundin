<?php

namespace AslBattles\FrontEnd;
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://vk.com/aslundin
 * @since      1.0.0
 *
 * @package    Asl_Battle
 * @subpackage Asl_Battle/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Asl_Battle
 * @subpackage Asl_Battle/public
 * @author     Alex Lundin <aslundin@yandex.ru>
 */
class AslBattlePublic {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	public function battle_shortcode() {
		add_shortcode( 'asl-battle', [ $this, 'render_shortcode_battle' ] );
	}

	public function render_shortcode_battle( $atts ) {
		global $wpdb;
		$params    = shortcode_atts(
			array(
				'id' => null, // параметр 1
			),
			$atts
		);
		$id        = $params['id'];
		$dbname    = $wpdb->prefix . 'battle_items_table';
		$dbComment = $wpdb->prefix . 'battle_comments_table';

		$head                 = $wpdb->get_results( "SELECT post_title, post_content, post_modified FROM $wpdb->posts WHERE ID = $id" );
		$head_first_argument  = get_post_meta( $id, 'first_argument', true );
		$head_second_argument = get_post_meta( $id, 'second_argument', true );

		$arguments = $wpdb->get_results( "SELECT * FROM $dbname WHERE `id_item` = $id" );
		$comments  = $wpdb->get_results( "SELECT * FROM $dbComment WHERE `comment_battle_id` = $id" );
		$first     = get_post_meta( $id, 'first_argument', true );
		$second    = get_post_meta( $id, 'second_argument', true );
		$date      = date( "M d, Y", strtotime( $head[0]->post_modified ) );
		$rating    = get_post_meta( $id, 'rating', true );
		$content   = $head[0]->post_content;
		$avatar    = explode( " ", get_post_meta( $id, 'username', true ) );
		if ( array_key_exists( 1, $avatar ) ) {
			$avatar_name = mb_substr( $avatar[0], 0, 1 ) . mb_substr( $avatar[1], 0, 1 );
		} else {
			$avatar_name = mb_substr( $avatar[0], 0, 1 );
		}
		$name              = get_post_meta( $id, 'username', true );
		$countFirstArg     = 0;
		$countSecondArg    = 0;
		$countPlusesFirst  = 0;
		$countPlusesSecond = 0;

		foreach ( $arguments as $argument ) {
			if ( $argument->argument === 'first' && $argument->moderate === '1' ) {
				$countFirstArg ++;
				$countPlusesFirst += (int) $argument->rating;
			} elseif ( $argument->argument === 'second' && $argument->moderate === '1' ) {
				$countSecondArg ++;
				$countPlusesSecond += (int) $argument->rating;
			}
		}
		$part          = 100 / ( $countFirstArg + $countSecondArg );
		$firstPercent  = $part * $countFirstArg * 100;
		$secondPercent = $part * $countSecondArg * 100;

		$listPublicArg = array_filter( $arguments, static function ( $var ) {
			return $var->moderate === '1';
		} );

		$publicComments = array_filter( $comments, static function ( $var ) {
			return $var->comment_moderate === '1';
		} );

		$out =
			"<div class='asl-battle' id='asl-battle-$id' data-battle='$id'>
				<div class='battle-title-main'>
	                <div role='heading' class='block-battle battle-title p-name'>
	                    <div class='battle-title-side-a'>
	                        <div>$first</div>
	                    </div>
	                    <div class='battle-title-vs'>
	                        <div class='battle-title-vs-text'>vs</div>
	                    </div>
	                    <div class='battle-title-side-b'>
	                        <div>$second</div>
	                    </div>
	                </div>
	                <div class='post-actions-line'>
	                    <span class='post-actions-line-item'>
	                    	<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 18 19'><path d='M4.60069444,4.09375 L3.25,4.09375 C2.47334957,4.09375 1.84375,4.72334957 1.84375,5.5 L1.84375,7.26736111 L16.15625,7.26736111 L16.15625,5.5 C16.15625,4.72334957 15.5266504,4.09375 14.75,4.09375 L13.3993056,4.09375 L13.3993056,4.55555556 C13.3993056,5.02154581 13.0215458,5.39930556 12.5555556,5.39930556 C12.0895653,5.39930556 11.7118056,5.02154581 11.7118056,4.55555556 L11.7118056,4.09375 L6.28819444,4.09375 L6.28819444,4.55555556 C6.28819444,5.02154581 5.9104347,5.39930556 5.44444444,5.39930556 C4.97845419,5.39930556 4.60069444,5.02154581 4.60069444,4.55555556 L4.60069444,4.09375 Z M6.28819444,2.40625 L11.7118056,2.40625 L11.7118056,1 C11.7118056,0.534009742 12.0895653,0.15625 12.5555556,0.15625 C13.0215458,0.15625 13.3993056,0.534009742 13.3993056,1 L13.3993056,2.40625 L14.75,2.40625 C16.4586309,2.40625 17.84375,3.79136906 17.84375,5.5 L17.84375,15.875 C17.84375,17.5836309 16.4586309,18.96875 14.75,18.96875 L3.25,18.96875 C1.54136906,18.96875 0.15625,17.5836309 0.15625,15.875 L0.15625,5.5 C0.15625,3.79136906 1.54136906,2.40625 3.25,2.40625 L4.60069444,2.40625 L4.60069444,1 C4.60069444,0.534009742 4.97845419,0.15625 5.44444444,0.15625 C5.9104347,0.15625 6.28819444,0.534009742 6.28819444,1 L6.28819444,2.40625 Z M1.84375,8.95486111 L1.84375,15.875 C1.84375,16.6516504 2.47334957,17.28125 3.25,17.28125 L14.75,17.28125 C15.5266504,17.28125 16.15625,16.6516504 16.15625,15.875 L16.15625,8.95486111 L1.84375,8.95486111 Z'></path></svg>
	                        $date
	                    </span>
	                </div>
	            </div>
	            <div class='block-battle post post-layout-block post-type-battle'>
                	<div class='post-upvote'>
                    	<span class='upvote' onclick='updRatingBattle(this)' data-battle='$id' data-rating='$rating'>
                        	<span>▲</span>
                         	$rating
                     </span>
                    <div class='clearfix20'></div>
                </div>
	                <div class='text-body text-body-layout-notitle text-body-type-post e-content'>
	                    $content
	                </div>
	                <div class='post-footer'>
	                    <div class='p-author'>
	                        <article class='user user-small'>
	                            <span class='user-avatar'>
	                                <span class='avatar'>
	                                    <span class='sb-avatar'>$avatar_name</span>
	                                </span>
	                            </span>
	                            <span class='user-info'>
	                                <span>
	                                    <span class='user-name'>$name</span>
	                                </span>
	                            </span>
	                            <span class='user-footer'>$date</span>
	                        </article>
	                    </div>
	                </div>
	            </div>
	            <div class='content'>
                	<div id='comments' class='post-comments'>";
		if ( count( $arguments ) !== 0 ) {
			$out .= "<div class='battle-stats'>
            					<div class='battle-stats-arguments battle-side-a-color'>
            						<strong>$countFirstArg argument and $countPlusesFirst pluses</strong><br/>
                					<small>for &laquo;$first&raquo;</small>
                				</div>
            					<div class='battle-stats-graph'>
                					<div class='battle-side-a-background' style='width: $firstPercent%'></div>
                					<div class='battle-side-b-background' style='width: $secondPercent%'></div>
            					</div>
            					<div class='battle-stats-arguments battle-side-b-color' style='text-align:right'>
            						<strong>$countSecondArg argument and $countPlusesSecond pluses</strong><br/>
            						<small>for &laquo;$second&raquo;</small>
            					</div>
        					 </div>";
		}
		$out .= "<div class='post-comments-list'>";
		foreach ( $listPublicArg as $item ) {
			$date_args       = date( "M d, Y", strtotime( $item->created_at ) );
			$avatar_arg      = explode( " ", $item->username );
			if ( array_key_exists( 1, $avatar_arg ) ) {
				$avatar_name_arg = mb_substr( $avatar_arg[0], 0, 1 ) . mb_substr( $avatar_arg[1], 0, 1 );
			} else {
				$avatar_name_arg = mb_substr( $avatar_arg[0], 0, 1 );
			}
			$secondComments  = array_filter( $publicComments, static function ( $var ) use ( $item ) {
				return $var->comment_argument_id === $item->id;
			} );
			$firstComments   = array_filter( $publicComments, static function ( $var ) use ( $item ) {
				return $var->comment_argument_id === $item->id;
			} );

			if ( $item->argument === 'second' ) {
				$out .= "<div data-argument='$item->id'>
 											<div class='clearfix'></div>
                    						<div class='battle-comment-prefix battle-comment-prefix-side-b'>
                        						for &laquo;$second&raquo;
                    						</div>
                    						<div class='block-battle comment comment-type-battle comment-type-battle-side-b'>
                        						<div class='comment-header'>
                            						<div class='comment-title'>$item->title</div>
                            						<div class='comment-type-battle-userinfo'>
                            							<span class='user user-tiny'>
                            								<span class='avatar user-avatar'>
                            									<span class='sb-avatar'>$avatar_name_arg</span>
                            								</span>
                                							<span class='user-name'>$item->username</span>
                            							</span>
                                						<span class='reply-date'>$date_args</span>
                            						</div>
                        						</div>
                        						<div class='comment-rating'>
                            						<span class='upvote' onclick='updRatingArgument(this)' data-argument='$item->id' data-battle='$id' data-rating='$item->rating'>
                               							<span>▲</span>
                                						$item->rating
                            						</span>
                        						</div>
                        						<div class='comment-body thread-collapse-toggle'>
                            						<div class='text-body text-body-type-comment'>
                                						$item->text
                            						</div>
                        						</div>
                        						<span class='comment-footer-button' data-argument='$item->id' onclick='openCommentForm(this)'>
                        							<svg stroke='currentColor' fill='currentColor' stroke-width='0' viewBox='0 0 16 16' height='1em' width='1em' xmlns='http://www.w3.org/2000/svg'><path d='M5.921 11.9 1.353 8.62a.719.719 0 0 1 0-1.238L5.921 4.1A.716.716 0 0 1 7 4.719V6c1.5 0 6 0 7 8-2.5-4.5-7-4-7-4v1.281c0 .56-.606.898-1.079.62z'></path></svg>
                        							reply
                        						</span>
                    						</div>
                    						<div class='clearfix20'></div>

											<!--Form Comment -->
				                   			<form class='block-battle' style='display: none;' data-argument='$item->id' onsubmit='formSubmit(this);return false' method='post'>
                								<div class='formItem'>
                    								<input type='hidden' name='comment_battle_id' value='$item->id_item'/>
                    								<input type='hidden' name='comment_argument_id' value='$item->id'/>
                    								<label>Your name: </label>
                    								<input type='text' name='comment_author'/>
                								</div>
								                <div class='formItem'>
								                    <label>Text comment:</label>
								                    <textarea rows='4' name='comment_text'></textarea>
								                </div>
                								<input type='submit' value='Send' class='btn'/>
            								</form>
            								<!--Success -->
            								 <div class='block-battle window-success' data-argument='$item->id' style='display:none'>
                								<svg stroke='#0b9d4a' fill='#0b9d4a' stroke-width='0' viewBox='0 0 16 16' height='120px' width='120px' xmlns='http://www.w3.org/2000/svg'><path d='M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z'></path></svg>
				                                <h3>Success!</h3>
                                                <p>Your comment will be added after verification.</p>
											</div>
            								";

				foreach ( $firstComments as $comment ) {
					$comment_date        = date( "M d, Y", strtotime( $comment->comment_date ) );
					$avatar_comment      = explode( " ", $comment->comment_author );
					$avatar_name_comment = mb_substr( $avatar_comment[0], 0, 1 ) . mb_substr( $avatar_comment[1], 0, 1 );
					$out                 .= "
													<div class='replies replies-indent-normal'>
											            <div class='reply'>
											                <div class='reply-header'>
											                    <span class='avatar reply-avatar'>
											                    	<span class='sb-avatar'>$avatar_name_comment</span>
											                    </span>
											                    <span class='comment-header-author-name'>$comment->comment_author</span>
											                    <span class='reply-date'>$comment_date</span>
											                </div>
											                <div class='reply-rating' data-id='$comment->id'>
											                    <span class='upvote upvote-type-inline' data-argument='$comment->comment_argument_id' data-rating='$comment->comment_rating' data-comment='$comment->id' data-battle='$comment->comment_battle_id' onclick='updRatingComment(this)'>
											                    	<span>+</span>
											                    	$comment->comment_rating
											                    </span>
											                </div>
											                <div class='reply-body thread-collapse-toggle'>
											                    <div class='text-body text-body-type-comment'>
											                        $comment->comment_text
											                    </div>
											                </div>
											            </div>
											        </div>
												";
				}

				$out .= "</div>";
			} else {
				$out .= "<div data-argument='$item->id'>
 											<div class='clearfix'></div>
                    						<div class='battle-comment-prefix battle-comment-prefix-side-a'>
                        						for &laquo;$first&raquo;
                    						</div>
                    						<div class='block-battle comment comment-type-battle comment-type-battle-side-a'>
                        						<div class='comment-header'>
                            						<div class='comment-title'>$item->title</div>
                            						<div class='comment-type-battle-userinfo'>
                            							<span class='user user-tiny'>
                            								<span class='avatar user-avatar'>
                            									<span class='sb-avatar'>$avatar_name_arg</span>
                            								</span>
                                							<span class='user-name'>$item->username</span>
                            							</span>
                                						<span class='reply-date'>$date_args</span>
                            						</div>
                        						</div>
                        						<div class='comment-rating'>
                            						<span class='upvote' onclick='updRatingArgument(this)' data-argument='$item->id' data-battle='$id' data-rating='$item->rating'>
                               							<span>▲</span>
                                						$item->rating
                            						</span>
                        						</div>
                        						<div class='comment-body thread-collapse-toggle'>
                            						<div class='text-body text-body-type-comment'>
                                						$item->text
                            						</div>
                        						</div>
                        						<span class='comment-footer-button'  data-argument='$item->id' onclick='openCommentForm(this)'>
                        							<svg stroke='currentColor' fill='currentColor' stroke-width='0' viewBox='0 0 16 16' height='1em' width='1em' xmlns='http://www.w3.org/2000/svg'><path d='M5.921 11.9 1.353 8.62a.719.719 0 0 1 0-1.238L5.921 4.1A.716.716 0 0 1 7 4.719V6c1.5 0 6 0 7 8-2.5-4.5-7-4-7-4v1.281c0 .56-.606.898-1.079.62z'></path></svg>
                        							reply
                        						</span>
                    						</div>
                    						<div class='clearfix20'></div>

				                    		<!--Form Comment -->
				                   			<form class='block-battle' style='display: none;' data-argument='$item->id' onsubmit='formSubmit(this);return false' method='post'>
                								<div class='formItem'>
                    								<input type='hidden' name='comment_battle_id' value='$item->id_item'/>
                    								<input type='hidden' name='comment_argument_id' value='$item->id'/>
                    								<label>Your name: </label>
                    								<input type='text' name='comment_author'/>
                								</div>
								                <div class='formItem'>
								                    <label>Text comment:</label>
								                    <textarea rows='4' name='comment_text'></textarea>
								                </div>
                								<input type='submit' value='Send' class='btn'/>
            								</form>
            								<!--Success -->
            								 <div class='block-battle window-success' style='display:none' data-argument='$item->id'>
                								<svg stroke='#0b9d4a' fill='#0b9d4a' stroke-width='0' viewBox='0 0 16 16' height='120px' width='120px' xmlns='http://www.w3.org/2000/svg'><path d='M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z'></path></svg>
				                                <h3>Success!</h3>
                                                <p>Your comment will be added after verification.</p>
											</div>
            								";

				foreach ( $secondComments as $comment ) {
					$comment_date        = date( "M d, Y", strtotime( $comment->comment_date ) );
					$avatar_comment      = explode( " ", $comment->comment_author );
					$avatar_name_comment = mb_substr( $avatar_comment[0], 0, 1 ) . mb_substr( $avatar_comment[1], 0, 1 );
					$out                 .= "
																<div class='replies replies-indent-normal'>
														            <div class='reply'>
														                <div class='reply-header'>
														                    <span class='avatar reply-avatar'>
														                        <span class='sb-avatar'>$avatar_name_comment</span>
														                    </span>
														                    <span class='comment-header-author-name'>$comment->comment_author</span>
														                    <span class='reply-date'>$comment_date</span>
														                </div>
														                <div class='reply-rating'>
														                    <span class='upvote upvote-type-inline' data-argument='$comment->comment_argument_id' data-rating='$comment->comment_rating' data-comment='$comment->id' data-battle='$comment->comment_battle_id' onclick='updRatingComment(this)'>
														                        <span>+</span>
														                        $comment->comment_rating
														                    </span>
														                </div>
														                <div class='reply-body thread-collapse-toggle'>
														                    <div class='text-body text-body-type-comment'>
														                        $comment->comment_text
														                    </div>
														                </div>
														            </div>
														        </div>
															";
				}

				$out .= "</div>";
			}
		}
		$out .= "</div>
                	</div>
            	</div>

            	<form class='block-battle' data-battle='$id' onsubmit='formSubmitArg(this);return false' method='post'>
                	<div class='formItem'>
                    	<input type='hidden' name='id_item' value='$id'/>
                    	<label>Your name: </label>
                    	<input type='text' name='username'/>
                	</div>
                	<div class='formItem'>
	                    <label>I am for:</label>
	                    <select name='argument'>
	                        <option value='first'>$head_first_argument</option>
	                        <option value='second'>$head_second_argument</option>
	                    </select>
                	</div>
                	<div class='formItem'>
                    	<label>Briefly describe your argument</label>
                    	<input type='text' name='title'/>
                	</div>
                	<div class='formItem'>
                    	<label>Detailed response</label>
                    	<textarea rows='4' name='text'></textarea>
                	</div>
                	<input type='submit' value='Send' class='btn'/>
            	</form>
            	<!--Success -->
            	<div class='block-battle window-success' data-battle='$id' style='display:none'>
            		<svg stroke='#0b9d4a' fill='#0b9d4a' stroke-width='0' viewBox='0 0 16 16' height='120px' width='120px' xmlns='http://www.w3.org/2000/svg'><path d='M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z'></path></svg>
            		<h3>Success!</h3>
            		<p>Your argument will be added after verification.</p>
            	</div>
		 	</div>";

		return $out;

		return '<div class="asl-battle" id="' . $params['id'] . '"></div>';
	}


	public function asl_block_battle_render( $attr ) {
		$id = $attr['battleId'];

		return do_shortcode( '[asl-battle id="' . $id . '"]' );
	}

	public function register_block_battle() {
		wp_register_script(
			'asl-battle-block', plugin_dir_url( __FILE__ ) . '/block/build/js/asl-battle-block.js',
			array( 'wp-api-fetch', 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n' )
		);
		register_block_type( 'asl/asl-battle', [
			'api_version'     => 2,
			'editor_script'   => 'asl-battle-block',
			'render_callback' => array( $this, 'asl_block_battle_render' )
		] );
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Asl_Battle_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Asl_Battle_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/asl-battle-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Asl_Battle_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Asl_Battle_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( 'axios', 'https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js', [], $this->version, true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/main.js', [ 'axios' ], $this->version, true );


		wp_localize_script( $this->plugin_name, 'asl_battles_public', array(
			'asl_battles_rest_uri' => get_rest_url(),
			'nonce'                => wp_create_nonce( 'wp_rest' )
		) );

	}

}
