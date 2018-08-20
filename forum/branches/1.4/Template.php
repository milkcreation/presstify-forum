<?php
/*
 * @Overridable
 */
namespace tiFy\Plugins\Forum;

use tiFy\Plugins\Forum\Options;

class Template extends \tiFy\App\Factory
{
    /* = ARGUMENTS = */    
    // Variable de requête
    protected static $QueryVar                = 'tify_forum';
    
    // Template d'affichage courant
    protected static $Template                = null;
        
    // Notification 
    protected static $Notice                = null;
    
    /* = CONSTRUCTEUR = */
    public function __construct()
    {
        parent::__construct();
        
        // Déclencheurs
        add_action( 'wp',                     array( $this, '_wp' ) );
        add_action( 'wp_enqueue_scripts',     array( $this, '_wp_enqueue_scripts' ) );
        add_filter( 'body_class',             array( $this, '_body_class' ) );
        add_filter( 'the_content',             array( $this, '_the_content' ) );
    }
        
    /* = DECLENCHEURS = */
    /** == Après le chargement complet des prérequis == **/
    final public function _wp()
    {
        if( ! $template = static::getTemplate() )
            return;
        
        // Lancement de actions
        $callback = join( '', array_map( 'ucfirst', preg_split( '/_/', $template ) ) );
        if( is_callable( 'tiFy\Plugins\Forum\Actions::'. $callback ) ) :
            call_user_func( 'tiFy\Plugins\Forum\Actions::'. $callback );
        endif;
    }
    
    /** == Mise en file des scripts == **/
    final public function _wp_enqueue_scripts()
    {
        // Bypass
        if( ! self::IsTemplate() )
            return;
        
        tify_control_enqueue( 'notices' );
        tify_control_enqueue( 'table' );
        tify_control_enqueue( 'quicktags_editor' );    
            
        wp_enqueue_script( 'tiFyPluginForumTemplate',     static::tFyAppUrl( get_class() ) ."/Assets/js/Template.js",     array( 'jquery' ), '151202' );
        wp_enqueue_style( 'tiFyPluginForumTemplate',     static::tFyAppUrl( get_class() ) ."/Assets/css/Template.css", array( 'tiFyTheme' ), '151202' );

        switch( static::getTemplate() ) :
            case 'home' :
                
                break;
            case 'login_form' :
                wp_enqueue_style( 'tiFyPluginForumLoginForm', static::tFyAppUrl( get_class() ) ."/Assets/css/LoginForm.css", array(), '160613' );
                break;
            case 'subscribe_form' :
                wp_enqueue_style( 'tiFyPluginForumSubscribeForm', static::tFyAppUrl( get_class() ) ."/Assets/css/SubscribeForm.css", array(), '160613' );
                break;
            case 'user_account' :
                wp_enqueue_style( 'tiFyPluginForumUserAccountForm', static::tFyAppUrl( get_class() ) ."/Assets/css/UserAccountForm.css", array(), '160613' );
                break;
            case 'topic' :
                wp_enqueue_style( 'tiFyPluginForumTopic', static::tFyAppUrl( get_class() ) ."/Assets/css/Topic.css", array(), '160613' );
                break;
        endswitch;
    }
    
    /** == Modification des classe de la balise body == **/
    final public function _body_class( $classes )
    {
        if( $template = static::getTemplate() ) :
            $classes[] = "tiFyPluginForumBody";
            $classes[] = "tiFyPluginForumBody-". $template;
        endif;
                        
        return $classes;
    }
        
    /** == Modification du contenu == **/
    final public function _the_content( $content )
    {
        // Bypass
        if( ! in_the_loop() )
            return $content;
        if( ! is_singular() )
            return $content;        
        if( Options::getHookID() !== get_the_ID() )
            return $content;
            
        $contentConfig = Forum::tFyAppConfig( 'content', '' );
            
        $output = "";
        if( $contentConfig === 'before' )
            $output .= $content; 
        
        $output .= self::content( static::getTemplate() );
        
        if( $contentConfig === 'after' )
            $output .= $content; 
        
            
        return $output;
    }
    
    /* = CONTRÔLEURS = */
    /** == Vérifie si la page courante affiche un gabarit du plugin == **/    
    final public static function IsTemplate( $template = null )
    {        
        if( is_null( $template ) ) :
            return ! empty( static::getTemplate() );
        else :
            return ( $template === static::getTemplate() );
        endif;
    }    
    
    /** == Récupération du template courant == **/
    final public static function getTemplate()
    {
        if( static::$Template ) :
            return static::$Template;
        elseif( isset( $_REQUEST[ static::$QueryVar ] ) ) :
            return static::$Template = esc_attr( $_REQUEST[ static::$QueryVar ] );
        elseif( is_singular() && ( Options::getHookID() === get_the_ID() ) ) :
            return 'home';
        endif;
    }
    
    /** == Récupération de l'url d'un template == **/
    final public static function getTemplateUrl( $template )
    {
        return esc_url( add_query_arg( array( static::$QueryVar => $template ), Forum::getBaseUri() ) );
    }
    
    /** == Définition d'une notification == **/
    final public static function setNotice( $message = '', $type = 'info' )
    {
        return self::$Notice = array(
            'message'           => $message,
            'type'              => $type,
            'class'				=> '',
			'dismissible'		=> false,
        );
    }
        
    /* = NOTIFICATIONS = */
    public static function notice( $args = array() )
    {
        $defaults = array(
            'text'              => static::$Notice['message'], 
            'type'              => static::$Notice['type'],
            'class'				=> '',
			'dismissible'		=> false
        );
        $args = wp_parse_args( $args, $defaults );
        
        return tify_control_notices( $args, false );
    }

    /* = LIENS = */
    /** == Liens vers les pages de template == **/
    final public static function url( $template )
    {
        return is_callable( 'static::url_'. $template ) ? call_user_func( 'static::url_'. $template ) : static::url_default( $template );    
    }
    
    /** == Lien par défaut == **/
    public static function url_default( $template )
    {
        return self::getTemplateUrl( $template );
    }
    
    /* = CONTENU DES PAGES = */
    /** == Contenu d'une page de template == **/
    final static public function content( $template )
    {                    
        return is_callable( 'static::content_'. $template ) ? call_user_func( 'static::content_'. $template ) : static::content_default( $template );
    }
    
    /** == Contenu par défaut == **/
    public static function content_default( $template )
    {
        $output  = "";
        $output .= "<div class=\"tiFyPluginForum\">\n";
        // Entête
        $output .= "\t<header class=\"tiFyPluginForum-Header\">". static::header( $template ) ."</header>\n";    
        // Corps de page
        $output .= "\t<section class=\"tiFyPluginForum-Body\">". static::title( $template ) . ( static::$Notice ? static::notice() : '' ) . static::body( $template ) ."</section>\n";
        // Pied de page
        $output .= "\t<footer class=\"tiFyPluginForum-Footer\">". static::footer( $template ) ."</footer>\n";
        $output .= "</div>\n";
        
        return $output;
    }
        
    /* = ENTÊTE = */
    /** == Entête d'une page de template == **/
    final public static function header( $template )
    {
        return is_callable( 'static::header_'. $template ) ? call_user_func( 'static::header_'. $template ) : static::header_default( $template );    
    }    
    
    /** == Entête par défaut == **/
    public static function header_default( $template )
    {            
        return '';
    }
    
    /* = TITRE DES PAGES = */
    /** == Titre d'une page de template == **/
    final public static function title( $template )
    {
        return is_callable( 'static::title_'. $template ) ? call_user_func( 'static::title_'. $template ) : static::title_default( $template );    
    }
    
    /** == Titre par défaut == **/
    public static function title_default( $template )
    {
        switch( $template) :
            default :
            case '404' :
                $title = "<h2 class=\"tiFyPluginForum-BodyTitle\">". __( 'Page introuvable', 'tify' ) ."</h2>";
                break;
            case 'home' :
                $title = "<h2 class=\"tiFyPluginForum-BodyTitle\">". __( 'Forum de discussion', 'tify' ) ."</h2>";
                break;
            case 'topic' :
                $title = "<h2 class=\"tiFyPluginForum-BodyTitle\">". __( 'Sujet de discussion', 'tify' ) ."</h2>";
                break;
            case 'user_account' :
                    $title = "<h2 class=\"tiFyPluginForum-BodyTitle\">". __( 'Modifier mes paramètres', 'tify' ) ."</h2>";
                break;
            case 'login_form' :
                    $title = "<h2 class=\"tiFyPluginForum-BodyTitle\">". __( 'Authentification', 'tify' ) ."</h2>";
                break;
            case 'subscribe_form' :
                    $title = "<h2 class=\"tiFyPluginForum-BodyTitle\">". __( 'Inscription', 'tify' ) ."</h2>";
                break;
            case 'activate' :
                    $title = "<h2 class=\"tiFyPluginForum-BodyTitle\">". __( 'Activation', 'tify' ) ."</h2>";
                break;
            case 'activation_email' :
                    $title = "<h2 class=\"tiFyPluginForum-BodyTitle\">". __( 'Envoi de l\'email d\'activation', 'tify' ) ."</h2>";
                break;
            case 'unsubscribe' :
                    $title = "<h2 class=\"tiFyPluginForum-BodyTitle\">". __( 'Désinscription', 'tify' ) ."</h2>";
                break;
        endswitch;
        
        return $title;
    }
        
    /* = CORPS DE PAGES = */
    /** == Corps de page d'un template == **/
    final public static function body( $template )
    {                
        return is_callable( 'static::body_'. $template ) ? call_user_func( 'static::body_'. $template ) : static::body_default( $template );
    }    
    
    /** == Corps de page par défaut == **/
    public static function body_default( $template )
    {
        return static::body_404();
    }
    
    /** == Corps de page introuvable == **/
    public static function body_404()
    {
        return "<p>". __( 'Désolé, cette page est malheureusement introuvable.', 'tify' ) ."</p>";
    }
    
    /** == Corps de la page d'accueil === ***/
    public static function body_home()
    {
        $output = "";
        if( ! current_user_can( 'tify_forum_read' ) ) :
            $output .= "<section class=\"tiFyPluginForum-Section tiFyPluginForum-Section--LoginForm\">";
            $output .= "<h3 class=\"tiFyPluginForum-SectionTitle tiFyPluginForum-SectionTitle--LoginForm\">". __( 'Authentification', 'tify' ) ."</h3>";
            $output .= static::body_login_form();
            $output .= "</section>";
            
            $output .= "<section class=\"tiFyPluginForum-Section tiFyPluginForum-Section--SubscribeForm\">";
            $output .= "<h3 class=\"tiFyPluginForum-SectionTitle tiFyPluginForum-SectionTitle--SubscribeForm\">". __( 'Créer un compte', 'tify' ) ."</h3>";
            $output .= static::body_subscribe_form();
            $output .= "</section>";            
        else :        
            $output .= "<section class=\"tiFyPluginForum-Section tiFyPluginForum-Section--TopicList\">";
            $output .= "<h3 class=\"tiFyPluginForum-SectionTitle tiFyPluginForum-SectionTitle--TopicList\">". __( 'Liste des sujets', 'tify' ) ."</h3>";
            $output .= static::body_topic_list();
            $output .= "</section>";            
            
            if( Options::get( 'topic::submit_opened' ) ) :
                if( current_user_can( 'tify_forum_submit_topic' ) ) :
                    $output .= "<section class=\"tiFyPluginForum-Section tiFyPluginForum-Section--TopicList\">";
                    $output .= "<h3 class=\"tiFyPluginForum-SectionTitle tiFyPluginForum-SectionTitle--TopicList\">". __( 'Proposer un sujet de discussion', 'tify' ) ."</h3>";            
                    $output .= static::topic_form();
                    $output .= "</section>";
                else: 
                    $output .= static::notice( array( 'text' => __( 'Vous n\'êtes pas autorisé à proposer de nouveaux sujet de discussion.', 'tify' ), 'type' => 'info' ) ); 
                endif;
            endif;
        endif;
                            
        return $output;    
    }
    
	/** == Corps de page du formulaire d'authentification == **/
	public static function body_login_form()
	{		
		$output  = "";		
		$output .= "<div class=\"tiFyPluginForum-LoginInterface\">\n";
		$output .= "\t<div class=\"tiFyPluginForum-loginForm\">\n";
		$output .= static::login_form();
		$output .= "\t</div>\n";
		$output .= "\t<div class=\"tiFyPluginForum-loginLostPassword\">\n";
		$output .= static::lostpassword_link();
		$output .= "\t</div>\n";
		$output .= "</div>\n";
									
		return $output;	
	}
    
    /** == Corps du formulaire d'inscription == **/
    public static function body_subscribe_form()
    {
        $output = "";    
        $output .= "<div class=\"tiFyPluginForum-SubscribeForm\">". static::subscribe_form() ."</div>";
                                    
        return $output;    
    }
    
    /** == Corps du formulaire de modification du compte utilisateur == **/
    public static function body_user_account()
    {
        $output = "";        
        $output .= static::user_inactive_notice();        
        $output .= "<div class=\"tify_forum-user_account_form\">". static::user_account_form() ."</div>";
                                    
        return $output;    
    }
    
    /** == Affichage de la liste des sujets == **/
    public static function body_topic_list()
    {
        $output = "";                
        $output .= "<div class=\"tiFyPluginForum-TopicList\">". static::topic_list() ."</div>";
                                    
        return $output; 
    }
            
    /** == Corps du contenu d'un sujet === **/
    public static function body_topic()
    {
        $output  = "";
        $output .= "<section class=\"tiFyPluginForum-Section tiFyPluginForum-Section--Topic\">";
        $output .= static::topic_page();
        $output .= "</section>";
        
        $output .= "<section class=\"tiFyPluginForum-Section tiFyPluginForum-Section--TopicContribs\">";
        $output .= "<h3 class=\"tiFyPluginForum-SectionTitle tiFyPluginForum-SectionTitle--TopicContribs\">". __( 'Liste des contributions', 'tify' ) ."</h3>";
        $output .= static::contribution_list();
        $output .= "</section>";
        
        $output .= "<section class=\"tiFyPluginForum-Section tiFyPluginForum-Section--ContribSubmit\">";
        $output .= "<h3 class=\"tiFyPluginForum-SectionTitle tiFyPluginForum-SectionTitle--ContribSubmit\">". __( 'Soumettre une contribution', 'tify' ) ."</h3>";            
        $output .= static::contribution_form();
        $output .= "</section>";
                                            
        return $output;    
    }        
    
    /* = PIED DE PAGE = */
    /** == Pied de page d'un template == **/
    final public static function footer( $template )
    {
        return is_callable( 'static::footer_'. $template ) ? call_user_func( 'static::footer_'. $template ) : static::footer_default( $template );
    }
    
    /** == Pied de page par défaut == **/
    public static function footer_default()
    {    
        return '';
    }
        
    /* = ELEMENTS DE TEMPLATE = */    
    /** == NAVIGATION == **/
    public static function header_navigation()
    {    
        $output  = "";
        $output .= "<hgroup class=\"tify_forum-header_navigation\">\n";
        $output .= "\t<section class=\"tify_forum-header_breadcrumb\">\n";
        $output .= static::breadcrumb();
        $output .= "\t</section>";
        $output .= "\t<section class=\"tify_forum-header_account\">\n";
        if( ! is_user_logged_in() ) :
            $output .= static::subscribe_form_button();
        else :
            $output .= static::user_account_button();
        endif;
        $output .= "\t</section>\n";
        $output .= "\t<section class=\"tify_forum-header_connect\">\n";
        if( ! is_user_logged_in() ) :            
            $output .= "\t\t\t<a href=\"#\">";
            $output .= static::login_form_button();        
            $output .= "\t\t\t</a>";
        else:
            $output .= static::logout_button();
        endif;
        $output .= "\t</section>\n";
        $output .= "</hgroup>\n";
        
        return $output;
    }
    
    /** == Fil d'Ariane == **/
    public static function breadcrumb()
    {
        $output  = "";
        $output .= "<ol class=\"tify_forum-breadcrumb\">\n";
        $output .= "\t<li>\n";
        if( $template !== 'home' )
            $output .= "<a href=\"". get_permalink() ."\" title=\"". __( 'Retour à l\'accueil du forum de discussion', 'tify' ) ."\">". __( 'Accueil', 'tify' ) ."</a>";
        else
            $output .= "<span class=\"active\">". __( 'Accueil', 'tify' ) ."</span>";
        $output .= "\t</li>\n";
                
        switch( $template ) :
            case 'login_form' :
                $output .= "\t<li><span class=\"active\">". __( 'Authentification', 'tify' ) ."</span></li>\n";
                break;
            case 'subscribe_form' :
                $output .= "\t<li><span class=\"active\">". __( 'Inscription', 'tify' ) ."</span></li>\n";
                break;
            case 'user_account' :
                $output .= "\t<li><span class=\"active\">". __( 'Mon compte', 'tify' ) ."</span></li>\n";
                break;
            case 'topic' :                
                $output .= "\t<li><span class=\"active\">". sprintf( __( 'Sujet : %s', 'tify' ), $this->CurrentTopic->topic_title ) ."</span></li>\n";
                break;
        endswitch;
        $output .= "</ol>";
        
        return $output;
    }

    /** == Affichage du bouton d'accès à l'interface d'authentification == **/
    public static function login_form_button( $args = array() )
    {
        $defaults = array(
            'text'    => __( 'S\'authentifier', 'tify' )
        );
        $args = wp_parse_args( $args, $defaults );
                
        $output  = "";
        $output .= "<a href=\"". static::url( 'login_form' ) ."\" title=\"". __( 'Authentification au forum', 'tify' ) ."\" class=\"tify_forum-login_button\">". $args['text'] ."</a>";
        
        return $output;
    }
    
    /** == Affichage du formulaire d'authentification == **/
    public static function login_form( $args = array() )
    {
        return Forum::getController( 'login' )->form( $args );
    }
    
    /** == Affichage du formulaire d'authentification == **/
    public static function login_errors()
    {
        return Forum::getController( 'login' )->formErrors();
    }
    
    /** == Affichage du bouton de récupération de mot de passe oublié == **/
    public static function lostpassword_link( $args = array() )
    {
        return Forum::getController( 'login' )->lostpassword_link( $args );
    }
    
    /** == Url de déconnection == **/
    public static function logout_url()
    {
        return Forum::getController( 'login' )->logout_url();
    }
    
    /** == Affichage du bouton de déconnection == **/
    public static function logout_link( $args = array() )
    {
        return Forum::getController( 'login' )->logout_link( $args );
    }

    /** == Affichage du bouton d'accès au formulaire d'inscription == **/
    public static function subscribe_form_button( $args = array() )
    {
        $defaults = array(
            'text'    => __( 'S\'inscrire', 'tify' )
        );
        $args = wp_parse_args( $args, $defaults );
                
        $output  = "";
        $output .= "<a href=\"". static::url( 'subscribe_form' ) ."\" title=\"". __( 'Inscription au forum', 'tify' ) ."\" class=\"tify_forum-subscribe_button\">". $args['text'] ."</a>";
        
        return $output;
    }
    
    /** == Affichage du formulaire d'inscription == **/
    public static function subscribe_form()
    {
        return tify_form_display( 'tiFyPluginForum_SubscribeForm', false );
    }

    /** == Bouton d'accès aux réglages des paramètres du compte == **/
    public static function user_account_button( $args = array() )
    {
        // Bypass
        if( ! current_user_can( 'tify_forum_allowed_user' ) )
            return;
        
        $defaults = array(
            'text'    => __( 'Modifier mes paramètres', 'tify' )
        );
        $args = wp_parse_args( $args, $defaults );
        
        $output  = "";
        $output .= "<a href=\"". static::url( 'user_account' ) ."\" title=\"". __( 'Modification des paramètres du compte', 'tify' ) ."\" class=\"tify_forum-account_button\">". $args['text'] ."</a>";
        
        return $output;
    }
    
    /** == Affichage du formulaire de modification de données personnel d'un contributeur == **/
    public static function user_account_form()
    {
        return tify_form_display( 'tiFyPluginForum_AccountForm', false );
    }
    
    /** == Affiche un message de notification aux utilisateurs inactifs == **/
    public static function user_inactive_notice()
    {
        if( is_user_logged_in() && ! \tiFy\Plugins\Forum\Forum::isActiveUser() )
            return tify_control_notices( array( 'text' => __( 'Votre compte est en attente d\'activation, vous ne pouvez pas poster de contenu pour l\'instant. Merci de votre compréhension.', 'tify' ), 'type' => 'info' ), false );
    }
    
    /** == Affichage de la liste des sujets == **/
    public static function topic_list()
    {
        global $wpdb;
        
        $QueryTopic = tify_query( 'tiFyForumTopic' );
    
        // Arguments de pagination
        $per_page     = (int) \tiFy\Plugins\Forum\Options::get( 'general::topics_per_page' );
        $start         = ( $paged = (int) get_query_var( 'paged', 0 ) ) ? ( $paged-1 ) * $per_page : 0;
        
        $join = " INNER JOIN {$wpdb->tify_forum_topicmeta} as tm ON ( topic_id = tm.tify_forum_topic_id )";
        
        $where         = " AND tm.meta_key = 'approved' AND tm.meta_value = 1";
        // Conditions de requête
        if( ! current_user_can( 'tify_forum_moderate_topics' ) ) :
            $where     .= " AND (". 
                            "( topic_status = 'publish' ) OR ( topic_author AND topic_author = ". get_current_user_id() ." )".
                        ")";
        endif;
        $limits     =     "LIMIT {$start},{$per_page}";
        $orderby    =     "topic_date DESC";
            
        // Requête de récupération
        $QueryTopic->query_items( compact( 'join', 'where', 'limits', 'orderby' ) );
        
        $datas = array(); $i=0;
        if( $QueryTopic->have_items() ) :
            while( $QueryTopic->have_items() ) : $QueryTopic->the_item();
                // Titre
                $topic_link = add_query_arg( array( 'id' => tify_query_field( 'id' ) ), static::url( 'topic' ) );
                $datas[$i]['title'] = "<a href=\"". $topic_link ."\" title=\"". sprintf( __( 'Consulter le sujet : %s', 'tify' ), tify_query_field( 'title' ) ) ."\">". tify_query_field( 'title' ) ."</a>";
                if( tify_query_field( 'contrib_status' ) !== 'open' )
                    $datas[$i]['title'] .= " - <b class=\"closed\">". __( 'Fermé', 'tify' ) ."</b>";    
                // Réponses    
                $datas[$i]['contribs'] = ( $count = (int) \tiFy\Plugins\Forum\Contribution::count( array( 'contrib_topic_id' => (int) tify_query_field( 'id' ), 'contrib_approved' => 1 ) ) ) ? $count : 0;         
                // Dernière réponse
                if( $last = \tiFy\Plugins\Forum\Contribution::last( (int) tify_query_field( 'id' ) ) ) :
                    $datas[$i]['latest'] = sprintf( __( 'par %s, le %s à %s', 'tify' ), $last->contrib_author, mysql2date( get_option( 'date_format'), $last->contrib_date ), mysql2date( get_option( 'time_format'), $last->contrib_date ) );
                else :
                    $datas[$i]['latest'] = __( 'Il n\'y a pour l\'instant aucune discussion sur ce sujet', 'tify' );
                endif;
                $i++;
            endwhile;
        endif;
        
        $output = tify_control_table(
            array(
                'columns'     => array(
                    'title'             => __( 'Sujet', 'tify' ),
                    'contribs'            => __( 'Réponses', 'tify' ),
                    'latest'            => __( 'Dernière réponse', 'tify' )
                ),
                'datas'        => $datas
            ),
            false
        );
        
        return $output;
    }
    
    /** == Affichage de la liste des sujets == **/
    public static function topic_page()
    {        
        $QueryTopic = tify_query( 'tiFyForumTopic' );
        $QueryTopic->query( array( 'topic_id' => $_REQUEST['id'] ) );
        
        $output = "";
        if( $QueryTopic->have_items() ) :
            while( $QueryTopic->have_items() ) : $QueryTopic->the_item();
                $output .=  "<div class=\"tiFyPluginForum-Item tiFyPluginForum-Item--Topic\">";
                $output .=      "<h3 class=\"tiFyPluginForum-ItemTitle tiFyPluginForum-ItemTitle--Topic\">". $QueryTopic->get_field( 'title' ) ."</h3>";
                $output .=      "<div class=\"tiFyPluginForum-ItemExcerpt tiFyPluginForum-ItemExcerpt--Topic\">". $QueryTopic->get_field( 'excerpt' ) ."</div>";
                $output .=  "</div>";
            endwhile;
        endif;
                
        return $output;
    }

    /** == Pagination des sujets de forum == **/
    public static function topic_pagination( $query )
    {
        return tify_pagination( 
            array(
                'class'    => 'tify_forum-pagination',
                'query'        => $query,
                'paged'        => get_query_var( 'paged', 0 ),
                'per_page'    => (int) \tiFy\Plugins\Forum\Options::get( 'general::topics_per_page' )
            ),
            false
        );
    }
        
    /** == Affichage du formulaire de soumission de nouveau sujet == **/
    public static function topic_form()
    {    
        $output  = "";
        $output .= "<div class=\"tify_forum-topic_form\">\n";
        $output .= "\t<form name=\"tify_forum_topic_form\" method=\"post\" action=\"". add_query_arg( array( 'action' => 'add_topic' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ."\">\n";
        $output .= "\t\t". wp_referer_field( false );
        $output .= "\t\t<p><label>". __( 'Titre', 'tify' ) ."</label><input type=\"text\" value=\"\" name=\"title\"/></p>\n";
        $output .= "\t\t\t<label>". __( 'Description', 'tify' ) ."</label>";
        $output .= "\t\t\t". tify_control_quicktags_editor( array( 'id' => 'tify_forum_topic_form', 'name' => 'excerpt' ), false );
        $output .= "\t\t<button type=\"submit\" name=\"tify_forum_topic_form-submit\" >". __( 'Soumettre', 'tify' ) ."</button>\n";
        $output .= "\t</form>\n";
        $output .= "</div>\n";        
        
        return $output;
    }

    /** == Affichage de la liste des contributions == **/    
    public static function contribution_list()
    {
        $CurrentTopic = ( ! empty( $_REQUEST['id'] ) ) ? \tiFy\Plugins\Forum\Topic::get( (int) $_REQUEST['id'] ) : null;
        
        $QueryContrib = tify_query( 'tiFyForumContribution' );
        
        // Arguments de pagination
        $per_page     = (int) \tiFy\Plugins\Forum\Options::get( 'general::contribs_per_page' );
        $start         = ( $paged = (int) get_query_var( 'paged', 0 ) ) ? ( $paged-1 ) * $per_page : 0;
        
        // Conditions de requête
        $where         =     "AND contrib_topic_id = {$CurrentTopic->topic_id}";
        if( ! current_user_can( 'tify_forum_moderate_contribs' ) ) :
            $where     .= " AND (". 
                            "( contrib_approved = 1 ) OR ( contrib_approved = 0 AND contrib_user_id AND contrib_user_id = ". get_current_user_id() ." )".
                        ")";
        endif;
        $limits     =     "LIMIT {$start},{$per_page}";
        $orderby    =     "contrib_date DESC";
        
        // Requête de récupération
        $QueryContrib->query_items( compact( 'where', 'limits', 'orderby' ) );
        
        $output  = "";
        if( $QueryContrib->have_items() ) :            
            $output .= "<div class=\"tify_forum-contrib_list\">\n";
            $output .= "\t<ol>\n";
            while( $QueryContrib->have_items() ) : $QueryContrib->the_item();
                $output .= "\t\t<li id=\"tify_forum-contrib". tify_query_field( 'id' ) ."\">";
                $output .= "\t\t\t<ul>";
                $output .= "\t\t\t\t<li class=\"author\">". tify_query_field( 'author' ) ."</li>";
                $output .= "\t\t\t\t<li class=\"date\">". sprintf( __( 'le %s', 'tify' ), mysql2date( get_option( 'date_format' ).' - '. get_option( 'time_format' ), tify_query_field( 'date' ) ) ) ."</li>";
                $output .= "\t\t\t\t<li>". tify_query_field( 'content' ) ."</li>";
                if( ! tify_query_field( 'approved' ) )
                    $output .= "\t\t\t\t<li class=\"unapproved\">". __( 'En attente de validation', 'tify' ) ."</li>";
                $output .= "\t\t\t</ul>";
                $output .= "\t\t</li>\n";
            endwhile;
            $output .= "\t</ol>\n";
            $output .= "</div>\n";
        else :
            $output .= tify_control_notices( array( 'text' => __( 'Il n\'y a aucune discussion sur ce sujet pour l\'instant.', 'tify' ), 'type' => 'info' ), false );
        endif;
        
        return $output;
    }
    
    /** ==  Pagination des contributions aux sujets de forum == **/
    public static function contribution_pagination( $query )
    {
        return tify_pagination( 
            array(
                'class'        => 'tify_forum-pagination',
                'query'        => $query,
                'paged'        => get_query_var( 'paged', 0 ),
                'per_page'    => (int) \tiFy\Plugins\Forum\Options::get( 'general::contribs_per_page' )
            ),
            false
        );
    }
    
    /** == Formulaire de contribution == **/
    public static function contribution_form() 
    {
        $CurrentTopic = ( ! empty( $_REQUEST['id'] ) ) ? \tiFy\Plugins\Forum\Topic::get( (int) $_REQUEST['id'] ) : null;
        $output  = "";
        if( $CurrentTopic->topic_contrib_status === 'open' ) :
            if( current_user_can( 'tify_forum_submit_contrib' ) ) :           
                $output .=  "<div class=\"tiFyPluginForum-ContribSubmit\">";
                if( isset( $_REQUEST['success'] ) ) :
                    $output .= tify_control_notices( array( 'text' => __( 'Votre contribution a bien été enregistrée, merci de votre participation à ce sujet de discussion.', 'tify' ), 'type' => 'info' ) );
                endif;
                $output .=      "<form method=\"post\" name=\"tiFyPluginForum-ContribForm\" action=\"". add_query_arg( array( 'action' => 'add_contribution' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ."\">\n";
                $output .=          "<input type=\"hidden\" name=\"contrib_topic_id\" value=\"". $CurrentTopic->topic_id ."\" />\n";
                $output .=          wp_referer_field( false );
                $output .=          tify_control_quicktags_editor( array( 'id' => 'tify_forum_contrib-'. $_REQUEST['id'], 'name' => 'content' ), false );
                $output .=          "<button type=\"submit\" name=\"tiFyPluginForum-ContribFormHandle\">". __( 'Contribuer', 'tify' ) ."</button>\n";
                $output .=      "</form>\n";
                $output .=  "</div>\n"; 
            else: 
                $output .= static::notice( array( 'text' => __( 'Vous n\'êtes pas autorisé à proposer de nouveaux sujet de discussion.', 'tify' ), 'type' => 'info' ) ); 
            endif;
        else :
            $output .= static::notice( array( 'text' => __( 'Le sujet de discussion est actuellement fermé aux contributions.', 'tify' ), 'type' => 'info' ) );
        endif;            
        
        return $output;
    }
    
    /** == Notification du formulaire de contribution == **/
    public function contribution_form_error()
    {
        $output = "";    
        $output .=    "<ul>";
        if( is_wp_error( $this->contribution_form_error ) )    
            foreach( $this->contribution_form_error->get_messages() as $msg )    
                $output .= "<ol>{$msg}</ol>";    
        $output .= "</ul>";    
        
        return $output;
    }
}