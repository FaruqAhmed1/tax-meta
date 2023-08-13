<?php
/**
 * Plugin Name:       Tax Meta
 * Plugin URI:        
 * Description:      Extra Meta Box for Category and Tag Taxonomy
 * Version:           1.0.0
 * Author:            Faruq Ahmed
 * Author URI:       
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tax-meta
 * Domain Path:       /languages
 *
 */

class TaxMeta {

 function __construct(){

    add_action('plugin_loaded',array( $this,'taxm_plugin_text_domain' ));

    add_action( 'init',array($this,'taxm_bootstrap') );

    add_action('category_add_form_fields', array( $this,'taxm_category_form_fields' ) );

    add_action('post_tag_add_form_fields', array( $this,'taxm_category_form_fields' ) );

    add_action( 'create_category',array( $this,'taxm_save_category_field' ) );

    add_action( 'create_post_tag',array( $this,'taxm_save_category_field' ) );

    add_action('category_edit_form_fields', array( $this,'taxm_category_edit_form_fields' ) );

    add_action('post_tag_edit_form_fields', array( $this,'taxm_category_edit_form_fields' ) );

    add_action( 'edit_category',array( $this,'taxm_update_category_field' ) );

    add_action( 'edit_post_tag',array( $this,'taxm_update_category_field' ) );

    add_action('admin_menu',array( $this,'taxm_add_metabox' ));

    add_action( 'admin_enqueue_scripts', array( $this, 'taxm_admin_assets' ) );

    add_action( 'save_post',array( $this,'taxm_save_post' ) );
 }

 function taxm_admin_assets() {
      wp_enqueue_style( 'taxm-admin-style', plugin_dir_url( __FILE__ ) . "assets/admin/css/style.css", null, time() );

  }

function taxm_update_category_field($term_id){
    if( wp_verify_nonce($_POST['_wpnonce'],"update-tag_{$term_id}" )){  
        $exta_info = sanitize_text_field($_POST['extra-info']);   
        update_term_meta( $term_id,'taxm_extra_info',$exta_info);
       }  
}

function taxm_save_category_field ($term_id){
    if( wp_verify_nonce($_POST['_wpnonce_add-tag'],'add-tag') ){
          $exta_info = sanitize_text_field( $_POST['extra-info'] );   
          update_term_meta( $term_id,'taxm_extra_info',$exta_info );
    }  
}
function taxm_category_edit_form_fields( $term ){
    $exta_info = get_term_meta($term->term_id,'taxm_extra_info',true);
    ?>
    <tr class="form-field form-required term-name-wrap">
          <th scope="row">
                <label for="name"><?php _e('Extra Filed','tax-meta') ?></label>
          </th>
          <td>
          <input name="extra-info" id="extra-info" type="text" value="<?php echo esc_attr($exta_info) ?>" size="40" aria-required="true" aria-describedby="name-description">
          <p id="name-description"><?php _e('Some Helps Information','tax-meta') ?></p>
          </td>
    </tr>
    <?php
}

function taxm_category_form_fields(){
    ?>
    <div class="form-field form-required term-name-wrap">
          <label for="tag-name"><?php _e('Extra Field','tex-meta') ?></label>
          <input name="extra-info" id="extra-info" type="text" value="" size="40" aria-required="true" aria-describedby="name-description">
          <p id="name-description"><?php _e('Some Helps Information','tax-meta') ?></p>
    </div>
    <?php
}

function taxm_bootstrap(){
    $args = array(
          'type' =>'string',
          'sanitize_callback'=>'sanitize_text_field',
          'single'=> true,
          'description' => 'sample meta field for category tax',
          'show_in_rest'=> true
    );
    register_meta( 'term','taxm_extra_info',$args );
}
  
//  Post meta 

 function taxm_add_metabox(){
      add_meta_box(
            'omb_select_post',
            __( 'Select Post', 'tax-meta' ),
            array( $this, 'taxm_display_post' ),
            array( 'page' )
        );
 }

//   if ( ! function_exists( 'ptmf_is_secured' ) ) {
    function taxm_is_secured( $nonce_field, $action, $post_id  ){
        $nonce = isset( $_POST[ $nonce_field ] ) ? $_POST[ $nonce_field ] : '';
        if ( $nonce == '' ) {
			return false;
		}
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		if ( wp_is_post_autosave( $post_id ) ) {
			return false;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return false;
		}

		return true;

    }
	
// }

 function taxm_save_post( $post_id ){

    if( ! TaxMeta::taxm_is_secured( 'taxm_nonce_posts','taxm_posts',$post_id ) ){
        return $post_id;
    }

    $selected_posts_id = $_POST['taxm_post' ] ? $_POST['taxm_post' ]: '';
    $selected_term_id = $_POST['taxm_term'] ? $_POST['taxm_term'] : '';

    if( $selected_posts_id > 0 ){
        update_post_meta( $post_id,'taxm_selected_post', $selected_posts_id );
    }

    if( $selected_term_id > 0  ){
        update_post_meta( $post_id,'taxm_selected_term',$selected_term_id );
    }
    return $post_id;
 }

 function taxm_display_post( $post ){
        wp_nonce_field( 'taxm_posts','taxm_nonce_posts' );
        $label = esc_html__( 'Select Post','tax-meta' );
        $label2 = esc_html__( 'Select Term','tax-meta' );

        $selected_post_id = get_post_meta( $post->ID,'taxm_selected_post',true );
        $selected_term_id = get_post_meta( $post->ID,'taxm_selected_term',true );
        
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => -1,

        );
        $_post  = new WP_query( $args );
        $dropdown_list ='';
        while( $_post->have_posts() ){
            $extra  = '';
            $_post->the_post();
           
            if(get_the_ID() == $selected_post_id){
                $extra = 'selected';
            }

            $dropdown_list .= sprintf("<option %s value='%s'>%s</option>",$extra, get_the_ID(),get_the_title());
        }
        wp_reset_query();


        $_terms = get_terms( array(
            'taxonomy'=> 'category',
        ) );
        $term_dropdown_list = '';
        foreach( $_terms as $_term  ){
            $extra = '';
            if( $_term->term_id == $selected_term_id ){
                $extra = 'selected';
            }

            $term_dropdown_list .= sprintf("<option %s value='%s'>%s</option>",$extra, $_term->term_id, $_term->name);

        }

        $metabox_html = <<<EOD
        <div class="fields">
            <div class="field_c">
                <div class="label_c">
                    <label>{$label}</label>
                </div>
                <div class="input_c">
                    <select name="taxm_post" id="taxm_post">
                    <option value="0">{$label}</option>
                    {$dropdown_list}
                    </select>
                </div>
                <div class="float_c"></div>
            </div>


            <div class="field_c">
                <div class="label_c">
                    <label for="taxm_term">{$label2}</label>
                </div>
                <div class="input_c">
                    <select name="taxm_term" id="taxm_term">
                    <option value="0">{$label2}</option>
                    {$term_dropdown_list}
                    </select>
                </div>
                <div class="float_c"></div>
            </div>
            
        </div>
        EOD;

        echo $metabox_html;

 }

 function taxm_plugin_text_domain(){
    load_plugin_textdomain('tax-meta',false,dirname(__FILE__).'/languages');
 }

}

new TaxMeta();