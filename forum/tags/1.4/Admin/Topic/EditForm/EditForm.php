<?php
namespace tiFy\Plugins\Forum\Admin\Topic\EditForm;

class EditForm extends \tiFy\Core\Templates\Admin\Model\EditForm\EditForm
{
	/* = DECLENCHEURS = */
	/** == Chargement de l'écran courant == **/
	final public function current_screen( $current_screen )
	{
		tify_control_enqueue( 'quicktags_editor' );
	}
	
	/* = TRAITEMENT = */
	/** == Traitement des données de requete == **/
	protected function parse_postdata( $postdata )
	{		
		$postdata = wp_unslash( $postdata );
	    $postdata['topic_author'] = get_current_user_id();
		
		// Dates de création
		if( ! isset( $postdata['topic_date'] ) || ( $postdata['topic_date'] === '0000-00-00 00:00:00' ) )
			$postdata['topic_date'] = current_time( 'mysql', false );
		if( ! isset( $postdata['topic_date_gmt'] ) || ( $postdata['topic_date_gmt'] === '0000-00-00 00:00:00' ) )
			$postdata['topic_date_gmt'] = current_time( 'mysql', true );
		
		// Extrait
		if( isset( $postdata['topic_excerpt'] ) )
			$postdata['topic_excerpt'] = wp_unslash( $postdata['topic_excerpt'] );
			
		// Dates de modification
		$postdata['topic_modified'] = current_time( 'mysql', false );
		$postdata['topic_modified_gmt'] = current_time( 'mysql', false );
		
		$postdata['item_meta']['approved'] = 1;
			
		return $postdata;
	}
	
	/* = VUES = */
	/** == Champs cachés == **/
	public function hidden_fields()
	{
	?>
		<input type="hidden" name="topic_date" value="<?php echo $this->item->topic_date;?>" />
		<input type="hidden" name="topic_date_gmt" value="<?php echo $this->item->topic_date_gmt;?>" />
	<?php 	
	}
	
	/** == Formulaire d'édition == **/
	public function form(){
	?>
		<input type="text" id="title" name="topic_title" value="<?php echo $this->item->topic_title;?>" placeholder="<?php _e( 'Intitulé du sujet', 'tify' );?>">						
		<?php 
			tify_control_quicktags_editor(
			array( 
					'id' 	=> 'content', 
					'name' 	=> 'topic_excerpt', 
					'value' => $this->item->topic_excerpt
				) 
			);
	}
}