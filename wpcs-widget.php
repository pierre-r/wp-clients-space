<?php

add_action('init', 'wpcs_manage_connection');
add_action('widgets_init', 'wpcs_widget_init');

function wpcs_manage_connection() {
    if (!defined('WP_ADMIN')) {
        global $wpcs_connection_error;
        if (!session_id()) {
            session_start();
        }
        $wpdb = $GLOBALS['wpdb'];
        $wpcs_connection_error = "";
        // CONNECTION
        if (isset($_POST['action']) && $_POST['action'] == 'wpcs_connection') {
            $post_id_from_email = $wpdb->get_row($wpdb->prepare("SELECT post_id FROM " . $wpdb->prefix . "postmeta WHERE meta_key = '_emailclient' AND meta_value = %s", $_POST['wpcs_email']))->post_id;
            $post_id_from_password = $wpdb->get_row($wpdb->prepare("SELECT post_id FROM " . $wpdb->prefix . "postmeta WHERE post_id = '%d' AND meta_key = '_motsdepasse' AND meta_value = %s", $post_id_from_email, $_POST['wpcs_password']))->post_id;
            if (!empty($post_id_from_email) && $post_id_from_email == $post_id_from_password) {
                $_SESSION['wpcs'] = get_page($post_id_from_email);
                header("Location: " . get_permalink($_SESSION['wpcs']->ID));
                exit;
            } else {
                $wpcs_connection_error = __('Incorrect email or password.', 'wpcs');
            }
        }
        // DECONNECTION
        // User connected but on different client space
        if ($_SESSION['wpcs']->ID > 0 && preg_match('/\/customers\/.+\/$/i', $_SERVER['REQUEST_URI']) && !preg_match('/\/customers\/' . $_SESSION['wpcs']->post_name . '\/$/i', $_SERVER['REQUEST_URI'])) {
            header("Location: " . get_permalink($_SESSION['wpcs']->ID));
            exit;
        }
        // User not connected
        $force_logout = (preg_match('/\/customers\//i', $_SERVER['REQUEST_URI']) && !$_SESSION['wpcs']->ID) ? 1 : 0;
        if (isset($_REQUEST['logout']) || $force_logout) {
            $_SESSION['wpcs'] = array();
            header('Location: ' . get_bloginfo('url'));
            exit;
        }
    }
}

function wpcs_widget_init() {
    register_widget('wpcs_widget');
}

function wpcs_login_form() {
    global $wpcs_connection_error;
    $output = "";
    if ($wpcs_connection_error) {
        $output .= '<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">&times;</a>' . $wpcs_connection_error . '</div>';
    }
    $output .= '<form method="post" action="" class="">
                <div class="control-group">
                    <label class="control-label">' . __('Email') . '</label>
                    <div class="controls">
                        <input type="text" name="wpcs_email" value="' . $_POST['wpcs_email'] . '" style="width: 94%;" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">' . __('Password') . '</label>
                    <div class="controls">
                        <input type="password" name="wpcs_password" value="' . $_POST['wpcs_password'] . '" style="width: 94%;" />
                    </div>
                </div>
                <div class="control-group">
                    <input type="hidden" name="action" value="wpcs_connection" />
                    <button type="submit" class="btn btn-lightblue">' . __('Log In') . '</button>
                </div>
            </form>';
    return $output;
}

class wpcs_widget extends WP_Widget {

    public function __construct() {
        $widget_options = array(
            'classname' => 'wpcs-widget',
            'description' => __('Widget to show a login form for your customers in your sidebar.', 'wpcs'),
        );
        //$this->WP_Widget('wpcs-widget', __('WP Clients Space', 'wpcs'), $widget_options);
        parent::__construct('wpcs-widget', __('WP Clients Space', 'wpcs'), $widget_options);
    }

    public function widget($args, $instance) {
        if ($instance['show_on_front_page'] || !is_front_page()) {
            extract($args);
            $output = "";
            $output .= $before_widget;
            $output .= $before_title . __('Customer Area', 'wpcs') . $after_title;
            if ($_SESSION['wpcs']) {
                $output .= '<ul>
                            <li><a href="' . get_permalink($_SESSION['wpcs']->ID) . '">' . __('Your customer area', 'wpcs') . '</a></li>
                            <li><a href="?logout=1">' . __('Log Out') . '</a></li>
                        </ul>
                    ';
            } else {
                $output .= wpcs_login_form();
            }
            $output .= $after_widget;
            echo $output;
        }
    }

    public function form($instance) {
        $default = array(
            //'title' => 'Client space',
            'show_on_front_page' => 1,
        );
        $instance = wp_parse_args($instance, $default);
        $show_on_front_page = ($instance['show_on_front_page']) ? ' checked="checked"' : '';
        //echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title') . '</label> <input type="text" name="' . $this->get_field_name('title') . '" value="' . $instance['title'] . '" id="' . $this->get_field_id('title') . '" /></p>';
        echo '<p><input type="checkbox" name="' . $this->get_field_name('show_on_front_page') . '" value="1"' . $show_on_front_page . ' id="' . $this->get_field_id('show_on_front_page') . '" /> <label for="' . $this->get_field_id('show_on_front_page') . '">' . __('Show on front page', 'wpcs') . '</label></p>';
    }

    public function update($new_instance, $old_instance) {
        if (!isset($new_instance['show_on_front_page'])) {
            $new_instance['show_on_front_page'] = 0;
        }
        return $new_instance;
    }

}

?>
