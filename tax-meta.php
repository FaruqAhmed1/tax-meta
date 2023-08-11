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
 }

  function taxm_update_category_field($term_id){
      if(wp_verify_nonce($_POST['_wpnonce'],"update-tag_{$term_id}")){  
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

 function taxm_plugin_text_domain(){
    load_plugin_textdomain('tax-meta',false,dirname(__FILE__).'/languages');
 }

}

new TaxMeta();