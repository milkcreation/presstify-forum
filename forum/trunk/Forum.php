<?php
/*
Plugin Name: Forum
Plugin URI: http://presstify.com/plugins/forum
Description: Gestion de forum
Version: 1.1.465
Author: Milkcreation
Author URI: http://profile.milkcreation.fr/jordy
*/

namespace tiFy\Plugins\Forum;

class Forum extends \tiFy\Environment\Plugin
{
	/* = ARGUMENTS = */
	// Rôles et habilitations
	private static $Roles			= array();
	
	// Url d'accès
	private static $BaseUri			= null;
	
	// Contrôleurs
	private static $Controller		= array();
					
	/* = CONSTRUCTEUR = */
	public function __construct()
	{	
		parent::__construct();
	
		// Définition des rôles et habilitations
		self::$Roles = self::tFyAppConfig( 'roles' );
		
		// Chargement des contrôleurs
		new Handle;
		new User;
		
		/// Habilitations
		$capabilities = self::getOverride( '\tiFy\Plugins\Forum\Capabilities' );
		static::$Controller['capabilities'] = new $capabilities;
		
		/// Formulaire d'authentification
		tify_component_register( 'Login' );		
		$login = self::getOverride( '\tiFy\Plugins\Forum\Login' );
		static::$Controller['login'] = new $login( 'tiFyPluginForum-Login' );
		
		/// Formulaire
		$forms = self::getOverride( '\tiFy\Plugins\Forum\Forms' );
		static::$Controller['forms'] = new $forms;
		
		/// Affichage général
		$template = self::getOverride( '\tiFy\Plugins\Forum\Template' );
		static::$Controller['template'] = new $template;
		
		/// Fonctions d'aide 
		require_once self::tFyAppDirname() .'/Helpers.php';
	}
	
	/* = CONTRÔLEURS = */
	/** == Récupération des controleurs == **/
	public static function getController( $controller )
	{
		if( isset( static::$Controller[$controller] ) ) :
			return static::$Controller[$controller];
		endif;
	}
	
	/** == Récupération des rôles et de leurs attributs == **/
	public static function getRoles()
	{
		if( ! empty( self::$Roles ) ) :
			return self::$Roles;
		else :
			return array();
		endif;
	}
	
	/** == Récupération de la liste des rôles == **/
	public static function getRoleNames()
	{
		return array_keys( self::getRoles() );
	}
	
	/** == Récupération de la liste des rôles == **/
	public static function getBaseUri()
	{
		if( ! empty( self::$BaseUri ) ) :
			return self::$BaseUri;
		else :
			return self::$BaseUri = ( $base_uri = Options::getHookPermalink() ) ? $base_uri : home_url();
		endif;
	}	
	
	/** == == **/
	public static function isActiveUser( $user_id = 0 )
	{
		// Bypass
		if( ! $user_id )
			$user_id = get_current_user_id();
		if( ! $user_id )
			return false;
		
		if( user_can( $user_id, 'tify_forum_allowed_user' ) ) :
			return get_user_option( 'tify_forum_active', $user_id );
		else :
			return true;
		endif;
	}
}
