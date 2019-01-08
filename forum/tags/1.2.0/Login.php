<?php
/*
 * @Overridable
 */
namespace tiFy\Plugins\Forum;

class Login extends \tiFy\Components\Login\Factory
{
	/* = CONTROLEUR = */
	/** == Url de redirection == **/
	public function getParamRedirectUrl( $default = '' )
	{
		return Forum::getBaseUri();
	}
	
	/** == Roles == **/
	public function getParamAllowedRoles( $default = '' )
	{
		return Forum::getRoleNames();
	}
}