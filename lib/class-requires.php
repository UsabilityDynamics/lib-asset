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
          'name'  => false,
          'path'  => '/scripts/app.state.js',
          'version' => '1.0',
          'debug' => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? true : false
        ), $_atts );


        // Generate Name.
        if( !$args->name ) {
          $args->name = str_replace( '.js', '', basename( $args->path || 'scripts/app.state.js' ) );
        }

        if( !$args->path ) {
          $args->path = $args->name;
        }

        if( isset( $_SERVER[ 'REDIRECT_URL' ] ) && $_SERVER[ 'REDIRECT_URL' ] === $args->path ) {

          // Generate Action Handler.
          do_action( 'ud:requires', $args );

          // Instance Action Handler.
          do_action( 'ud:requires:' . $args->name );

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
            '/** ---- ' . $args->name . ' ----- */',
            'require.config(' . json_encode( (array) $_config, JSON_FORCE_OBJECT ) . ');',
            'require();'
          );

          if( $args->debug ) {
            $_output[] = 'console.log( "ud.requires", "' . $args->name .'");';
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