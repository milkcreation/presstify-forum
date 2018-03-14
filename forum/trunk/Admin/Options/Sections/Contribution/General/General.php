<?php
namespace tiFy\Plugins\Forum\Admin\Options\Sections\Contribution\General;

use tiFy\Core\Taboox\Admin;

class General extends Admin
{	
	/* = CHARGEMENT DE LA PAGE COURANTE = */
	public function current_screen( $current_screen )
	{		
		tify_control_enqueue( 'switch' );
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form()
	{
		?>		
		<table class="form-table">
			<tbody>
				<?php //Renseignement sur le nom et l'email ?>
				<tr>
					<th scope="row">
						<?php _e( 'L\'auteur d’une réponse devra obligatoirement renseigner son nom et son adresse de messagerie', 'tify' );?>
					</th>
					<td>
					<?php 
						tify_control_switch( 
							array( 
								'name' 		=> 'tify_forum_options[general][require_name_email]', 
								'checked' 	=> (int) \tiFy\Plugins\Forum\Options::get( 'general::require_name_email' ),
								'value_on'	=> 1,
								'value_off'	=> 0
							) 
						);
					?>
					</td>
				</tr>
				<?php //Utilisateur en mode connecté ? ?>
				<tr>
					<th scope="row">
						<?php _e( 'Un utilisateur doit être enregistré et connecté pour publier des réponses', 'tify' );?>
					</th>
					<td>
					<?php 
						tify_control_switch( 
							array( 
								'name' 		=> 'tify_forum_options[general][contrib_registration]', 
								'checked' 	=> (int) \tiFy\Plugins\Forum\Options::get( 'general::contrib_registration' ),
								'value_on'	=> 1,
								'value_off'	=> 0					
							) 
						);
					?></td>
				</tr>
			</tbody>
		</table>
		<?php /*//Fil de Discussion ?>
			<?php tify_control_switch( array( 'name' => 'tify_forum_global_params[thread_contribs]', 'checked' => $params['thread_contribs'] ) );?>	
			<?php $maxdeep = (int) apply_filters( 'tify_forum_thread_contribs_depth_max', 5 );
				$thread_contribs_depth = '</label><select name="tify_forum_global_params[thread_contribs_depth]" id="thread_contribs_depth">';
			for ( $i = 2; $i <= $maxdeep; $i++ ) {
				$thread_contribs_depth .= "<option value='" . esc_attr($i) . "'";
				if ( $params['thread_contribs_depth'] == $i ) $thread_contribs_depth .= " selected='selected'";
				$thread_contribs_depth .= ">$i</option>";
			}
			$thread_contribs_depth .= '</select>';
			printf( __( 'Activer les commentaires imbriqués jusqu’à %s niveaux'), $thread_contribs_depth );
			?>
			<br />
			<br />
		<?php //Pagination ?>
			<?php tify_control_switch( array( 'name' => 'tify_forum_global_params[page_contribs]', 'checked' => $params['page_contribs'] ) );?>	
			<?php 
				$default_contribs_page = '</label><label for="default_contribs_page"><select name="tify_forum_global_params[default_contribs_page]" id="default_contribs_page"><option value="newest"';
				if ( 'newest' == $params['default_contribs_page'] ) $default_contribs_page .= ' selected="selected"';
				$default_contribs_page .= '>' . __('last') . '</option><option value="oldest"';
				if ( 'oldest' == $params['default_contribs_page'] ) $default_contribs_page .= ' selected="selected"';
				$default_contribs_page .= '>' . __('first') . '</option></select>';
				printf( __('Break comments into pages with %1$s top level comments per page and the %2$s page displayed by default'), '</label><label for="contribs_per_page"><input name="tify_forum_global_params[contribs_per_page]" type="text" id="contribs_per_page" value="' . esc_attr( $params['contribs_per_page'] ) . '" class="small-text" />', $default_contribs_page );
			?></label>
			<br />
			<br />
		<?php //Order?>	
			<?php
			$contribs_order = '<select name="tify_forum_global_params[contribs_order]" id="contribs_order"><option value="asc"';
			if ( 'asc' == $params['contribs_order'] ) $contribs_order.= ' selected="selected"';
			$contribs_order .= '>' . __('older') . '</option><option value="desc"';
			if ( 'desc' == $params['contribs_order'] ) $contribs_order .= ' selected="selected"';
			$contribs_order .= '>' . __('newer') . '</option></select>';
			printf( __( 'Comments should be displayed with the %s comments at the top of each page' ), $contribs_order );
		*/ ?>
		<?php
	}
}