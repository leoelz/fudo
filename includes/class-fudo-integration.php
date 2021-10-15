<?php
/**
 * Fudo Integration.
 *
 * @package  Fudo
 * @category Integration
 */
if ( ! class_exists( 'Fudo_Integration' ) ) :
	class Fudo_Integration extends WC_Integration {
		/**
		 * Init and hook in the integration.
		 */
		public function __construct() {
			global $woocommerce;
			$this->id                 = 'fudo';
			$this->method_title       = __( 'Fudo', 'woocommerce-fudo-integration' );
			$this->method_description = __( 'Integración de Fudo a WooCommerce.', 'woocommerce-fudo-integration' );
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();
			// Define user set variables.
			$this->fudo_client_id     = $this->get_option( 'fudo_client_id' );
			$this->fudo_client_secret = $this->get_option( 'fudo_client_secret' );
			$this->fudo_login     = $this->get_option( 'fudo_login' );
			$this->fudo_password = $this->get_option( 'fudo_password' );
			// Actions.
			add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );
		}
		/**
		 * Initialize integration settings form fields.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'fudo_client_id' => array(
					'title'             => __( 'Client Id', 'woocommerce-fudo-integration' ),
					'type'              => 'text',
					'description'       => __( 'Enter with the Fudo Client Id. You can find this in Fudo Admin Panel, "Aplicaciones Externas".', 'woocommerce-fudo-integration' ),
					'desc_tip'          => true
				),
				'fudo_client_secret' => array(
					'title'             => __( 'Client Secret', 'woocommerce-fudo-integration' ),
					'type'              => 'text',
					'description'       => __( 'Enter with the Fudo Client Secret. You can find this in Fudo Admin Panel, "Aplicaciones Externas".', 'woocommerce-fudo-integration' ),
					'desc_tip'          => true
				),
				'fudo_login' => array(
					'title'             => __( 'User', 'woocommerce-fudo-integration' ),
					'type'              => 'text',
					'description'       => __( 'Enter with the Fudo User. You can find this in Fudo Admin Panel, "Usuarios".', 'woocommerce-fudo-integration' ),
					'desc_tip'          => true
				),
				'fudo_password' => array(
					'title'             => __( 'Password', 'woocommerce-fudo-integration' ),
					'type'              => 'text',
					'description'       => __( 'Enter with the Fudo Password. You can find this in Fudo Admin Panel, "Usuarios".', 'woocommerce-fudo-integration' ),
					'desc_tip'          => true
				),
			);
		}
		public function add_integration( $integrations ) {
			$integrations[] = 'Fudo_Integration';
			return $integrations;
		}
	}
endif;