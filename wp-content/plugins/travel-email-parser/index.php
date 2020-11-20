<?php
require __DIR__ . '/vendor/autoload.php';

use ZBateson\MailMimeParser\MailMimeParser;
use ZBateson\MailMimeParser\Message;

/*
Plugin Name: PHP Travel Email Parser
Plugin URI: http://example.com
Description: Simple PHP Travel Email Parser
Version: 1.0
Author: Abu Bah
Author URI: https://github.com/abu-bah/
*/

function htmlFormCode() {
    echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="POST" enctype="multipart/form-data">';
    echo '<p>';
    echo 'Email File (required) <br/>';
    echo '<input type="file" name="email-file">' . ( isset($_POST["email-file"]) ? esc_attr($_POST["email-file"]) : '' ) . '</input>';
    echo '</p>';
    echo '<p><input type="submit" name="form-submitted" value="Get Reservation Details"></p>';
    echo '</form>';
}

function parseEmailContents() {

    // if the submit button is clicked, parse the email
    if (isset($_POST['form-submitted'])) {

        if (isset($_FILES['email-file']) && $_FILES['email-file']['tmp_name']) {
            $file = $_FILES['email-file']['tmp_name'];
            $parts = explode('.', $_FILES['email-file']['name']);
            $extension = end($parts);

            // extension validation
            if ($extension !== 'eml') {
                echo '<p style="color: red;">Invalid file format. File must be in .eml format.</p>';
            } else {
                $emailContents = file_get_contents($file);
                $message = Message::from($emailContents);
                $html = strip_tags($message->getHtmlContent());
                $from = $message->getHeader('From');

                $airline = str_replace('From: ', '', $from);
                $passengerName = str_replace('!', '', substr($html, strpos($html, 'Hello ') + 6, strpos($html, 'Issued:') - (strpos($html, 'Hello ') + 6)));
                $recordLocator = trim(substr($html, strpos($html, 'Record locator: ') + 16, strpos($html, 'Manage Your Trip') - (strpos($html, 'Record locator: ') + 16)));

                echo "<p><b>Airline:</b> $airline</p>";
                echo "<p><b>Passenger name:</b> $passengerName</p>";
                echo "<p><b>Record locator:</b> $recordLocator</p>";

                die;
            }
        } else {
            echo '<p style="color: red;">Email File field is required.</p>';
        }
    }
}

function emailContentFormShortcode() {
    parseEmailContents();
    htmlFormCode();
}

add_shortcode('email_content_form', 'emailContentFormShortcode');

?>