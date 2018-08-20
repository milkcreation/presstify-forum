<?php
namespace tiFy\Plugins\Forum;

class Options
{
    /* = ARGUMENTS = */
    /** == Liste des options par defaut == **/    
    private static $Defaults        = array(        
        'general'                       => array( 
            'require_name_email'            => 1, 
            'contrib_registration'          => 1, 
            'thread_contribs'               => 0, 
            'thread_contribs_depth'         => 5, 
            'page_contribs'                 => 1, 
            'contribs_per_page'             => 50, 
            'default_contribs_page'         => 'newest',
            'contrib_order'                 => 'desc',
            'topics_per_page'               => 20 
        ),
        'forum'                         => array(
            'private_read'                  => 0  
        ),
        'topic'                         => array(
            'submit_opened'                 => 1,
            'moderate'                      => 1 
        ),
        'mailing'                       => array( 
            'contribs_notify'               => 0, 
            'moderation_notify'             => 0 
        ),
        'moderation'                    => array( 
            'contrib_moderation'            => 1,
            'contrib_whitelist'             => 1 
        ),
        'contributor'                   => array(
            'double_optin'                  => 1,
            'moderate_account_activation'   => 1
        ),
        'contrib'                       => array(
            'blacklist_keys'                => array()
        )
    );
    
    // Configuration des forums
    private static $Options             = null;
    
    // ID de la page d'accroche
    private static $HookID                 = null;
    
    // Url de la page d'accroche
    private static $HookPermalink        = null;    
        
    /* = CONTRÔLEURS = */
    /** == Récupération de la liste complète des options (à l'initialisation) == **/
    private static function getAll()
    {    
        $stored = get_option( 'tify_forum_options', array() );
        $options = array();
        
        foreach( (array) self::$Defaults as $k => $v  ) :
            
            if( is_array( $v ) ) :
                foreach( (array) $v as $i => $j ) :
                    $options[$k][$i] = isset( $stored[$k][$i] ) ? $stored[$k][$i] : $j;
                endforeach;
            else :
                $options[$k] = isset( $stored[$k] ) ? $stored[$k] : $v;
            endif;
        endforeach;

        self::$Options = $options;
    }
    
    /** == Récupération des options == **/
    public static function get( $option = null )
    {
        if( ! self::$Options )
            self::getAll();
        
        if( ! $option )
            return self::$Options;
        
        if( ! preg_match( '/::/', $option ) ) :
            return isset( self::$Options[$option] ) ? self::$Options[$option] : null;
        else :
            list( $k, $i ) = preg_split( '/::/', $option, 2 );
            return isset( self::$Options[$k][$i] ) ? self::$Options[$k][$i] : null;
        endif;
    }
    
    /** == ID de la page d'accroche == **/
    public static function getHookID()
    {
        if( is_null( self::$HookID ) )
            self::$HookID = (int) get_option( 'page_for_tify_forum', Forum::tFyAppConfig( 'hook_id' ) );
        
        return self::$HookID;
    }
    
    /** == Permaliens de la page d'accroche == **/
    public static function getHookPermalink()
    {
        if( is_null( self::$HookPermalink ) )
            self::$HookPermalink = \get_permalink( self::getHookID() );
        
        return self::$HookPermalink;
    }
}