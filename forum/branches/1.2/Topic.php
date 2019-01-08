<?php
namespace tiFy\Plugins\Forum;

class Topic
{	
	/* = CONTROLEUR = */
	/** == Récupération d'un sujet de forum == **/
	public static function get( $topic_id )
	{
		return tify_db_get( 'tiFyForumTopic' )->select()->row_by_id( (int) $topic_id );
	}
	
	/** == Récupération d'une liste de contribution == **/
	public static function getList( $args = array() )
	{
		return tify_db_get( 'tiFyForumTopic' )->select()->rows( $args );
	}
	/** == Récupération d'une contribution == **/
	public static function count( $args = array() )
	{
		return tify_db_get( 'tiFyForumTopic' )->select()->count( $args );
	}
	
	/** == Mise à jour du calcul des contributions == **/
	public static function updateCountNow( $topic_id ) 
	{
		global $wpdb;
		
		$topic_id = (int)  $topic_id;
		if ( !  $topic_id )
			return false;

		if ( ! $topic = self::get( $topic_id ) )
			return false;
	
		$old = (int) $topic->topic_contrib_count;
		$new = (int) $wpdb->get_var( 
			$wpdb->prepare( 
				"SELECT COUNT(*) FROM {$wpdb->tify_forum_contribution} WHERE contrib_topic_id = %d AND contrib_approved = '1'", 
				$topic_id 
			) 
		);
		tify_db_get( 'tiFyForumTopic' )->handle()->update( $topic_id, array( 'topic_contrib_count' => $new ) );

		do_action( 'tify_forum_update_contrib_count', $topic_id, $new, $old );
	
		return true;
	}	
	
	/** == Insertion d'un nouveau sujet en base de données == **/
	public static function insert( $topicdata ) 
	{
		$data = wp_unslash( $topicdata );
	
		$topic_author       	= ! isset( $data['topic_author'] )      ? '' : $data['topic_author'];
	
		$topic_date     		= ! isset( $data['topic_date'] )     	? current_time( 'mysql' ) 			: $data['topic_date'];
		$topic_date_gmt 		= ! isset( $data['topic_date_gmt'] ) 	? get_gmt_from_date( $topic_date ) 	: $data['topic_date_gmt'];

		$topic_title 			= ! isset( $data['topic_title'] )  		? '' : $data['topic_title'];
		$topic_content  		= ! isset( $data['topic_content'] )  	? '' : $data['topic_content'];
		$topic_excerpt  		= ! isset( $data['topic_excerpt'] )  	? '' : $data['topic_excerpt'];
		
		$topic_status   		= ! isset( $data['topic_status'] )   		? 'waiting'	: $data['topic_status'];
		$topic_contrib_status	= ! isset( $data['topic_contrib_status'] )  ? 'closed'	: $data['topic_contrib_status'];
		
		$topic_parent   		= ! isset( $data['topic_parent'] )   	? 0  : $data['topic_parent'];
	
		$compacted = compact( 'topic_author', 'topic_date', 'topic_date_gmt', 'topic_title', 'topic_content', 'topic_excerpt', 'topic_parent' );
		
		if ( ! $topic_id = tify_db_get( 'tiFyForumTopic' )->handle()->create( $compacted ) )
			return false;
			
		$topic = self::get( $topic_id );
	
		if( isset( $topicdata['topic_meta'] ) && is_array( $topicdata['topic_meta'] ) ) :
			foreach ( $topicdata['topic_meta'] as $meta_key => $meta_value ) :
				tify_db_get( 'tiFyForumTopic' )->meta()->add( $topic->topic_id, $meta_key, $meta_value, true );
			endforeach;
		endif;
	
		do_action( 'tify_forum_insert_topic', $topic_id, $topic );
	
		return $topic_id;
	}
		
	/** == Filtrage des données d'un sujet de forum == **/
	public static function filter( $topicdata ) 
	{
		if ( isset( $topicdata['topic_author'] ) )
			$topicdata['topic_author'] = apply_filters( 'pre_user_id', $topicdata['topic_author'] );
	
		$topicdata['topic_title'] 	= apply_filters( 'tify_forum_pre_topic_title', 		$topicdata['topic_title'] );
		$topicdata['topic_content'] = apply_filters( 'tify_forum_pre_topic_content', 	$topicdata['topic_content'] );
		$topicdata['topic_excerpt'] = apply_filters( 'tify_forum_pre_topic_excerpt', 	$topicdata['topic_excerpt'] );

		return $topicdata;
	}
	
    /** == Permission d'enregistrement de contribution == **/
	public static function allow( $topicdata ) 
	{	
		// Vérification des doublons
		$dupe_id = tify_db_get( 'tiFyForumTopic' )->select()->cell( null, array( 'title' => $topicdata['topic_title'] ) );

		$dupe_id = apply_filters( 'tify_forum_duplicate_topic_id', $dupe_id, $topicdata );

		if ( $dupe_id ) :
			do_action( 'tify_forum_topic_duplicate_trigger', $topicdata );
			if ( defined( 'DOING_AJAX' ) )
				die( __( 'Il semblerait qu\'un sujet identique existe déjà.', 'tify' ) );
			
			wp_die( __(  'Il semblerait qu\'un sujet identique existe déjà.', 'tify' ), 409 );
		endif;
        
		$approved = Options::get( 'topic::moderate' ) ? -1 : 1;		
		$approved = apply_filters( 'tify_forum_pre_contrib_approved', $approved, $topicdata );
		
		return $approved;
	}
}