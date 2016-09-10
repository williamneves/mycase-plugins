<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html 
*/

/**
 * O_Provider_Model provide a common interface for supported IDps on HybridAuth.
 *
 * Basically, each provider adapter has to define at least 4 methods:
 *   O_Providers_{provider_name}::initialize()
 *   O_Providers_{provider_name}::loginBegin()
 *   O_Providers_{provider_name}::loginFinish()
 *   O_Providers_{provider_name}::getUserProfile()
 *
 * HybridAuth also come with three others models
 *   Class O_Provider_Model_OpenID for providers that uses the OpenID 1 and 2 protocol.
 *   Class O_Provider_Model_OAuth1 for providers that uses the OAuth 1 protocol.
 *   Class O_Provider_Model_OAuth2 for providers that uses the OAuth 2 protocol.
 */
abstract class O_Provider_Model
{
	/* IDp ID (or unique name) */
	public $providerId = NULL;

	/* specific provider adapter config */
	public $config     = NULL;

   	/* provider extra parameters */
	public $params     = NULL;

	/* Endpoint URL for that provider */
	public $endpoint   = NULL; 

	/* O_User obj, represents the current loggedin user */
	public $user       = NULL;

	/* the provider api client (optional) */
	public $api        = NULL; 

	/**
	* common providers adapter constructor
	*/
	function __construct( $providerId, $config, $params = NULL )
	{
		# init the IDp adapter parameters, get them from the cache if possible
		if( ! $params ){
			$this->params = O_Auth::storage()->get( "hauth_session.$providerId.id_provider_params" );
		}
		else{
			$this->params = $params;
		}

		// idp id
		$this->providerId = $providerId;

		// set HybridAuth endpoint for this provider
		$this->endpoint = O_Auth::storage()->get( "hauth_session.$providerId.hauth_endpoint" );

		// idp config
		$this->config = $config;

		// new user instance
		$this->user = new O_User();
		$this->user->providerId = $providerId;

		// initialize the current provider adapter
		$this->initialize(); 

		O_Logger::debug( "O_Provider_Model::__construct( $providerId ) initialized. dump current adapter instance: ", serialize( $this ) );
	}

	// --------------------------------------------------------------------

	/**
	* IDp wrappers initializer
	*
	* The main job of wrappers initializer is to performs (depend on the IDp api client it self): 
	*     - include some libs nedded by this provider,
	*     - check IDp key and secret,
	*     - set some needed parameters (stored in $this->params) by this IDp api client
	*     - create and setup an instance of the IDp api client on $this->api 
	*/
	abstract protected function initialize(); 

	// --------------------------------------------------------------------

	/**
	* begin login 
	*/
	abstract protected function loginBegin();

	// --------------------------------------------------------------------

	/**
	* finish login
	*/
	abstract protected function loginFinish();

	// --------------------------------------------------------------------

   	/**
	* generic logout, just erase current provider adapter stored data to let O_Auth all forget about it
	*/
	function logout()
	{
		O_Logger::info( "Enter [{$this->providerId}]::logout()" );

		$this->clearTokens();

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	* grab the user profile from the IDp api client
	*/
	function getUserProfile()
	{
		O_Logger::error( "HybridAuth do not provide users contats list for {$this->providerId} yet." ); 
		
		throw new Exception( "Provider does not support this feature.", 8 ); 
	}

	// --------------------------------------------------------------------

	/**
	* load the current logged in user contacts list from the IDp api client  
	*/
	function getUserContacts() 
	{
		O_Logger::error( "HybridAuth do not provide users contats list for {$this->providerId} yet." ); 
		
		throw new Exception( "Provider does not support this feature.", 8 ); 
	}

	// --------------------------------------------------------------------

	/**
	* return the user activity stream  
	*/
	function getUserActivity( $stream ) 
	{
		O_Logger::error( "HybridAuth do not provide user's activity stream for {$this->providerId} yet." ); 
		
		throw new Exception( "Provider does not support this feature.", 8 ); 
	}

	// --------------------------------------------------------------------

	/**
	* return the user activity stream  
	*/ 
	function setUserStatus( $status )
	{
		O_Logger::error( "HybridAuth do not provide user's activity stream for {$this->providerId} yet." ); 
		
		throw new Exception( "Provider does not support this feature.", 8 ); 
	}

	// --------------------------------------------------------------------

	/**
	* return true if the user is connected to the current provider
	*/ 
	public function isUserConnected()
	{
		return (bool) O_Auth::storage()->get( "hauth_session.{$this->providerId}.is_logged_in" );
	}

	// --------------------------------------------------------------------

	/**
	* set user to connected 
	*/ 
	public function setUserConnected()
	{
		O_Logger::info( "Enter [{$this->providerId}]::setUserConnected()" );
		
		O_Auth::storage()->set( "hauth_session.{$this->providerId}.is_logged_in", 1 );
	}

	// --------------------------------------------------------------------

	/**
	* set user to unconnected 
	*/ 
	public function setUserUnconnected()
	{
		O_Logger::info( "Enter [{$this->providerId}]::setUserUnconnected()" );
		
		O_Auth::storage()->set( "hauth_session.{$this->providerId}.is_logged_in", 0 ); 
	}

	// --------------------------------------------------------------------

	/**
	* get or set a token 
	*/ 
	public function token( $token, $value = NULL )
	{
		if( $value === NULL ){
			return O_Auth::storage()->get( "hauth_session.{$this->providerId}.token.$token" );
		}
		else{
			O_Auth::storage()->set( "hauth_session.{$this->providerId}.token.$token", $value );
		}
	}

	// --------------------------------------------------------------------

	/**
	* delete a stored token 
	*/ 
	public function deleteToken( $token )
	{
		O_Auth::storage()->delete( "hauth_session.{$this->providerId}.token.$token" );
	}

	// --------------------------------------------------------------------

	/**
	* clear all existen tokens for this provider
	*/ 
	public function clearTokens()
	{ 
		O_Auth::storage()->deleteMatch( "hauth_session.{$this->providerId}." );
	}
}