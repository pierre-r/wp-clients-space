<?php

function wpcs_init() {
    // tableau des labels pour le custom post type
    $labels = array(
        'name' => __('Clients space', 'wpcs'),
        'singular_name' => __('Client space', 'wpcs'),
        'add_new' => __('Add customer', 'wpcs'),
        'add_new_item' => __('Add new customer', 'wpcs'),
        'edit_item' => __('Edit customer', 'wpcs'),
        'new_item' => __('New customer', 'wpcs'),
        'view_item' => __('View customer', 'wpcs'),
        'search_items' => __('Search customer', 'wpcs'),
        'not_found' => __('No customer', 'wpcs'),
        'not_found_in_trash' => __('No customer in trash', 'wpcs'),
        'parent_item_colon' => '',
        'menu_name' => __('Clients space', 'wpcs')
    );

    // enregistrement du custom post type
    register_post_type('customers', array(
        'public' => true,
        'labels' => $labels,
        'menu_position' => 9,
        'capability_type' => 'post',
        'supports' => array('title', 'editor', 'thumbnail'),
    ));
    //ajout d'une taille d'image pour le custom post type
    add_image_size('avatar_client', 150, 150, true);
}

// permet de gerer les metabox
function customers_metaboxes() {
    add_meta_box('infoclient', __('Customer login informations', 'wpcs'), 'customers_metabox', 'customers', 'side', 'low');
}

// metabox pour le mot de passe du client
function customers_metabox($object) {
    //generation d'un token pour le formulaire pour éviter les hacks
    wp_nonce_field('customers', 'customers_nonce');
    wp_nonce_field('customers', 'email_customers_nonce');
    ?>
    <div class="meta-box-item-title">
        <h4><?php _e('Customer email', 'wpcs'); ?></h4>
        <div class="meta-box-item-content">
            <input type="text" name="customers_email" value="<?php echo esc_attr(get_post_meta($object->ID, '_emailclient', true)); ?>" style="width: 100%;" />
        </div>
    </div>
    <div class="meta-box-item-title">
        <h4><?php _e('Customer password', 'wpcs'); ?></h4>
        <div class="meta-box-item-content">
            <input type="text" name="customers_mdp" value="<?php echo esc_attr(get_post_meta($object->ID, '_motsdepasse', true)); ?>" style="width: 100%;" />
        </div>
        <!--<br />
        <div class="meta-box-item-content">
                <a href="<?php echo $_SERVER["REQUEST_URI"]; ?>&alertmyclient=1" title="Alerter le client">Alerter le client</a>
        </div>-->
    </div>
    <?php
}

function customers_savepost($post_id, $post) {
    $wpdb = $GLOBALS['wpdb'];
    //print_r($_POST);
    if (!isset($_POST['customers_mdp']) || !wp_verify_nonce($_POST['customers_nonce'], 'customers') || !isset($_POST['customers_email']) || !wp_verify_nonce($_POST['email_customers_nonce'], 'customers')) {
        return $post_id;
    }
    // verification du role d'administrateur qui modifie l'article
    $type = get_post_type_object($post->post_type);
    if (!current_user_can($type->cap->edit_post)) {
        return $post_id;
    }
    // Update custom post types
    update_post_meta($post_id, '_motsdepasse', $_POST['customers_mdp']);
    update_post_meta($post_id, '_emailclient', $_POST['customers_email']);
}

/**
 * Suppression d'un client
 */
function wpcs_deleteclient($post_ID) {
    return wp_delete_post($post_ID);
}

function alert_client($login_client) {
    $wpdb = $GLOBALS['wpdb'];
    $query = $wpdb->prepare("SELECT email_client FROM " . $wpdb->prefix . "clients WHERE login_client = '%s'", $login_client);
    $result = $wpdb->get_results($query);
    $result = $result[0];
    //echo $result->email_client;
    // TODO mail client
    $urlredirect = get_bloginfo('wpurl');
    header("Location: " . $urlredirect . "/wp-admin/post.php?post=" . $_GET['post'] . "&action=edit", TRUE, 301);
    exit();
}

//alert client 

if (isset($_REQUEST['alertmyclient'])) {
    alert_client($_SESSION['client']['login']);
}