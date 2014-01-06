<?php
/**
 * AMD Require Handler
 *
 * ### Actions
 *
 * - ud:requires
 * - ud:requires:{name}
 *
 * ### Filters
 *
 * - ud:requires:headers
 * - ud:requires:config
 *
 * @author potanin@UD
 * @version 0.1.0
 * @module UsabilityDynamics
 */
namespace UsabilityDynamics {

  if( !class_exists( 'UsabilityDynamics\Requires' ) ) {

    /**
     * Class Requires
     *
     * @class Requires
     */
    class Requires {

      /**
       * Instance Name.
       *
       * @public
       * @property $name
       * @type {Object}
       */
      public $name = null;

      /**
       * Instance Path.
       *
       * @public
       * @property $path
       * @type {Object}
       */
      public $path = null;

      /**
       * Library Server.
       *
       * @public
       * @property $server
       * @type {Object}
       */
      public $server = '//cdn.udx.io/require.js';

      /**
       * Constructor.
       *
       * args.path - relative path to home to serve data-main
       *
       * @todo Add output cleaning to remove any errors or warnigns.
       * @todo Add logic to not serve JS until template_redirect action to let JS deps register.
       *
       * @param array $_atts
       * @internal param array|mixed $args .path
       */
      function __construct( $_atts = array() ) {

        $args = (object) shortcode_atts( array(
          'name'  => 'app.state',
          'path'  => '/scripts/',
          'scope'  => [ 'public' ],
          'debug' => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? true : false
        ), $_atts );

        // Force Array.
        $this->scope = (array) $args->scope;

        // Set Instance Properties.
        $this->name = $args->name  ? $args->name : str_replace( '.js', '', basename( $args->path || '/app.state.js' ) );
        $this->path = ( $args->path ? $args->path : $args->name ) . $this->name . '.js' ;
        $this->debug = $args->debug ?  $args->debug : false;

        if( in_array( 'public', $this->scope ) ) {
          $this->public = true;
        }

        if( in_array( 'preview', $this->scope ) ) {
          $this->preview = true;
        }

        if( in_array( 'backend', $this->scope ) ) {
          $this->backend = true;
        }

        if( in_array( 'login', $this->scope ) ) {
          $this->login = true;
        }

        if( in_array( 'customizer', $this->scope ) ) {
          $this->customizer = true;
        }

        // die( '<pre>' . print_r( $this, true ) . '</pre>' );

        // Bind Actions.
        add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );

        add_action( 'wp_head', array( &$this, '_render_tag' ), 100 );
        add_action( 'admin_print_scripts', array( &$this, '_render_tag' ), 100 );
        add_action( 'customize_controls_print_scripts', array( &$this, '_render_tag' ), 100 );
        add_action( 'customize_preview_init', array( &$this, '_render_tag' ), 100 );
        add_action( 'login_enqueue_scripts', array( &$this, '_render_tag' ), 100 );

        // Serve Scripts.
        add_action( 'admin_init', array( &$this, '_render_scripts' ) );
        add_action( 'template_redirect', array( &$this, '_render_scripts' ) );

        // Footer Script Loadering.
        // add_action( 'wp_footer', array( &$this, '_render_tag' ) );
        // add_action( 'customize_controls_print_footer_scripts', array( &$this, '_render_tag' ) );

      }

      /**
       * General Admin Scripts.
       *
       * @todo The frontent preview is using incorrect action; "customize_preview_init" is triggered on body not in head.
       *
       * @action login_enqueue_scripts - Login page header scripts.
       * @action customize_controls_print_scripts - Customizer Interface Scripts.
       * @action admin_print_scripts - Customizer Interface Scripts.
       * @action wp_head - Frontend header scripts.
       * @action wp_footer - Frontend header scripts.
       */
      public function _render_tag() {

        // Standard Admin.
        if( current_filter() == 'admin_print_scripts' && $this->backend ) {
          echo '<script data-scope="admin" id="' . $this->name . '" data-main="' . $this->path . '" src="' . $this->server . '"></script>' . "\n";
        }

        // Admin Customizer Controls.
        if( current_filter() == 'customize_controls_print_scripts' && $this->customizer ) {
          echo '<script data-scope="customizer" id="' . $this->name . '" data-main="' . $this->path . '" src="' . $this->server . '"></script>' . "\n";
        }

        // Login Scripts.
        if( current_filter() == 'login_enqueue_scripts' && $this->login ) {
          echo '<script data-scope="login" id="' . $this->name . '" data-main="' . $this->path . '" src="' . $this->server . '"></script>' . "\n";
        }

        // Public Frontend.
        if( current_filter() == 'wp_head' && $this->public ) {
          echo '<script data-scope="public" id="' . $this->name . '" data-main="' . $this->path . '" src="' . $this->server . '"></script>' . "\n";
        }

        // Frontned Customization Preview.
        if( current_filter() == 'customize_preview_init' && $this->preview ) {
          echo '<script data-scope="preview" id="' . $this->name . '" data-main="' . $this->path . '" src="' . $this->server . '"></script>' . "\n";
        }

      }

      /**
       * JavaScript in Preview Scripts.
       *
       */
      public function customize_preview_init() {

      }

      /**
       * Frontned Scripts.
       *
       */
      public function wp_enqueue_scripts() {

      }

      /**
       * Serve Scripts.
       *
       * @action template_redirect
       * @action admin_init
       */
      function _render_scripts() {

        if( isset( $_SERVER[ 'REDIRECT_URL' ] ) && $_SERVER[ 'REDIRECT_URL' ] === $this->path ) {

          // Generate Action Handler.
          do_action( 'ud:requires', $this );

          // Instance Action Handler.
          do_action( 'ud:requires:' . $this->name );

          // Set Headers.
          add_filter( 'nocache_headers', function( $headers ) {

            if( !$headers ) {
              $headers = array();
            }

            // JavaScript Asset.
            $headers[ 'Content-Type' ] = 'application/javascript; charset=' . get_bloginfo( 'charset' );

            // limit rendering of pages to same origin iframes
            $headers[ 'X-Frame-Options' ] = 'SAMEORIGIN';

            // Varnish Support.
            $headers[ 'Vary' ] = 'Accept-Encoding';

            $headers = apply_filters( 'ud:requires:headers', $headers );

            return $headers;

          });

          // Standard Headers.
          nocache_headers();

          // WordPress will try to make it 404.
          http_response_code( 200 );

          $_config = apply_filters( 'ud:requires:config', array(
            'paths' => array(
              'ajax' => esc_url( admin_url( 'admin-ajax.php' ) ),
              'home' => esc_url( home_url( '/' ) ),
              'login' => wp_login_url(),
              "jquery" => ""
            ),
            'browser'  => array(
              'mobile' => wp_is_mobile(),
              'ios' => wp_is_mobile() && preg_match( '/iPad|iPod|iPhone/', $_SERVER['HTTP_USER_AGENT'] ),
            )
          ));

          $_output = array(
            '/** ---- ' . $this->name . ' ----- */',
            'require.config(' . json_encode( (array) $_config, JSON_FORCE_OBJECT ) . ');',
            'require();'
          );

          if( $this->debug ) {
            $_output[] = 'console.log( "ud.requires", "' . $this->name .'");';
            $_output[] = 'console.log( "ud.requires.config", ' . json_encode( (array) $_config, JSON_FORCE_OBJECT ) . ');';
          }

          do_action( 'ud:requires:output', $_output );

          // Clean array and uutput JavaScript.
          die( implode( "\n", array_filter( (array) $_output ) ) );

        }

      }

    }

  }

}