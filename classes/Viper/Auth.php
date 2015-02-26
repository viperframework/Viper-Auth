<?php defined('SYSPATH') OR die('Direct access is never permitted.');

/**
 * User Authorization Library Abstract Class.
 * 
 * Handles user login and logout, as well as secure password hashing.
 *
 * @package      Viper/Auth
 * @category     Base
 * @name         Auth
 * @author       Michael Noël <mike@viperframework.com>
 * @author       Viper Team
 * @copyright    (c) 2015 Viper Framework
 * @license      https://viperframework.com/license
 * @version      4.0.1
 */

abstract class Viper_Auth {
    
	/**
	 * @static
	 * @access  protected
	 * @var     instance  Auth instances
	 */
	protected static $_instance;

    /**
	 * @access  protected
	 * @var     Session  Session data
	 */
	protected $_session;

    /**
	 * @access  protected
	 * @var     Config  Config file
	 */
	protected $_config;

	/**
	 * Loads Session and configuration options.
	 *
	 * @access  public
	 * @param   array  Config Options
	 * @return  void
     * @uses  Session::instance
     * @uses  Viper::DEVELOPMENT
     * @uses  Viper::$environment
     * @uses  Log::instance
     * @uses  Log::DEBUG
	 */
	public function __construct($config = array())
	{
		// Save the config in the object.
		$this->_config = $config;
		
		// Set the session in place.
		$this->_session = Session::instance($this->_config['session']['type']);
        
        // Check for development phase.
        if (Viper::DEVELOPMENT === Viper::$environment) 
        {
            $log = Log::instance();
            
            $log->add(Log::DEBUG, ':message', array(
                ':message' => 'Auth Library loaded.',
            ));
        } // End If
	} // End Method
	
	/**
	 * Instantiate the singleton pattern.
	 *
	 * @static
	 * @access  public
	 * @return  Auth
     * @uses  Viper::$config
     * @uses  Auth::$_instance
	 */
	public static function instance()
	{
		if ( ! isset(Auth::$_instance))
		{
			// Load the configuration for this type.
			$config = Viper::$config->load('auth');
			
			if ( ! $type = $config->get('driver'))
			{
				$type = 'file';
			} // End If

			// Set the session class name.
			$class = 'Auth_'.ucfirst($type);

			// Create a new session instance.
			Auth::$_instance = new $class($config);
		} // End If

		return Auth::$_instance;
	} // End Method
    
    /**
     * Check Password.
     *
     * @access  public
     * @param   string  $password  Password
     * @return  void
     */
	abstract public function check_password($password);
    
    /**
     * Get Provider.
     * 
     * @access  public
     * @return  mixed
     * @since  4.0.1
     */
    public function get_provider()
    {
        return $this->_session->get($this->_config['session']['key'].'_provider', NULL);
    } // End Method
    
    /**
	 * Login.
	 *
	 * @access  public
	 * @param   string   $username
	 * @param   string   $password
	 * @param   boolean  $remember
	 * @return  void
	 */
	abstract protected function _login($username, $password, $remember);
    
    /**
     * Get User.
     *
     * Gets the currently logged in user from the session.
     * Returns NULL if no user is currently logged in.
     *
     * @access  public
     * @param   mixed  $default  Default value to return if the user is currently not logged in
     * @return  mixed
     */
	public function get_user($default = NULL)
	{
		return $this->_session->get($this->_config['session']['key'], $default);
	} // End Method
    
	/**
     * Perform a hmac hash, using the configured method.
     *
     * @access  public
     * @param   string  $str  String to hash
     * @return  string
     * @uses  Viper_Exception
     * @uses  hash_hmac
     */
	public function Hash($string)
	{
		if ( ! $this->_config['hash']['key']) 
		{
			throw new Viper_Exception('A valid hash key must be set in your auth config.');
		} // End If
		
		return hash_hmac($this->_config['hash']['method'], $string, $this->_config['hash']['key']);
	} // End Method
    
    /**
     * Creates a hashed hmac password from a plaintext password. This
     * method is deprecated, [Auth::Hash] should be used instead.
     *
     * @deprecated
     * @access  public
     * @param   string  $password  Plaintext password
     * @return  Hash
     * @uses  Auth::Hash
     */
	public function hash_password($password)
	{
		return $this->Hash($password);
	} // End Method
    
    /**
     * Check if there is an active session. Optionally allows 
     * checking for a specific role.
     *
     * @access  public
     * @param   string  $role  Role name
     * @return  mixed
     */
	public function logged_in($role = NULL)
	{
		return ($this->get_user() !== NULL);
	} // End Method
    
    /**
     * Logged In OAuth2.
     * 
     * @todo  NEEDS WORK!
     * 
     * @access  public
     * @param   mixed  $provider  OAuth2 Provider
     * @return  mixed
     * @since  4.0.1
     */
    public function logged_in_oauth($provider = NULL)
    {
        // For starters, the user needs to be logged in.
        if ( ! parent::logged_in())
        {
            return FALSE;
        } // End If
        
        // Get the user from the session. Because `parent::logged_in` return TRUE,
        // we know this is a valid user ORM object.
        $user = $this->get_user();
        
        if ($provider !== NULL)
        {
            // Check for one specific OAuth2 provider.
            $provider = $provider.'_id';
            
            //return ! empty($user->$provider);
        } // End If
        
        // Otherwise, just check the password field.
        // We don't store passwords for OAuth users.
        //return empty($user->password);
    } // End Method
    
	/**
     * Attempt to log in an User by using an ORM object and 
     * plain-text password.
	 *
	 * @access  public
	 * @param   string   $username  Username to log in
	 * @param   string   $password  Password to check against
	 * @param   boolean  $remember  Enable autologin
	 * @return  boolean
	 */
	public function login($username, $password, $remember = FALSE)
	{
		if (empty($password)) 
		{
			return array(
				'valid' => FALSE,
				'message' => 'password',
			);
		} // End If
		
		return $this->_login($username, $password, $remember);
	} // End Method
    
    /**
     * Complete Login.
     *
     * @access  public
     * @param   object  $user  User data object
     * @return  boolean
     */
	protected function complete_login($user)
	{
		// Regenerate `session_id`.
		$this->_session->regenerate();
		
		// Store `username` in session.
		$this->_session->set($this->_config['session']['key'], $user);
		
		return TRUE;
	} // End Method
    
	/**
	 * Log out an User by removing the related session variables.
	 *
	 * @access  public
	 * @param   boolean  $destroy     Completely destroy the session
	 * @param   boolean  $logout_all  Remove all tokens for user
	 * @return  boolean
	 */
	public function logout($destroy = FALSE, $logout_all = FALSE)
	{
		if ($destroy === TRUE)
		{
			// Destroy the session completely.
			$this->_session->destroy();
		}
		else
		{
			// Remove the user from the session.
			$this->_session->delete($this->_config['session']['key']);

			// Regenerate `session_id`.
			$this->_session->regenerate();
		} // End If

		// Double check.
		return ! $this->logged_in();
	} // End Method
    
    /**
     * Unique Key.
     * 
     * @access  public
     * @param   mixed  $value 
     * @param   mixed  $oauth_provider 
     * @return  mixed
     * @since  4.0.1
     * @uses  Valid::email
     */
    public function unique_key($value, $oauth_provider = NULL)
    {
        if ($oauth_provider)
        {
            return $oauth_provider.'_id';
        } // End If
        
        return Valid::email($value) ? 'email' : 'name';
    } // End Method
    
    
    /**
     * Get enabled oAuth2 providers.
     * 
     * @static
     * @access  public
     * @return  array
     * @uses  Module::is_active
     * @uses  Viper::$config
     * @uses  Route::get
     */
	public static function providers()
	{
		if ( ! Module::is_active('oauth2'))
        {
			return array();
        } // End If

		$config = Viper::$config->load('oauth2')->get('providers', array());
        
		$providers = array();

		foreach($config as $name => $provider)
		{
			if ($provider['enable'] === TRUE)
			{
				$providers[$name] = array(
					'name' => $name,
					'url' => Route::get('oauth2/provider')
                        ->uri(array(
                            'provider' => $name, 
                            'action' => 'login',
                        )),
					'icon' => isset($provider['icon']) ? $provider['icon'] : 'facebook',
				);
			} // End If
		} // End Foreach
        
		return $providers;
	} // End Method
    
    /**
     * Password.
     *
     * @access  public
     * @param   string  $username
     * @return  void
     */
	abstract public function password($username);
    
	/**
	 * Username. Gets the logged in username.
	 *
	 * @access  public
	 * @param   bool  Use route username parameter
	 * @return  object|string
	 * @since   4.1.0
	 * @uses  Request::initial
	 */
	public function username($use_route = FALSE) 
	{
		if ( ! $use_route) 
		{
			if ($this->logged_in()) 
			{
				// Data object.
				$user = $this->get_user();
				
				// Username.
				return $user->username;
			} // End If
		} // End If
		
		// Check for vanity url.
		$username = Request::initial()->param('username');
		
		return ($username != NULL) ? (string) $username : (string) NULL;
	} // End Method
	
	/**
	 * Get an user id.
	 * 
	 * @access  public
	 * @return  integer
	 * @since  4.1.1
	 */
	public function id() 
	{
		if ($user = $this->get_user()) 
		{
			if ($user !== NULL) 
			{
				return $user->id;
			} // End If
		} // End If
		
		return NULL;
	} // End Method
    
    /**
     * Name confirm.
     * 
     * @access  public
     * @param   mixed  $name  Full name
     * @return  mixed
     * @since  4.1.0
     * @uses  Text::slashes
     */
    public function name_confirm($name = NULL) 
    {
        if ($name == NULL) 
        {
            return NULL;
        } // End If
        
        if ($user = $this->get_user()) 
        {
            if ($user !== NULL) 
            {
                $user_full_name = Text::slashes('strip', $user->profile->first_name.' '.$user->profile->last_name);
                
                return ($user_full_name == $name) ? TRUE : FALSE;
            } // End If
        } // End If
    } // End Method
    
    /**
     * Full Name.
     * 
     * @access  public
     * @param   mixed  $divider  Divider between names
     * @return  string
     * @since  4.1.1
     */
    public function full_name($divider = ' ') 
    {
        return $this->name('full', $divider);
    } // End Method
    
    /**
     * Get the name of the user.
     * 
     * @access  public
     * @param   mixed  Return type
     * @return  mixed
     * @since  4.1.1
     * @uses  Text::slashes
     */
    public function name($type = 'full', $divider = ' ') 
    {
        if ($user = $this->get_user()) 
        {
            if ($user !== NULL) 
            {
                switch ($type) 
                {
                    case ('first'):
                        
                        $name = Text::slashes('strip', $user->profile->first_name);
                        
                        break;
                    case ('last'):
                        
                        $name = Text::slashes('strip', $user->profile->last_name);
                        
                        break;
                    case ('last first'):
                        
                        $name = Text::slashes('strip', $user->profile->last_name.$divider.$user->profile->first_name);
                        
                        break;
                    case ('first last'):
                    case ('full'):
                    default:
                        
                        $name = Text::slashes('strip', $user->profile->first_name.$divider.$user->profile->last_name);
                        
                        break;
                } // End Switch
                
                return $name;
            } // End If
        } // End If
    } // End Method
    
} // End Viper_Auth Abstract Class
