<?php defined('SYSPATH') OR die('Direct access is never permitted.');

/**
 * File Auth driver.
 *
 * [!!] This Auth driver does not support roles nor `auto_login`.
 *
 * @package      Viper/Auth
 * @category     Auth
 * @name         File
 * @author       Michael Noël <mike@viperframework.com>
 * @author       Viper Team
 * @copyright    (c) 2015 Viper Framework
 * @license      https://viperframework.com/license
 * @version      3.3.2
 */

class Viper_Auth_File extends Auth {

	/**
	 * List of Users.
	 *
	 * @access  protected
	 * @var     Object  $_users  Object of Users
	 */
	protected $_users;

	/**
	 * Constructor loads the user list into the class.
	 * 
	 * @access  public
	 * @return  void
	 * @uses  Arr::get
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Load a user list.
		$this->_users = Arr::get($config, 'Users', array());
	} // End Method

	/**
	 * Logs a user in.
	 *
	 * @access  protected
	 * @param   string   $username  Username
	 * @param   string   $password  Password
	 * @param   boolean  $remember  Enable autologin (not supported)
	 * @return  boolean
	 */
	protected function _login($username, $password, $remember)
	{
		if (is_string($password))
		{
			// Create a hashed password.
			$password = $this->Hash($password);
		} // End If

		if (isset($this->_users[$username]) AND $this->_users[$username] === $password)
		{
			// Complete the login.
			return $this->complete_login($username);
		} // End If

		// Login failed.
		return FALSE;
	} // End Method

	/**
	 * Force Login.
	 *
	 * Forces a user to be logged in, without specifying a password.
	 *
	 * @access  public
	 * @param   mixed    $username  Username
	 * @return  boolean
	 */
	public function force_login($username)
	{
		// Complete the login.
		return $this->complete_login($username);
	} // End Method

	/**
	 * Get the stored password for a username.
	 *
	 * @access  public
	 * @param   mixed  $username  Username
	 * @return  string
	 */
	public function password($username)
	{
		return Arr::get($this->_users, $username, FALSE);
	} // End Method

	/**
	 * Compare password with original (plain text). Works for current (logged in) user.
	 *
	 * @access  public
	 * @param   string  $password  Password
	 * @return  boolean
	 */
	public function check_password($password)
	{
		$username = $this->get_user();

		if ($username === FALSE)
		{
			return FALSE;
		} // End If

		return ($password === $this->password($username));
	} // End Method

} // End Viper_Auth_File Class
