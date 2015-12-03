<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       bonnier.dk
 * @since      1.0.0
 *
 * @package    Wa_External_Header_V2
 * @subpackage Wa_External_Header_V2/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">
    <form action='options.php' method='post'>

        <h2>Bonnier co-branding settings</h2>

        <?php
        settings_fields( $this->plugin_name );
        do_settings_sections( $this->plugin_name );
        submit_button();
        ?>

    </form>
</div>