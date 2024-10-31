<div class="wrap">
    <h2>Rapidmail</h2>
    <form action="options.php" method="post">
        <?php
        settings_fields( 'rapidmail_connector_settings' );
        do_settings_sections( 'rapidmail_connector_settings_general' );
        submit_button();
        ?>
    </form>
</div>