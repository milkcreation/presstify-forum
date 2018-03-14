<?php
namespace tiFy\Plugins\Forum\Admin\Contribution\ListTable;

class ListTable extends \tiFy\Core\Templates\Admin\Model\ListTable\ListTable
{
	/* = DECLENCHEURS = */
	/** == Affichage de l'écran courant == **/
	final public function current_screen( $current_screen )
	{
		wp_enqueue_style( 'tiFyPluginForumAdminContributionListTable', self::tFyAppUrl() .'/ListTable.css', array(), 160603 );
	}
	
	/* = PARAMETRAGE = */
	/** == Définition des messages de notification == **/
	public function set_notices()
	{
		return array(
			'approved'		=> __( 'La contribution est désormais approuvée.', 'tify' ),
			'unapproved'	=> __( 'La contribution est désormais desapprouvée.', 'tify' )	
		);
	}
	
	/** == Définition des vues filtrées == **/
	public function set_views()
	{
		return array(
			array(
				'label'				=> __( 'Toutes', 'tify' ),
				'count'				=> $this->count_items(),
				'remove_query_args'	=> true
			),
			array(
				'label'				=> array( 'singular' => __( 'Approuvée', 'tify' ), 'plural' => __( 'Approuvées', 'tify' ) ),
				'count'				=> $this->count_items( array( 'contrib_approved' => 1 ) ),
				'hide_empty'		=> true,
				'add_query_args'	=> array( 'contrib_approved' => 1 )
			),
			array(
				'label'				=> array( 'singular' => __( 'En attente', 'tify' ), 'plural' => __( 'En attentes', 'tify' ) ),
				'count'				=> $this->count_items( array( 'contrib_approved' => 0 ) ),
				'hide_empty'		=> true,
				'add_query_args'	=> array( 'contrib_approved' => 0 )
			)		
		);
	}
	
	/** == Définition de la liste des colonnes == **/
	public function set_columns()
	{
		return array(
			'cb'				=> $this->get_cb_column_header(),
			'contrib_author'	=> __( 'Auteur', 'tify' ),
			'contrib_date'		=> __( 'Date', 'tify' ),
			'contrib_content'	=> __( 'Contribution', 'tify' ),
			'contrib_topic'	=> __( 'En réponse à', 'tify' )
		);
	}
	
	/** == == **/
	public function set_bulk_actions()
	{
		return array(
			'approve'	=> __( 'Approuver', 'tify' ),
			'unapprove'	=> __( 'Désapprouver', 'tify' )
		);
	}
	
	/** == Définition des actions sur un élément == **/
	public function set_row_actions()
	{
		return array( 
			'edit', 
			'delete',
			'approve' 		=> array(
				'label'			=> __( 'Approuver', 'tify' ),
				'title'			=> __( 'Approuver la contribution', 'tify' ),
				'link_attrs'	=> array( 'style' => 'color:#006505;' ),
				'nonce'			=> $this->get_item_nonce_action( 'approve' )
			),
			'unapprove' 	=> array(
				'label'			=> __( 'Désapprouver', 'tify' ),
				'title'			=> __( 'Désapprouver la contribution', 'tify' ),
				'link_attrs'	=> array( 'style' => 'color:#D98500;' ),	
				'nonce'			=> $this->get_item_nonce_action( 'unapprove' )
			)
		);
	}
	
	/** == Définition de l'ajout automatique des actions de l'élément pour la colonne principale == **/
	public function set_handle_row_actions()
	{
		return false;
	}
		
	/* = TRAITEMENT = */
	/** == Éxecution de l'action - Approuver == **/
	protected function process_bulk_action_approve()
	{
		$item_ids = $this->current_item();	
		
			// Vérification des permissions d'accès
		if( ! wp_verify_nonce( @$_REQUEST['_wpnonce'], 'bulk-'. $this->Plural ) ) :
			check_admin_referer( $this->get_item_nonce_action( 'approve' ) );
		endif;
		
		// Traitement de l'élément
		foreach( (array) $item_ids as $item_id ) :
			$this->db()->handle()->update( $item_id, array( 'contrib_approved' => 1 ) );
		endforeach;
		
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'approved', $sendback );	
		
		wp_redirect( $sendback );
		exit;
	}
	
	/** == Éxecution de l'action - Approuver == **/
	protected function process_bulk_action_unapprove()
	{
		$item_ids = $this->current_item();	
		
			// Vérification des permissions d'accès
		if( ! wp_verify_nonce( @$_REQUEST['_wpnonce'], 'bulk-'. $this->Plural ) ) :
			check_admin_referer( $this->get_item_nonce_action( 'unapprove' ) );
		endif;
		
		// Traitement de l'élément
		foreach( (array) $item_ids as $item_id ) :
			$this->db()->handle()->update( $item_id, array( 'contrib_approved' => 0 ) );
		endforeach;
			
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'unapproved', $sendback );	
		
		wp_redirect( $sendback );
		exit;
	}
	
	/* = AFFICHAGE = */
	/** == == **/
	public function single_row( $item ) 
	{
	?>
		<tr class="<?php echo $item->contrib_approved ? 'approved' : 'unapproved';?>">
	<?php 
		$this->single_row_columns( $item );
	?>
		</tr>
	<?php
	}
	
	/** == COLONNE - Auteur === **/
	public function column_contrib_author( $item )
	{
		if ( $user = get_user_by( 'ID', $item->contrib_user_id ) ) :
			$author = $user->display_name;
			$avatar = get_avatar( $item->contrib_user_id, 32 );
		else :
			$author = __( 'Anonyme', 'tify' );
			$avatar = get_avatar( 0, 32 );
		endif;
		
		$row_actions = array( 'approve', 'unapprove', 'edit', 'delete' );
		if( $item->contrib_approved == 1 ) :
			$row_actions = array_diff( $row_actions , array( 'approve' ) );
		else :	
			$row_actions = array_diff( $row_actions , array( 'unapprove' ) );
		endif;
		
		if( $edit_link = $this->get_item_edit_link( $item, array(), $author, 'row-contrib_author' ) ) :
			return sprintf( '%1$s<strong>%2$s</strong>%3$s', $avatar, $edit_link, $this->get_row_actions( $item, $row_actions ) );
		else :
			return sprintf( '%1$s<strong>%2$s</strong>', $avatar, $author );		
		endif;
	}
	
	/** == COLONNE - Sujet === **/
	public function column_contrib_topic( $item )
	{
		$output = \tiFy\Plugins\Forum\Topic::get( (int) $item->contrib_topic_id )->topic_title;

		return $output;
	}
}