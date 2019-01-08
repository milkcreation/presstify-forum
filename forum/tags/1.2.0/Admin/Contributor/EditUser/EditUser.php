<?php
namespace tiFy\Plugins\Forum\Admin\Contributor\EditUser;

class EditUser extends \tiFy\Core\Templates\Admin\Model\EditUser\EditUser
{
	/** == Récupération de la liste des rôles concernés par la vue == **/
	public function set_roles()
	{
		return \tiFy\Plugins\Forum\Forum::getRoleNames();
	}
}