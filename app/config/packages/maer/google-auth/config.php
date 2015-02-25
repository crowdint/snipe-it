<?php
/**
 * Google Auth Configuration
 * --------------------------
 */
return array(

    'client_id'    => getenv('GOOGLE_CLIENT_ID'),
    'secret'       => getenv('GOOGLE_CLIENT_SECRET'),
    'callback_url' => getenv('GOOGLE_CLIENT_CALLBACK_URL'),

    /*
    * Allowed accounts
    * -------------------------------------
    * Enter full e-mail addresses or entire domains.
    * If empty, all e-mail addresses will be allowed.
    */
    // 'allow'     => array('your-email@a-domain.com', 'another-domain.com'),
    'allow' => preg_split('/[\s,]+/', getenv('GOOGLE_ALLOWED_DOMAINS')),

    /*
    * Disallowed accounts
    * -------------------------------------
    * Enter full e-mail addresses or entire domains.
    * If an e-mail or domain is in the allowed and disallowed,
    * it will be blocked.
    */
    'disallow'     => preg_split('/[\s,]+/', getenv('GOOGLE_DISALLOWED_DOMAINS')),
);
