<?php
/*
Plugin Name: My Custom Contact Form
Description: A simple plugin that adds a customizable contact form to a post or page via a shortcode.
Author: Olgun Özoktaş
Version: 1.0
*/

require_once 'installation.php';
require_once 'submissions.php';

/**
 * In every request those hooks must be registered
 * Because WordPress stores registered hooks in memory, not in the database
 */
register_activation_hook(__FILE__, 'mccf_install');
register_uninstall_hook(__FILE__, 'mccf_uninstall');

function mccf_contact_form()
{
    $content = '';

//    if (get_transient('form_submitted') == 'success') {
//        echo '<p>Thank you for your submission!</p>';
//        delete_transient('form_submitted');
//    }

    $content .= '<form method="post" action="" class="mccf_form">';
    $content .= '<input type="hidden" name="submit_form" value="1">';
    $content .= '<input type="text" name="full_name" placeholder="Your Name" id="mccf_full_name" class="mccf_input">';
    $content .= '<input type="email" name="email_address" placeholder="Your Email" id="mccf_email_address" class="mccf_input">';
    $content .= '<textarea placeholder="Your Message" name="message" id="mccf_message" class="mccf_textarea"></textarea>';
    $content .= '<input type="submit" name="submit_form" value="' . get_option('mccf_button_text') . '" id="mccf_submit_form" class="mccf_button">';
    $content .= '</form>';

    return $content;
}

add_shortcode('mccf_contact_form', 'mccf_contact_form');

function mccf_enqueue_scripts()
{
    wp_enqueue_script('mccf-form-submit', plugin_dir_url(__FILE__) . 'mccf-form-submit.js', array('jquery'), '1.0', true);
    wp_localize_script('mccf-form-submit', 'form_submit', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mccf_form_nonce')
    ));
}

add_action('wp_enqueue_scripts', 'mccf_enqueue_scripts');

function mccf_process_form()
{
    check_ajax_referer('mccf_form_nonce', 'nonce');
    global $wpdb;

    parse_str($_POST['data'], $form_data);

    if (isset($form_data['submit_form'])) {
        $table_name = $wpdb->prefix . 'mccf_submissions';

        $wpdb->insert(
            $table_name,
            array(
                'time' => current_time('mysql'),
                'name' => sanitize_text_field($form_data['full_name']),
                'email' => sanitize_email($form_data['email_address']),
                'message' => sanitize_textarea_field($form_data['message']),
            )
        );

        $to = get_option('admin_email');
        $subject = get_option('mccf_email_subject');
        $headers = 'From: ' . $form_data['full_name'] . ' <' . $form_data['email_address'] . '>' . "\r\n";

        wp_mail($to, $subject, $form_data['message'], $headers);

        echo 'success';
    }
    wp_die();
}

add_action('wp_ajax_mccf_process_form', 'mccf_process_form');
add_action('wp_ajax_nopriv_mccf_process_form', 'mccf_process_form');


//function mccf_capture_form()
//{
//    global $wpdb;
//
//    if (isset($_POST['submit_form'])) {
//        $table_name = $wpdb->prefix . 'mccf_submissions';
//
//        $wpdb->insert(
//            $table_name,
//            array(
//                'time' => current_time('mysql'),
//                'name' => sanitize_text_field($_POST['full_name']),
//                'email' => sanitize_email($_POST['email_address']),
//                'message' => sanitize_textarea_field($_POST['message']),
//            )
//        );
//
//        $to = get_option('admin_email');
//        $subject = get_option('mccf_email_subject');
//        $headers = 'From: ' . $_POST['full_name'] . ' <' . $_POST['email_address'] . '>' . "\r\n";
//
//        wp_mail($to, $subject, $_POST['message'], $headers);
//
//        set_transient('form_submitted', 'success', 60);
//    }
//}
//
//add_action('wp_head', 'mccf_capture_form');

function mccf_register_options_page(): void
{
    add_menu_page(
        'Custom Contact Form',
        'Custom Contact Form',
        'manage_options',
        'mccf',
        '',
        'dashicons-format-aside'
    );

    add_submenu_page(
        'mccf',
        'Custom Contact Form',
        'Custom Contact Form',
        'manage_options', // only administrators and super admins
        'mccf',
        'mccf_options_page',
        'dashicons-email'
    );

    add_submenu_page(
        'mccf',
        'Contact Form Submissions',
        'Form Submissions',
        'manage_options',
        'mccf_submissions',
        'mccf_list_submissions',
        'dashicons-email',
    );
}

add_action('admin_menu', 'mccf_register_options_page');

function mccf_validate_button_text($input)
{
    if (empty($input)) {
        add_settings_error('mccf_button_text', 'mccf_text_error', 'Button text cannot be empty', 'error');
        return get_option('mccf_button_text');
    }

    add_settings_error('mccf_button_text', 'mccf_text_success', 'Button successfully updated', 'updated');

    return $input;
}

function mccf_options_page() {
    ?>
    <div>
        <h2>Custom Contact Form Settings</h2>
        <?php settings_errors('mccf_button_text'); ?>
        <p>Use [mccf_contact_form] to include your form into posts or pages.</p>
        <form method="post" action="options.php">
            <?php
            settings_fields('mccf_options_group');
            ?>
            <h3>Button Text</h3>
            <p>
                <input type="text" id="mccf_button_text" name="mccf_button_text"
                       value="<?php echo get_option('mccf_button_text'); ?>"/>
            </p>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function mccf_register_settings(): void
{
    add_option('mccf_button_text', 'Send Message');
    register_setting('mccf_options_group', 'mccf_button_text', 'mccf_validate_button_text');
}

add_action('admin_init', 'mccf_register_settings');

function mccf_enqueue_styles()
{
    wp_enqueue_style('mccf_styles', plugin_dir_url(__FILE__) . 'my-custom-contact-form.css');
}

add_action('wp_enqueue_scripts', 'mccf_enqueue_styles');