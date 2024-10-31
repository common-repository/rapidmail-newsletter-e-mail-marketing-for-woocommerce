<?php

namespace Rapidmail\Connector\Admin;

/**
 *
 * @package    Rapidmail_Connector
 * @subpackage Rapidmail_Connector/admin
 * @author     rapidmail GmbH <ebess@rapidmail.de>
 */
class Auth extends \WC_Auth
{
    public function create_key() {
        return $this->create_keys('Rapidmail ' . date('Y-m-d H:i:s'), get_current_user_id(), 'read');
    }
}