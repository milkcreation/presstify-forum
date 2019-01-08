<?php
namespace tiFy\Plugins\Forum\Admin\Contributor\ListUser;

class ListUser extends \tiFy\Core\Templates\Admin\Model\ListUser\ListUser
{
    /* = PARAMETRAGES = */
    /** == Définition des messages de notification == **/
    public function set_notices()
    {
        return array(
            'activated'        => __( 'Le contributeur a été activé.', 'tify' ),
            'unactivated'    => __( 'Le contributeur a été désactivé.', 'tify' )    
        );
    }
    
    /** == Définition des colonnes == **/
    public function set_columns()
    {
        return array(
            'cb'                => $this->get_cb_column_header(),
            'user_login'         => __( 'Username' ),
            'display_name'        => __( 'Nom', 'tify' ),
            'user_email'        => __( 'E-mail', 'tify' ),
            'user_registered'    => __( 'Enregistrement', 'tify' )
        );
    }
    
    /** == Définition des actions groupées == **/
    public function set_bulk_actions()
    {
        return array(
            'active'    => __( 'Activer', 'tify' ),
            'unactive'    => __( 'Désactiver', 'tify' )    
        );
    }
    
    /** == Définition des actions sur un élément == **/
    public function set_row_actions()
    {
        return array(
            'edit',                
            'delete',
            'active'    => array(
                'label'            => __( 'Activer', 'tify' ),
                'title'            => __( 'Activer le contributeur', 'tify' ),
                'link_attrs'    => array( 'style' => 'color:#006505;' ),
                'nonce'            => $this->get_item_nonce_action( 'active' )
            ),
            'unactive'    => array(
                'label'            => __( 'Désactiver', 'tify' ),
                'title'            => __( 'Activer le contributeur', 'tify' ),
                'link_attrs'    => array( 'style' => 'color:#D98500;' ),
                'nonce'            => $this->get_item_nonce_action( 'unactive' )
            )
        );
    }
    
    /** == Définition de l'ajout automatique des actions de l'élément pour la colonne principale == **/
    public function set_handle_row_actions()
    {
        return false;
    }
    
    /** == Récupération de la liste des rôles concernés par la vue == **/
    public function set_roles()
    {
        return \tiFy\Plugins\Forum\Forum::getRoleNames();
    }
    
    /* = TRAITEMENT = */
    /** == Éxecution de l'action - Activer un utilisateur == **/
    protected function process_bulk_action_active()
    {
        $item_ids = $this->current_item();
        
        // Vérification des permissions d'accès
        if( ! wp_verify_nonce( @$_REQUEST['_wpnonce'], 'bulk-'. $this->Plural ) ) :
            check_admin_referer( $this->get_item_nonce_action( 'active' ) );
        endif;
        
        // Traitement de l'élément
        foreach( (array) $item_ids as $item_id ) :    
            update_user_option( $item_id, 'tify_forum_status', 'activated' );
        endforeach;
            
        // Traitement de la redirection
        $sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
        $sendback = add_query_arg( 'message', 'activated', $sendback );    
        
        wp_redirect( $sendback );
        exit;
    }
    
    /** == Éxecution de l'action - Désactiver un utilisateur == **/
    protected function process_bulk_action_unactive()
    {
        $item_ids = $this->current_item();
        
        // Vérification des permissions d'accès
        if( ! wp_verify_nonce( @$_REQUEST['_wpnonce'], 'bulk-'. $this->Plural ) ) :
            check_admin_referer( $this->get_item_nonce_action( 'unactive' ) );
        endif;
        
        // Traitement de l'élément
        foreach( (array) $item_ids as $item_id ) :    
            update_user_option( $item_id, 'tify_forum_status', 'disabled' );
        endforeach;
        
        // Traitement de la redirection
        $sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
        $sendback = add_query_arg( 'message', 'unactivated', $sendback );    
        
        wp_redirect( $sendback );
        exit;
    }
    
    /* = AFFICHAGE = */
    /** == == **/
    public function single_row( $item ) 
    {
    ?>
        <tr class="<?php echo get_user_option( 'tify_forum_status', $item->ID ) === 'activated' ? '' : 'highlighted-error';?>">
    <?php 
        $this->single_row_columns( $item );
    ?>
        </tr>
    <?php
    }
    
    /* = COLONNES = */
    /** == Login == **/
    public function column_user_login( $item )
    {
        $row_actions = array( 'active', 'unactive', 'edit', 'delete' );
        $avatar = get_avatar( $item->ID, 32 );
        
        switch( get_user_option( 'tify_forum_status', $item->ID ) ) :
            default:
            case 'disabled' :
                $row_actions = array_diff( $row_actions , array( 'unactive' ) );
                break;
            case 'activated' :
                $row_actions = array_diff( $row_actions , array( 'active' ) );
                break;
        endswitch;
        
        if( $edit_link = $this->get_item_edit_link( $item, array(), $item->user_login, 'row-user' ) ) :
            return sprintf( '%1$s<strong>%2$s</strong>%3$s', $avatar, $edit_link, $this->get_row_actions( $item, $row_actions ) );
        else :
            return sprintf( '%1$<strong>s%2$s</strong>', $avatar, $item->user_login );        
        endif;
    }
}