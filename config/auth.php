<?php defined('SYSPATH') OR die('Direct access is never permitted.');

return array(
    
	/**
     * Set the type of driver to use to authenticate.
     * 
     * @var  string
     */
	'driver' => 'ORM',
	
    /**
     * Hash setup.
     * 
     * @var  array
     */
    'hash' => array(
        /**
         * Type of hash to use for passwords.
         * Any algorithm supported by the hash function can be used here.
         * 
         * @var  string
         * @link  http://php.net/hash
         * @link  http://php.net/hash_algos
         */
        'method' => 'sha256',
        
        /**
         * Generate a `hash_key` for this unique application.
         * 
         * @see  https://www.grc.com/passwords.htm
         */
        'key' => NULL,
    ),
    
	/**
     * Set the auto-login (remember me) cookie lifetime, in seconds.
     * The default lifetime is ().
     * 
     * (12 hours) 43200
     * ( 2 weeks) 21209600
     * 
     * @var  integer
     */
	'lifetimeseconds' => 43200,
    
    /**
     * Set the lifetime of the session.
     * 
     * @var  string
     */
	'lifetime' => '12 hours',
    
    /**
     * The number of failed logins allowed can be specified here:
     * If the user mistypes their password X times, then they will not be permitted to log in 
     * during the jail time. This helps prevent against brute-force attacks.
     */
    
    /**
     * Define the maximum failed attempts to login
     * set '0' to disable the login jail feature.
     */
    'max_failed_logins' => 5,
    
	/**
     * Define the time that user who archive the `max_failed_logins` will need to 
     * wait before his next attempt.
     */
    'login_jail_time' => '15 minutes',
    
    /**
     * Session info.
     * 
     * @var    array
     * @since  3.3.2
     * @uses  Session::$default
     */
    'session' => array(
        // Set the type of session to use.
        'type' => Session::$default,
        
        // Which model is giving authorization.
        'key' => 'Auth_User',
    ),
    
	// Username/password combinations for the Auth File driver.
	'users' => array(
		'admin' => '', // username : password
	),
    
	/**
     * Allow user registration?
     * 
     * @var  boolean
     * @since  3.3.2
     */
	'register' => TRUE,
    
	/**
     * Use username for login and registration (TRUE) 
     * or use email as username (FALSE)?
     * 
     * @var  boolean
     * @since  3.3.2
     */
	'username' => TRUE,
    
    /**
     * Password rules for validation
     * 
     * @var  array
     * @since  3.3.2
     */
	'password' => array(
		'length_min' => 4,
	),
    
	/**
     * Username rules for validation.
     * 
     * @var  array
     * @since  3.3.2
     */
	'name' => array(
		'chars' => 'a-zA-Z0-9_\-\^\.',
		'length_min' => 4,
		'length_max' => 32,
	),
    
	/**
     * Use confirm password field in registration?
     * 
     * @var  boolean
     * @since  3.3.2
     */
	'confirm_pass' => TRUE,
    
	/**
     * Use nickname for registration (TRUE) or use username (FALSE)?
     * 
     * @var  boolean
     * @since  3.3.2
     */
	'use_nick' => FALSE,
    
     /**
      * Toggle reCaptcha support: if set, then during registration the user is shown
      * a reCaptcha which they must answer correctly (unless they are using one of the 3rd party users).
      *
      * Setup
      *   -> You must have the reCaptcha library (e.g. http://recaptcha.net) in your vendors directory. (bundled in the default repo)
      *   -> You must set the private and public key in /config/recaptcha.php from https://www.google.com/recaptcha/admin/create
      * 
      * @var  boolean
      * @since  3.3.2
      */
	'use_captcha' => FALSE,
    
    /**
     * Enable buddy relationship (FALSE)?
     * 
     * @var  boolean
     */
    'enable_buddy' => FALSE,
    
);
