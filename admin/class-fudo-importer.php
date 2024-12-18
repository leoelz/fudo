<?php
/**
 * Fudo Importer.
 *
 * @package  Fudo
 * @category Importer
 */
/*

$fudo_category_fields = ["id","kitchenId","name","parentId","enableMobile","enableOnlineMenu","enableQrMenu","subcategories","taxes"];
$wc_category_fields = [];

$fudo_product_fields = ["id","code","proportions","kitchenId","image","favourite","minStock","name","price","cost","stock","stockControl","ignoreAvailability","active","iconName","description","providerId","productCategoryId","productTypeId","unitId","productProportions","comboGroups","enableOnlineMenu","enableQrMenu"];

$wc_product_fields = ['name','slug','date_created','date_modified','status','featured','catalog_visibility','description','short_description' ,'sku','price','regular_price','sale_price','date_on_sale_from','date_on_sale_to','total_sales','tax_status','tax_class','manage_stock','stock_quantity','stock_status','backorders','low_stock_amount','sold_individually','weight','length','width','height','upsell_ids', 'cross_sell_ids','parent_id','reviews_allowed','purchase_note','attributes','default_attributes','menu_order','post_password','virtual','downloadable','category_ids','tag_ids','shipping_class_id','downloads','image_id','gallery_image_ids','download_limit','download_expiry','rating_counts','average_rating','review_count'];

categorias separadas por coma, y si es subcategoría separadas con > (madre > hija)
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined('WC_ABSPATH')){
	define('WC_ABSPATH', plugin_dir_path( dirname( __FILE__ ) )  . '../woocommerce/');
}
/**
 * Include dependencies.
 */
if ( ! class_exists( 'WC_Product_Importer', false ) ) {
	include_once WC_ABSPATH . 'includes/import/abstract-wc-product-importer.php';
}

if ( ! class_exists( 'Fudo_Importer' ) ) :
	class Fudo_Importer extends WC_Product_Importer {

		/**
		 * Tracks current row being parsed.
		 *
		 * @var integer
		 */
		protected $api;

		/**
		 * Initialize importer.
		 *
		 * @param string $file   File to read.
		 * @param array  $params Arguments for the parser.
		 */
		public function __construct( $params = array() ) {
			$default_args = array(
				'mapping'          => array(), // Column mapping. fudo_heading => schema_heading.
				'parse'            => false, // Whether to sanitize and format data.
				'update_existing'  => true, // Whether to update existing items. // !!!!!!!! Primer Importación
				'prevent_timeouts' => true, // Check memory and time usage and abort if reaching limit.
				'escape'           => "\0", // PHP uses '\' as the default escape character. This is not RFC-4180 compliant. This disables the escape character.
			);

			$this->params = wp_parse_args( $params, $default_args );

			if ( isset( $this->params['mapping']['from'], $this->params['mapping']['to'] ) ) {
				$this->params['mapping'] = array_combine( $this->params['mapping']['from'], $this->params['mapping']['to'] );
			}

			$this->api = new Fudo_Client();

		}
		/**
		 * Parse a category fields
		 * Categories are separated by commas and subcategories are "parent > subcategory".
		 *
		 * @param string $value Field value.
		 *
		 * @return array of arrays with "parent" and "name" keys.
		 */
		public function parse_categories_field( $value ) {
			if ( empty( $value ) ) {
				return array();
			}

			$row_terms  = $this->explode_values( $value );
			$categories = array();

			foreach ( $row_terms as $row_term ) {
				$parent = null;
				$_terms = array_map( 'trim', explode( '>', $row_term ) );
				$total  = count( $_terms );

				foreach ( $_terms as $index => $_term ) {

					$term = wp_insert_term( $_term, 'product_cat', array( 'parent' => intval( $parent ) ) );

					if ( is_wp_error( $term ) ) {
						if ( $term->get_error_code() === 'term_exists' ) {
							// When term exists, error data should contain existing term id.
							$term_id = $term->get_error_data();
						} else {
							break; // We cannot continue on any other error.
						}
					} else {
						// New term.
						$term_id = $term['term_id'];
					}

					// Only requires assign the last category.
					if ( ( 1 + $index ) === $total ) {
						$categories[] = $term_id;
					} else {
						// Store parent to be able to insert or query categories based in parent ID.
						$parent = $term_id;
					}
				}
			}

			return $categories;
		}

		public function get_category_name($categories, $category_id){
			if($categories[$category_id]["parentId"] != "") {
				return $this->get_category_name($categories, $categories[$category_id]["parentId"])." > ".$categories[$category_id]["name"];
			}
			return $categories[$category_id]["name"];
		}

		protected function wc_get_product_id_by_fudo_id($fudo_id){
			$meta_query_args = [
				'post_type'=>'product',
				'meta_key' => 'fudo_id',
				'meta_query'=>[
					[
						'key'     => 'fudo_id',
						'value'   => $fudo_id,
						'compare' => '='
					]
				]
			];
			$meta_query = new WP_Query( $meta_query_args );
			return $meta_query->post->ID;
		}
		protected function parse_combo_group($products){
			$product_ids=[];
			for($i=0,$n=count($products);$i<$n;$i++){
				$product_ids[]=$products[$i]['productId'];
			}
			return $product_ids;
		}
		/**
		 * Process importer.
		 *
		 * Do not import products with IDs or SKUs that already exist if option
		 * update existing is false, and likewise, if updating products, do not
		 * process rows which do not exist if an ID/SKU is provided.
		 *
		 * @return array
		 */
		public function import() {
			$this->start_time = time();
			$index            = 0;
			$update_existing  = $this->params['update_existing'];
			$data             = array(
				'imported' => array(),
				'failed'   => array(),
				'updated'  => array(),
				'skipped'  => array(),
			);

			$categories = json_decode($this->api->get_categories(), true);
			$products = json_decode($this->api->get_products(), true);
			$stock = json_decode($this->api->get_stock(), true);

			foreach($products as $fudo_product_id => $product_data2){
				$product_data=[];//['id'=>'','sku'=>'','name'=>'','slug'=>'','date_created'=>'','date_modified'=>'','status'=>'','featured'=>'','catalog_visibility'=>'','description'=>'','short_description'=>'' ,'sku'=>'','price'=>'','regular_price'=>'','sale_price'=>'','date_on_sale_from'=>'','date_on_sale_to'=>'','total_sales'=>'','tax_status'=>'','tax_class'=>'','manage_stock'=>'','stock_quantity'=>'','stock_status'=>'','backorders'=>'','low_stock_amount'=>'','sold_individually'=>'','weight'=>'','length'=>'','width'=>'','height'=>'','upsell_ids'=>'', 'cross_sell_ids'=>'','parent_id'=>'','reviews_allowed'=>'','purchase_note'=>'','attributes'=>'','default_attributes'=>'','menu_order'=>'','post_password'=>'','virtual'=>'','downloadable'=>'','category_ids'=>'','tag_ids'=>'','shipping_class_id'=>'','downloads'=>'','image_id'=>'','gallery_image_ids'=>'','download_limit'=>'','download_expiry'=>'','rating_counts'=>'','average_rating'=>'','review_count'=>''];
				$category = $this->get_category_name($categories, $product_data2["productCategoryId"]);
				//$product_data["id"]=
				$product_data["sku"]=$product_data2['code'];
				$product_data["name"]=$product_data2["name"];
				$product_data["regular_price"]=$product_data2["price"];
				$product_data["featured"]=$product_data2["favourite"];
				$product_data["low_stock_amount"]=$product_data2["minStock"];
				$product_data["manage_stock"]=$product_data2["stockControl"];
				$product_data["stock_quantity"]=$stock[$fudo_product_id]["stock"]==null?$stock[$fudo_product_id]["availability"]:$stock[$fudo_product_id]["stock"];
				$product_data["cross_sell_ids"]=$this->parse_combo_group($product_data2["comboGroups"]);
				$product_data["type"]=count($product_data["cross_sell_ids"])>0?"grouped":"simple";
				$product_data["stock_status"] = ($stock[$fudo_product_id]["stock"]==null?$stock[$fudo_product_id]["availability"]:$stock[$fudo_product_id]["stock"]) > 0;
				$product_data["category_ids"]=$this->parse_categories_field($category);
				$product_data["meta_data"]=[["key"=>"fudo_id","value"=>$fudo_product_id]];

				do_action( 'woocommerce_product_import_before_import', $product_data );
				$id = $this->wc_get_product_id_by_fudo_id($fudo_product_id);
				if ( $id ) {
					$product_data["id"]=$id;
					$product   = wc_get_product( $id );
					$product_data["description"]=$product->get_description('db');
					$product_data["short_description"]=$product->get_short_description('db');
					$product_data["catalog_visibility"]=$product->get_catalog_visibility();//"enableOnlineMenu",
				}else{
					$product_data["description"]=$product_data2["description"];
					$product_data["short_description"]=$product_data2["description"];
					$product_data["catalog_visibility"]=$product_data2["active"]?"visible":"hidden";//"enableOnlineMenu","enableQrMenu"
				}

				$result = $this->process_item( $product_data );

				if ( is_wp_error( $result ) ) {
					$result->add_data( array( 'id' => $id, 'product_data'=>$product_data ) );
					$data['failed'][] = $result;
				} elseif ( $result['updated'] ) {
					$data['updated'][] = $result['id'];
				} else {
					$data['imported'][] = $result['id'];
				}

			}

			return $data;
		}
        public function remove_old( $hours = 24 ) {
            $deleted = [];
            $query_args = [
                'post_type'=>'product',
                'post_status'=>'publish',
                'date_query'=> [
                    'column' => 'post_modified',
                    'before' => $hours . ' hours ago'
                ]
            ];
            $query = new WP_Query( $query_args );
            while ( $query->have_posts() ) {
                $query->the_post();
                $post = get_post();
                wp_update_post([
                    'ID'=>$post->ID,
                    'post_status'=>'private'
                ]);
                $deleted[] = $post->ID;
            }
            return $deleted;
        }
	}
endif;