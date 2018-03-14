<?php
namespace tiFy\Plugins\Forum;

class Handle extends \tiFy\App\Factory
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $tFyAppActions				= array(
		'wp'		
	); 
		
	/* = DECLENCHEURS = */
	/** == Initialisation globale == **/
	final public function wp()
	{
		if( ! is_singular() || ( \tiFy\Plugins\Forum\Options::getHookID() !== get_the_ID() ) || ! isset( $_REQUEST['action'] ) )
			return;
			
		switch( $_REQUEST['action'] ) :
			default : 
				return;
				break;
			case 'add_topic' :
				$topic = $this->handle_topic_submission( wp_unslash( $_POST ) );
				if( is_wp_error( $topic ) ) :
					wp_die( $topic->get_error_message(), $topic->get_error_code(), $topic->get_error_data() );
				else :
					$location = add_query_arg( array( 'success' => true ), wp_get_referer() );
					wp_safe_redirect( $location );
				endif;
				break;
		 	case 'add_contribution' :
				$contrib = $this->handle_contrib_submission( wp_unslash( $_POST ) );
				if( is_wp_error( $contrib ) ) :
					wp_die( $contrib->get_error_message(), $contrib->get_error_code(), $contrib->get_error_data() );
				else :
					$location = add_query_arg( array( 'success' => true ), wp_get_referer() );
					wp_safe_redirect( $location );
				endif;
				exit;
				break;			
		endswitch;
	}
	
	/* = CONTROLEUR = */
	/** == Traitement de la soumission d'un nouveau sujet == **/
	private function handle_topic_submission( $topic_data )
	{
		$topic_parent = 0;
		$topic_author = 0;
		$topic_title = $topic_excerpt = $topic_content = null;
		
		// Traitement des variables de soumission de formulaire
		if ( isset( $topic_data['topic_parent'] ) )
			$topic_parent = (int) $topic_data['topic_parent'];

		if ( isset( $topic_data['author'] ) )
			$topic_author = (int) $topic_data['author'];
		
		if ( isset( $topic_data['excerpt'] ) && is_string( $topic_data['excerpt'] ) )
			$topic_excerpt = trim( $topic_data['excerpt'] );	
			
		if ( isset( $topic_data['content'] ) && is_string( $topic_data['content'] ) )
			$topic_content = trim( $topic_data['content'] );
		
		if ( isset( $topic_data['title'] ) && is_string( $topic_data['title'] ) )
			$topic_title = trim( $topic_data['title'] );
			
		// Vérification de contributeur
		$user = wp_get_current_user();
		if( $user->exists() ) :
			if ( empty( $user->display_name ) ) 
				$user->display_name = $user->user_login;

			$topic_author       = $user->ID;
			
			if ( current_user_can( 'unfiltered_html' ) ) :
				if ( ! isset( $topic_data['_wp_unfiltered_html_contrib'] )
					|| ! wp_verify_nonce( $contrib_data['_wp_unfiltered_html_contrib'], 'unfiltered-html-contrib_'. $contrib_topic_id )
				) :
					kses_remove_filters(); // start with a clean slate
					kses_init_filters(); // set up the filters
				endif;
			endif;
		else :
			if ( Options::get( 'topic::contrib_registration' ) ) 
				return new \WP_Error( 'not_logged_in', __( 'Désolé, Vous devez impérativement être authentifié pour soumettre un sujet de forum.', 'tify' ), 403 );
		endif;
		
		$topicdata = compact(
			'topic_author',
			'topic_excerpt',
			'topic_content',
			'topic_title',	
			'topic_parent'
		);
			
		$topic_id = $this->new_topic( wp_slash( $topicdata ) );
		
		if ( ! $topic_id )
			return new \WP_Error( 'tify_forum_topic_save_error', __( '<strong>ERREUR</strong>: Malheureusement, votre sujet n\'a pas pu être enregistrée. Merci de rééssayer plus tard.', 'tify' ), 500 );
	
		return Topic::get( $topic_id );
	}	
	
	/** == Sauvegarde d'un nouveau sujet == **/
	private function new_topic( $topicdata ) 
	{		
		// Pré-Formatage des données
		$topicdata = apply_filters( 'preprocess_tify_forum_topic', $topicdata );
				
		// Formatage des données de contribution
		/// Forum en relation
		$topicdata['topic_parent'] = (int) $topicdata['topic_parent'];
		
		/// Auteur de la contribution
		$topicdata['topic_author'] = (int) $topicdata['topic_author'];
	
		/// Date de soumission de la contribution
		if ( empty( $topicdata['topic_date'] ) ) 
			$topicdata['topic_date'] = current_time('mysql');	
		if ( empty( $topicdata['topic_date_gmt'] ) )
			$topicdata['topic_date_gmt'] = current_time( 'mysql', 1 );

		// Filtrage des données de soumission
		$topicdata = Topic::filter( $topicdata );
		
		// Définition du statut de moderation
		$topicdata['topic_meta']['approved'] = Topic::allow( $topicdata );

		// Insertion en base de donnée de la contribution
		$topic_id = Topic::insert( $topicdata );
				
		do_action( 'tify_forum_topic_post', $topic_id /*, $topicdata['topic_approved']*/ );
	
		return $topic_id;
	}	
	
	/** == Traitement de la soumission d'une nouvelle contribution == **/
	private function handle_contrib_submission( $contrib_data )
	{		
		$contrib_topic_id = $contrib_parent = 0;
		$contrib_author = $contrib_author_email = $contrib_author_url = $contrib_content = $_wp_unfiltered_html_contrib = null;
		
		// Traitement des variables de soumission de formulaire
		if ( isset( $contrib_data['contrib_topic_id'] ) )
			$contrib_topic_id = (int) $contrib_data['contrib_topic_id'];

		if ( isset( $contrib_data['author'] ) && is_string( $contrib_data['author'] ) )
			$contrib_author = trim( strip_tags( $contrib_data['author'] ) );

		if ( isset( $contrib_data['email'] ) && is_string( $contrib_data['email'] ) ) 
			$contrib_author_email = trim( $contrib_data['email'] );
	
		if ( isset( $contrib_data['url'] ) && is_string( $contrib_data['url'] ) ) 
			$contrib_author_url = trim( $contrib_data['url'] );
			
		if ( isset( $contrib_data['content'] ) && is_string( $contrib_data['content'] ) )
			$contrib_content = trim( $contrib_data['content'] );

		if ( isset( $contrib_data['contrib_parent'] ) )
			$contrib_parent = absint( $contrib_data['contrib_parent'] );

		if ( isset( $contrib_data['_wp_unfiltered_html_contrib'] ) && is_string( $contrib_data['_wp_unfiltered_html_contrib'] ) )
			$_wp_unfiltered_html_contrib = trim( $contrib_data['_wp_unfiltered_html_contrib'] );
	
		// Récupération des données du sujet relatif
		$topic = Topic::get( $contrib_topic_id );
	
		if ( empty( $topic->topic_contrib_status ) ) :
			do_action( 'tify_forum_contrib_id_not_found', $contrib_topic_id );	
			return new \WP_Error( 'tify_forum_contrib_id_not_found' );	
		endif;
			
		/**
		 * Gestion de l'accès privé au sujet de forum
		 * @todo
		 * ------------------------------------------------------------------------------------------
		 * 	$status = get_post_status( $post );
		 * 	if ( ( 'private' == $status ) && ! current_user_can( 'read_tify_forum_topic', $contrib_topic_id ) )
		 * 		return new WP_Error( 'tify_forum_contrib_id_not_found' );
		 */
		
		//$status_obj = get_post_status_object( $status );
	
		if ( $topic->topic_contrib_status !== 'open' ) :
			do_action( 'tify_forum_contrib_closed', $contrib_topic_id );	
			return new \WP_Error( 'tify_forum_contrib_closed', __( 'Désolé, les contributions pour ce sujet sont actuellement fermées.', 'tify' ), 403 );	
		elseif ( 'trash' == $topic->topic_status ) :
			do_action( 'tify_forum_contrib_on_trash', $contrib_topic_id );	
			return new \WP_Error( 'tify_forum_contrib_on_trash' );		
		/**
		 *	elseif ( ! $status_obj->public && ! $status_obj->private ) :
		 *		do_action( 'tify_forum_contrib_on_draft', $contrib_topic_id );	
		 *		return new WP_Error( 'tify_forum_contrib_on_draft' );	
		 *	elseif ( post_password_required( $contrib_topic_id ) ) :
		 *		do_action( 'tify_forum_contrib_on_password_protected', $contrib_topic_id );	
		 *		return new WP_Error( 'tify_forum_contrib_on_password_protected' );
		 */
		else :
			do_action( 'tify_forum_contrib_pre_handle', $contrib_topic_id );	
		endif;
		
		// Vérification de contributeur
		$user = wp_get_current_user();
		if ( $user->exists() ) :
			if ( empty( $user->display_name ) ) 
				$user->display_name = $user->user_login;

			$contrib_author       = $user->display_name;
			$contrib_author_email = $user->user_email;
			$contrib_author_url   = $user->user_url;
			$contrib_user_id      = $user->ID;
			
			if ( current_user_can( 'unfiltered_html' ) ) :
				if ( ! isset( $contrib_data['_wp_unfiltered_html_contrib'] )
					|| ! wp_verify_nonce( $contrib_data['_wp_unfiltered_html_contrib'], 'unfiltered-html-contrib_'. $contrib_topic_id )
				) :
					kses_remove_filters(); // start with a clean slate
					kses_init_filters(); // set up the filters
				endif;
			endif;
		else :
			if ( Options::get( 'general::contrib_registration' ) ) 
				return new \WP_Error( 'not_logged_in', __( 'Désolé, Vous devez impérativement être authentifié pour soumettre une contribution au sujet de forum.', 'tify' ), 403 );
		endif;
	
		$contrib_type = '';
	
		if ( Options::get( 'global::require_name_email' ) && ! $user->exists() ) :
			if ( 6 > strlen( $contrib_author_email ) || '' == $contrib_author ) 
				return new \WP_Error( 'tify_forum_contrib_require_name_email', __( '<strong>ERREUR</strong>: Merci de renseigner les champs requis.', 'tify' ), 200 );
			elseif ( ! is_email( $contrib_author_email ) ) 
				return new \WP_Error( 'tify_forum_contrib_require_valid_email', __( '<strong>ERROR</strong>: L\'adresse email fournie n\'est pas valide.', 'tify' ), 200 );
		endif;
	
		if ( '' == $contrib_content ) 
			return new \WP_Error( 'tify_forum_contrib_require_valid_content', __( '<strong>ERREUR</strong>: Le contenu de votre contribution est introuvable.', 'tify' ), 200 );
	
		$contribdata = compact(
			'contrib_topic_id',
			'contrib_author',
			'contrib_author_email',
			'contrib_author_url',
			'contrib_content',
			'contrib_type',
			'contrib_parent',
			'contrib_user_id'
		);
		
		$contrib_id = $this->new_contrib( wp_slash( $contribdata ) );
		if ( ! $contrib_id )
			return new WP_Error( 'tify_forum_contrib_save_error', __( '<strong>ERREUR</strong>: Malheureusement, votre contribution n\'a pas pu être enregistrée. Merci de rééssayer plus tard.', 'tify' ), 500 );
	
		return Contribution::get( $contrib_id );	
	}

	/** == Sauvegarde d'une nouvelle contribution == **/
	private function new_contrib( $contribdata ) 
	{		
		if ( isset( $contribdata['user_ID'] ) )
			$contribdata['contrib_user_id'] = $contribdata['user_ID'] = (int) $contribdata['user_ID'];
	
		$prefiltered_user_id = ( isset( $contribdata['contrib_user_id'] ) ) ? (int) $contribdata['contrib_user_id'] : 0;

		// Pré-Formatage des données de contribution
		$contribdata = apply_filters( 'preprocess_tify_forum_contrib', $contribdata );
				
		// Formatage des données de contribution
		/// Sujet de forum en relation
		$contribdata['contrib_topic_id'] = (int) $contribdata['contrib_topic_id'];
		/// Auteur de la contribution
		if ( isset( $contribdata['user_ID'] ) && $prefiltered_user_id !== (int) $contribdata['user_ID'] )
			$contribdata['contrib_user_id'] = $contribdata['user_ID'] = (int) $contribdata['user_ID'];
		elseif ( isset( $contribdata['contrib_user_id'] ) )
			$contribdata['contrib_user_id'] = (int) $contribdata['contrib_user_id'];
		/// Contribution parente
		$contribdata['contrib_parent'] = isset( $contribdata['contrib_parent'] ) ? absint( $contribdata['contrib_parent'] ) : 0;
		$parent_status = ( 0 < $contribdata['contrib_parent'] ) ? $this->master->contribution->get_status( $contribdata['contrib_parent'] ) : '';
		$contribdata['contrib_parent'] = ( 'approved' == $parent_status || 'unapproved' == $parent_status ) ? $contribdata['contrib_parent'] : 0;
		/// IP de l'auteur contribution
		if ( ! isset( $contribdata['contrib_author_IP'] ) ) 
			$contribdata['contrib_author_IP'] = $_SERVER['REMOTE_ADDR'];
		$contribdata['contrib_author_IP'] = preg_replace( '/[^0-9a-fA-F:., ]/', '', $contribdata['contrib_author_IP'] );
		/// Navigateur internet de l'auteur contribution 
		if ( ! isset( $contribdata['contrib_agent'] ) )
			$contribdata['contrib_agent'] = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT']: '';		
		$contribdata['contrib_agent'] = substr( $contribdata['contrib_agent'], 0, 254 );
		/// Date de soumission de la contribution
		if ( empty( $contribdata['contrib_date'] ) ) 
			$contribdata['contrib_date'] = current_time('mysql');	
		if ( empty( $contribdata['contrib_date_gmt'] ) )
			$contribdata['contrib_date_gmt'] = current_time( 'mysql', 1 );

		// Filtrage des données de soumission de la contribution
		$contribdata = Contribution::filter( $contribdata );
		
		// Définition du statut de moderation de la contribution
		$contribdata['contrib_approved'] = Contribution::allow( $contribdata );
		
		// Insertion en base de donnée de la contribution
		$contrib_id = Contribution::insert( $contribdata );
		
		do_action( 'tify_forum_contrib_post', $contrib_id, $contribdata['contrib_approved'] );
	
		return $contrib_id;
	}
}