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
    class Requires extends \UsabilityDynamics\Utility {

      /**
       * Library Server.
       *
       * @public
       * @property $server
       * @type {Object}
       */
      public static $server = '//cdn.udx.io/udx.requires.js';

      /**
       * Instance Settings.
       *
       * @public
       * @property $_settings
       * @type {Object}
       */
      private $_settings = array();

      public $_instances = array();

      /**
       * Constructor.
       *
       * args.path - relative path to home to serve data-main
       *
       * @todo Add output cleaning to remove any errors or warnigns.
       * @todo Add logic to not serve JS until template_redirect action to let JS deps register.
       * @todo Instane / settings should probably be based on scope since only a single requires.js instance can be handled per view.
       *
       * @param array $args
       *
       * @internal param array $_atts
       *
       * @internal param array|mixed $args .path
       */
      function __construct( $args = array() ) {

        // Save Instance.
        //self::$instance = &$this;

        if( did_action( 'template_redirect' ) ) {
          _doing_it_wrong( __FUNCTION__, sprintf( __( 'Requires called too late.' ) ) );
        }

        $args = self::parse_args( $args, array(
          'name'   => 'main',
          'type'   => 'model',
          'context'  => '_',
          'path'   => '/model/app',
          'base'   => '/',

          'data'  => array(),
          'config' => array(),

          'shim'   => array(),
          'deps'   => array(),

          'paths'  => array(
            'api'  => esc_url( admin_url( 'admin-ajax.php' ) ),
            'home'  => esc_url( home_url( '/' ) ),
            'login' => wp_login_url()
          ),
          'args'   => array(
            "access-token" => "test"
          ),

          'cache'  => '',
          'vary'   => '',
          'code' => 200
        ));

        // Create Stateless Settings.
        $this->_settings = new \UsabilityDynamics\Settings( array(
          "key" => $args->name
        ));

        // Set Passed Arguments.
        $this->set( $args );

        // Compute Values.
        $this->set( '_slug', self::create_slug( $args->name ? $args->name : str_replace( '.js', '', basename( $args->path || '/main.js' ) ), array( 'separator' => '-' ) ) );
        $this->set( '_path', ( $args->path ? $args->path : '/scripts/' . $this->name . '.js' ) );

        // Bind Actions.
        add_action( 'wp_footer', array( &$this, 'render_tag' ), 100 );
        add_action( 'admin_print_scripts', array( &$this, 'render_tag' ), 100 );
        add_action( 'customize_controls_print_scripts', array( &$this, 'render_tag' ), 100 );
        add_action( 'customize_controls_print_footer_scripts', array( &$this, 'render_tag' ), 100 );
        add_action( 'customize_preview_init', array( &$this, 'render_tag' ), 100 );
        add_action( 'login_enqueue_scripts', array( &$this, 'render_tag' ), 100 );

        // Serve Scripts.
        add_action( 'admin_init', array( &$this, '_serve_model' ) );
        add_action( 'template_redirect', array( &$this, '_serve_model' ) );

        // die( '<pre>' . print_r( $this->get(), true ) . '</pre>' );

        // @chainable.
        return $this;

      }

      /**
       * Add Arbitray Data
       *
       * @param string $key
       * @param null   $value
       *
       * @return null|\UsabilityDynamics\Settings
       * @internal param array $data
       */
      public function data( $key = '', $value = null ) {

        if( $key && $value ) {
          return $this->set( 'data' . '.' . $key, $value );
        }

        return $this->set( 'data', $key );

      }

      /**
       * General Admin Scripts.
       *
       * @todo The frontent preview is using incorrect action; "customize_preview_init" is triggered on body not in head.
       *
       * @action login_enqueue_scripts - Login page header scripts.
       * @action customize_controls_print_scripts - Customizer Interface scripts.
       * @action customize_controls_print_footer_scripts - Customizer Interface footer scripts.
       * @action admin_print_scripts - General administrative scripts.
       * @action wp_head - Frontend header scripts.
       * @action wp_footer - Frontend header scripts.
       */
      public function render_tag() {

        $scope = is_admin() ? 'private' : 'public';

        echo '<script data-scope="' . $scope . '" data-name="' . $this->get( 'name' ) . '" data-main="' . $this->get( '_path' ) . '" src="' . self::$server . '"></script>' . "\n";

        return;

        // Standard Admin.
        if( current_filter() == 'admin_print_scripts' && $this->backend ) {
          echo '<script data-scope="admin" data-name="' . $this->name . '" data-main="' . $this->path . '" src="' . $this->server . '"></script>' . "\n";
        }

        // Admin Customizer Controls.
        if( current_filter() == 'customize_controls_print_scripts' && $this->customizer ) {
          echo '<script data-scope="customizer" data-name="' . $this->name . '" data-main="' . $this->path . '" src="' . $this->server . '"></script>' . "\n";
        }

        // Login Scripts.
        if( current_filter() == 'login_enqueue_scripts' && $this->login ) {
          echo '<script data-scope="login" data-name="' . $this->name . '" data-main="' . $this->path . '" src="' . $this->server . '"></script>' . "\n";
        }

        // Public Frontend.
        if( current_filter() == 'wp_footer' && $this->public ) {
          echo '<script data-scope="public" data-name="' . $this->name . '" data-main="' . $this->path . '" src="' . $this->server . '"></script>' . "\n";
        }

        // Frontned Customization Preview.
        if( current_filter() == 'customize_preview_init' && $this->preview ) {
          echo '<script data-scope="preview" data-name="' . $this->name . '" data-main="' . $this->path . '" src="' . $this->server . '"></script>' . "\n";
        }

      }

      /**
       * Serve Scripts.
       *
       *  @todo add html_entity_decode() for data strings.
       *
       * @action template_redirect
       * @action admin_init
       */
      public function _serve_model() {

        if( isset( $_SERVER[ 'REDIRECT_URL' ] ) && $_SERVER[ 'REDIRECT_URL' ] === $this->get( '_path' ) ) {

          // Generate Action Handler.
          do_action( 'ud:requires', $this );

          // Instance Action Handler.
          do_action( 'ud:requires:' . $this->get( 'name' ) );

          //die( '<pre>' . print_r( $this->get(), true ) . '</pre>' );

          // Set Headers.
          add_filter( 'nocache_headers', function ( $headers ) {

            if( !$headers ) {
              $headers = array();
            }

            $this->set( '_headers', array(
              'Content-Type'    => 'application/javascript; charset=' . get_bloginfo( 'charset' ),
              'X-Frame-Options' => 'SAMEORIGIN',
              'Vary'            => 'Accept-Encoding'
            ));

            // $headers = apply_filters( 'ud:requires:headers', $headers );

            return $this->get( '_headers' );

          });

          // Standard Headers.
          nocache_headers();

          // WordPress will try to make it 404.
          http_response_code( $this->get( 'code', 200 ) );

          $data = apply_filters( 'ud:requires:config', array(
            'type'    => $this->get( 'type' ),
            'name'    => $this->get( 'name' ),
            'context' => $this->get( 'context' ),
            'baseUrl' => $this->get( 'base' ),
            'urlArgs' => $this->get( 'args' ),
            'paths'   => $this->get( 'paths' ),
            'config'  => $this->get( 'config' ),
            'data'    => $this->get( 'data' ),
            'deps'    => $this->get( 'deps' )
          ));

          self::send( array_filter( $data ) );

        }

      }

      public static function send( $data ) {
        die( 'define(' . json_encode( $data ) . ');' );
      }

      /**
       * Error Handler
       *
       * @param $errno
       * @param $errstr
       * @param $errfile
       * @param $errline
       *
       * @param $errfile
       *
       * @return bool
       */
      public static function error_handler( $errno = null, $errstr = '', $errfile = null, $errline = null ) {

        // This error code is not included in error_reporting
        if( !( error_reporting() & $errno ) ) {
          return;
        }

        switch( $errno ) {

          // Fatal
          case E_ERROR:
          case E_CORE_ERROR:
          case E_RECOVERABLE_ERROR:
          case E_COMPILE_ERROR:
          case E_USER_ERROR:
            wp_die( "<h1>Website Temporarily Unavailable</h1><p>We apologize for the inconvenience and will return shortly.</p>" );
            break;

          // Do Nothing
          case E_WARNING:
          case E_USER_NOTICE:
            return true;
            break;

          // No Idea.
          default:
            return;
            // wp_die( "<h1>Website Temporarily Unavailable</h1><p>We apologize for the inconvenience and will return shortly.</p>" );
            break;
        }

        return true;

      }

      /**
       * Get Setting.
       *
       * @method get
       *
       * @for Requires
       * @author potanin@UD
       * @since 0.1.1
       */
      public function get( $key, $default = null ) {
        return $this->_settings ? $this->_settings->get( $key, $default ) : null;
      }

      /**
       * Set Setting.
       *
       * @usage
       *
       * @method get
       * @for Requires
       *
       * @author potanin@UD
       * @since 0.1.1
       */
      public function set( $key, $value = null ) {
        return $this->_settings ? $this->_settings->set( $key, $value ) : null;
      }

      public static function get_instance() {
        return self::$instances;
      }

    }

  }

}