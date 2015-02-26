## Introduction 

The User Authentication and Authorization is provided by the Viper Auth Module.

## Install

The Auth Module is automatically installed with Viper Framework, but needs to be enabled before you can use it. To enable, open your `application/bootstrap.php` file and modify the call to [Viper::modules] by including the Auth Module like so:

~~~
Viper::modules(array(
	'auth' => MODPATH.'auth', // Viper Auth Module
));
~~~

### After Install

After you install and enable the module, you'll then need to adjust the [config file](#configuration).

The Auth Module provides the [Auth::File] driver for you.

[!!] There is also an Auth Driver included with the Viper ORM Module.

As your application needs change you may need to find another driver or [develop](#developing-drivers) your own.

### Configuration

The default configuration file is located in `MODPATH/auth/config/auth.php`. You should copy this file to `APPPATH/config/auth.php` and make changes there, in keeping with the [cascading filesystem](../viper/files).

[Config merging](../viper/config#config-merging) allows these default configuration settings to apply if you don't overwrite them in your application configuration file.

Name | Type | Default | Description
-----|------|---------|------------
driver | `string` | file | The name of the Auth Driver to use.
hash_method | `string` | sha256 | The hashing function to use for the passwords.
hash_key | `string` | NULL | The key to use when hashing the password.
session_type | `string` | [Session::$default] | The type of session to use when storing the Auth User.
session_key | `string` | Auth_User | The name of the session variable used to save the user.

## Usage

The Auth Module provides methods to help you log users in and out of your application.

### Log In

The [Auth::login] method handles the login.

~~~
	// Handled from a form with inputs with parameters email and password.
	$post = $this->request->post();
    
	$success = Auth::instance()->login($post['email'], $post['password']);
	
	if ($success)
	{
		// Login successful, send response to your app.
	}
	else
	{
		// Login failed, send back to form with error message.
	} // End If
~~~

### Logged In User

There are two ways to check if a user is logged in. If you simply need to verify if the user is logged in use [Auth::logged_in].

~~~
	if (Auth::instance()->logged_in())
	{
		// The user is logged in, continue to destination.
        $this->redirect('/user/profile', 302);
	}
	else
	{
		// The user is not logged in, redirect to the log in form.
        $this->redirect('/login', 302);
	} // End If
~~~

You can also get the logged in user data object by using [Auth::get_user].

~~~
	$user = Auth::instance()->get_user();
	
	// Check for a user (NULL if not user is found).
	if ($user !== null)
	{
		// User is found, continue on.
        $this->redirect('/user/profile', 302);
	}
	else
	{
		// User was not found, redirect to the log in form.
        $this->redirect('/login', 302);
	} // End If
~~~

### Log Out

The [Auth::logout] method will take care of logging out a user.

~~~
	Auth::instance()->logout();
	// Redirect the user back to login page.
~~~

### Forcing Login

[Auth_File::force_login] allows you to force a user login without a password.

~~~
	// Force the user with a username of admin to be logged into your application.
	Auth::instance()->force_login('admin');
	
	// Get the object.
	$user = Auth::instance()->get_user();
~~~

## Drivers

### File Driver

The [Auth::File] driver is included with the Auth Module.

Below are additional configuration options that can be set for this driver.

Name | Type | Default | Description
-----|------|---------|-------------
users | `array` | array() | A user => password (_hashed_) array of all the users in your application.

## Developing Drivers

### Real World Example

The ORM Module comes with an Auth Driver you can learn from.

[!!] We will be developing an `example` driver. In your own driver you will substitute `example` with your driver name.

This example file would be saved at `APPPATH/classes/auth/example.php` (or `MODPATH` if you are creating a module).

### A Quick Example

Let's take a look at the following example:

~~~
	class Auth_Example extends Auth
	{
		protected function _login($username, $password, $remember)
		{
			// Do username/password check here.
		} // End Method
	
		public function password($username)
		{
			// Return the password for the username.
		} // End Method
	
		public function check_password($password)
		{
			// Check to see if the logged in user has the given password.
		} // End Method
	
		public function logged_in($role = NULL)
		{
			// Check to see if the user is logged in, and if $role is set, has all roles.
		} // End Method
	
		public function get_user($default = NULL)
		{
			// Get the logged in user, or return the $default if a user is not found.
		} // End Method
		
	} // End Auth_Example Class
~~~

### Extending Auth

All drivers must extend the [Auth] class.

	class Auth_Example extends Auth

### Abstract Methods

The `Auth` class has 3 abstract methods that must be defined in your new driver.

~~~
	abstract protected function _login($username, $password, $remember);
	
	abstract public function password($username);
	
	abstract public function check_password($user);
~~~

### Extending Functionality

Given that every Auth system is going to check if users exist and if they have roles or not you will more than likely have to change some default functionality.

Here are a few functions that you should pay attention to.

~~~
	public function logged_in($role = NULL)
	
	public function get_user($default = NULL)
~~~

### Activating The Driver

After you create your driver you will want to use it. It is a easy as setting the `driver` [configuration](config) option to the name of your driver (in our case `example`).













