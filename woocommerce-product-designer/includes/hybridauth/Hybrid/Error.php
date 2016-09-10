<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
*/

/**
 * Errors manager
 * 
 * HybridAuth errors are stored in Hybrid::storage() and not displayed directly to the end user 
 */
class O_Error
{
	/**
	* store error in session
	*/
	public static function setError( $message, $code = NULL, $trace = NULL, $previous = NULL )
	{
		O_Logger::info( "Enter O_Error::setError( $message )" );

		O_Auth::storage()->set( "hauth_session.error.status"  , 1         );
		O_Auth::storage()->set( "hauth_session.error.message" , $message  );
		O_Auth::storage()->set( "hauth_session.error.code"    , $code     );
		O_Auth::storage()->set( "hauth_session.error.trace"   , $trace    );
		O_Auth::storage()->set( "hauth_session.error.previous", $previous );
	}

	/**
	* clear the last error
	*/
	public static function clearError()
	{ 
		O_Logger::info( "Enter O_Error::clearError()" );

		O_Auth::storage()->delete( "hauth_session.error.status"   );
		O_Auth::storage()->delete( "hauth_session.error.message"  );
		O_Auth::storage()->delete( "hauth_session.error.code"     );
		O_Auth::storage()->delete( "hauth_session.error.trace"    );
		O_Auth::storage()->delete( "hauth_session.error.previous" );
	}

	/**
	* Checks to see if there is a an error. 
	* 
	* @return boolean True if there is an error.
	*/
	public static function hasError()
	{ 
		return (bool) O_Auth::storage()->get( "hauth_session.error.status" );
	}

	/**
	* return error message 
	*/
	public static function getErrorMessage()
	{ 
		return O_Auth::storage()->get( "hauth_session.error.message" );
	}

	/**
	* return error code  
	*/
	public static function getErrorCode()
	{ 
		return O_Auth::storage()->get( "hauth_session.error.code" );
	}

	/**
	* return string detailled error backtrace as string.
	*/
	public static function getErrorTrace()
	{ 
		return O_Auth::storage()->get( "hauth_session.error.trace" );
	}

	/**
	* @return string detailled error backtrace as string.
	*/
	public static function getErrorPrevious()
	{ 
		return O_Auth::storage()->get( "hauth_session.error.previous" );
	}
}
