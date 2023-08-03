<?php

function mccf_list_submissions() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mccf_submissions';

    // Handle deletion
    if (isset($_GET['delete'])) {
        $wpdb->delete($table_name, array('id' => $_GET['delete']));
        echo '<div class="notice notice-success is-dismissible"><p>Submission deleted.</p></div>';
    }

    $submissions = $wpdb->get_results("SELECT * FROM $table_name");

    echo '<h2>Contact Form Submissions</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Time</th><th>Action</th></tr></thead>';
    echo '<tbody>';
    foreach ($submissions as $submission) {
        echo '<tr>';
        echo '<td>' . esc_html($submission->name) . '</td>';
        echo '<td>' . esc_html($submission->email) . '</td>';
        echo '<td>' . esc_html($submission->message) . '</td>';
        echo '<td>' . esc_html($submission->time) . '</td>';
        echo '<td><a href="?page=mccf_submissions&delete=' . esc_attr($submission->id) . '">Delete</a></td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}