<?php
/*
 * @Overridable
 */
namespace tiFy\Plugins\Forum;

use \tiFy\Plugins\Forum\Options;
use \tiFy\Plugins\Forum\User;

class Capabilities extends \tiFy\App\Factory
{
    /* = CONSTRUCTEUR = */
    public function __construct()
    {
        parent::__construct();
        
        // Déclencheurs
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
    }
            
    /* = DECLENCHEURS = */
    /** == Initialisation de l'interface d'administration == **/
    final public function admin_init()
    {                
        // Création des roles et des habilitations
        foreach( (array) Forum::getRoles() as $role => $args ) :
            // Création du rôle
            if( ! $_role =  get_role( $role ) )
                $_role = add_role( $role, $args['display_name'] );

            // Création des habilitations
            foreach( (array)  $args['capabilities'] as $cap => $grant ) :
                if( ! isset( $_role->capabilities[$cap] ) ||  ( $_role->capabilities[$cap] != $grant ) ) :
                    $_role->add_cap( $cap, $grant );
                endif;
            endforeach;
        endforeach;
    }
    
    /** == Définition des habilitations == **/
    final public function map_meta_cap( $caps, $cap, $user_id, $args )
    {
        $user = get_userdata( $user_id );
        switch ( $cap ) :
            // Utilisateur habilité
            case 'tify_forum_allowed_user' :
            // Habilitation d'accès à la consultation des pages du forum
            case 'tify_forum_read' :
            // Habilitation de modération des sujets
            case 'tify_forum_moderate_topics' :
            // Habilitation de modération des contributions
            case 'tify_forum_moderate_contribs' :
            // Habilitation de soumission de nouveau sujet
            case 'tify_forum_submit_topic' :
            // Habilitation de soumission de contribution
            case 'tify_forum_submit_contrib' :
                $callback = preg_replace( '/^tify_forum_/', '', $cap );                
                $caps = call_user_func( array( $this, $callback ), $caps, $cap, $user_id, $args );
                break;
        endswitch;
            
        return $caps;
    }
    
    /* = CONTROLEURS = */
    /** == Habilitation de modération des sujets == **/
	public function allowed_user( $caps, $cap, $user_id, $args )
	{
		if( ! is_user_logged_in() ) :
			$caps = array( 'do_not_allow' );
		else :
			$userdata = get_userdata( $user_id );
			foreach( (array) Forum::getRoleNames() as $role ) :
				if( in_array( $role, $userdata->roles ) ) :
					return array( 'exist' );
				endif;
			endforeach;
			$caps = array( 'do_not_allow' );
		endif;
		
		return $caps;
	}
    
    /** == Habilitation d'accès à la consultation des pages du forum == **/
    public function read( $caps, $cap, $user_id, $args )
    {
        if( user_can( $user_id, 'tify_forum_allowed_user' ) ) :
            $caps = array( 'exist' );
        elseif( ! Options::get( 'forum::private_read' ) ) :
            $caps = array( 'exist' );
        else :
            $caps = array( 'do_not_allow' );
        endif;
        
        return $caps;
    }
    
    /** == Habilitation de modération des sujets == **/
    public function moderate_topics( $caps, $cap, $user_id, $args )
    {
        if( user_can( $user_id, 'administrator' ) ) :
            $caps = array( 'exist' );
        else :
            $caps = array( 'do_not_allow' );
        endif;
        
        return $caps;
    }
    
    /** == Habilitation de modération des contributions == **/
    public function moderate_contribs( $caps, $cap, $user_id, $args )
    {
        if( user_can( $user_id, 'administrator' ) ) :
            $caps = array( 'exist' );
        else :
            $caps = array( 'do_not_allow' );
        endif;
        
        return $caps;
    }
    
    /** == Habilitation de soumission de nouveau sujet == **/
    public function submit_topic( $caps, $cap, $user_id, $args )
    {
        if( user_can( $user_id, 'administrator' ) ) :
            $caps = array( 'exist' );
        elseif( user_can( $user_id, 'tify_forum_allowed_user' ) ) :
            if( User::isActive( $user_id ) ) :
                $caps = array( 'exist' );
            else :
                $caps = array( 'do_not_allow' );
            endif;
        endif;
        
        return $caps;
    }
    
    /** == Habilitation de soumission de contribution == **/
    public function submit_contrib( $caps, $cap, $user_id, $args )
    {
        if( user_can( $user_id, 'administrator' ) || user_can( $user_id, 'tify_forum_allowed_user' ) ) :
            $caps = array( 'exist' );
        else :
            $caps = array( 'do_not_allow' );
        endif;
        
        return $caps;
    }
}