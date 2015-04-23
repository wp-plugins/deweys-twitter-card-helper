<?php
/**
 * Dewey's Twitter Card Helper
 *
 * @package   WADTwitterCards
 * @author    Luke DeWitt <dewey@whatadewitt.com>
 * @license   GPL-2.0+
 * @link      http://www.whatadewitt.ca
 * @copyright 2014 Luke DeWitt
 */

/**
 * Plugin class.
 *
 * @package WADTwitterCards
 * @author  Luke DeWitt <dewey@whatadewitt.com>
 */
class WADTwitterCards {

  /**
   * Plugin version, used for cache-busting of style and script file references.
   *
   * @since   1.0.0
   *
   * @var     string
   */
  protected $version = '1.0.0';

  /**
   * Unique identifier for your plugin.
   *
   * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
   * match the Text Domain file header in the main plugin file.
   *
   * @since    1.0.0
   *
   * @var      string
   */
  protected $plugin_slug = 'wad_twitter_cards';

  /**
   * Instance of this class.
   *
   * @since    1.0.0
   *
   * @var      object
   */
  protected static $instance = null;

  /**
   * Slug of the plugin screen.
   *
   * @since    1.0.0
   *
   * @var      string
   */
  protected $plugin_screen_hook_suffix = null;

  /**
   * Initialize the plugin by setting localization, filters, and administration functions.
   *
   * @since     1.0.0
   */
  private function __construct() {

    // Load plugin text domain
    add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

    // Twitter Cards
    add_action( 'wp_head', array( $this, 'generate_twitter_card' ) );
  }

  /**
   * Return an instance of this class.
   *
   * @since     1.0.0
   *
   * @return    object    A single instance of this class.
   */
  public static function get_instance() {

    // If the single instance hasn't been set, set it now.
    if ( null == self::$instance ) {
      self::$instance = new self;
    }

    return self::$instance;
  }

  /**
   * Load the plugin text domain for translation.
   *
   * @since    1.0.0
   */
  public function load_plugin_textdomain() {

    $domain = $this->plugin_slug;
    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
  }

  /**
   * Print the twitter: meta tags to the page head
   *
   * @since    1.0.0
   */
  public function generate_twitter_card() {
    global $post;
    if ( is_singular() ) {
      if (post_type_supports( $post->post_type, 'twitter_cards' ) ) {

        //Define defaults
        $tags = array();
        $tags['twitter:card'] = 'summary';
        $tags['twitter:site'] = '@undefined'; // YO! This should definitely be updated with the twitter_cards filter
        $tags['twitter:title'] = get_bloginfo( 'name' );
        $tags['twitter:url'] = get_permalink( get_the_ID() );
        $tags['twitter:description'] = get_the_excerpt();

        //Check for a post thumbnail.
        if ( current_theme_supports('post-thumbnails') && has_post_thumbnail( $post->ID ) ) {
          $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium', false);
          $tags['twitter:image'] = $thumbnail[0];
        }
      }
    } else if ( is_front_page() ) {
      $tags['twitter:card'] = get_bloginfo( 'name' );
      $tags['twitter:site'] = get_bloginfo( 'name' );
      $tags['twitter:creator'] = 'website';
      $tags['twitter:url'] = get_bloginfo( 'url' );
      $tags['twitter:description'] = get_bloginfo( 'description' );
    }

    $tags = apply_filters( 'twitter_cards', $tags );
    $tags = apply_filters( "{$post->post_type}_twitter_cards", $tags );

    //filter post tags
    foreach ( $tags as $key => $value ) {
      echo "<meta name='" . $key . "' content='" . $value . "' />\n";
    }
  }
}
