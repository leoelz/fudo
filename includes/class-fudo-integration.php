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
			$this->fudo_use_api     = $this->get_option( 'fudo_use_api' , true );
			$this->fudo_use_staging = $this->get_option( 'fudo_use_staging', false );
			$this->fudo_import_interval_minutes = $this->get_option( 'fudo_import_interval_minutes', false );
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
					'description'       => __( 'If you don\'t have access to API. Enter the Fudo User. You can find this in Fudo Admin Panel, "Usuarios".', 'woocommerce-fudo-integration' ),
					'desc_tip'          => true
				),
				'fudo_password' => array(
					'title'             => __( 'Password', 'woocommerce-fudo-integration' ),
					'type'              => 'text',
					'description'       => __( 'If you don\'t have access to API. Enter the Fudo Password. You can find this in Fudo Admin Panel, "Usuarios".', 'woocommerce-fudo-integration' ),
					'desc_tip'          => true
				),
				'fudo_use_api' => array(
					'title'             => __( 'Use API', 'woocommerce-fudo-integration' ),
					'type'              => 'checkbox',
					'description'       => __( 'Disable if you have no access to API yet', 'woocommerce-fudo-integration' ),
					'desc_tip'          => true
				),
				'fudo_use_staging' => array(
					'title'             => __( 'Use Staging', 'woocommerce-fudo-integration' ),
					'type'              => 'checkbox',
					'description'       => __( 'Only if you have access to API', 'woocommerce-fudo-integration' ),
					'desc_tip'          => true
				),
					'fudo_import_interval_minutes' => array(
					'title'             => __( 'Import Interval', 'woocommerce-fudo-integration' ),
					'type'              => 'text',
					'description'       => __( 'Minutes interval to run the product importation from fudo', 'woocommerce-fudo-integration' ),
					'desc_tip'          => true
				)
			);
		}
		public function add_integration( $integrations ) {
			$integrations[] = 'Fudo_Integration';
			return $integrations;
		}

		public function process_admin_options() {
			parent::process_admin_options();
			if ( false !== as_has_scheduled_action( 'fudo_products_importation' ) ) {
				as_unschedule_action('fudo_products_importation');
			}
		}
	}
endif;