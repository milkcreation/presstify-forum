<?php
namespace tiFy\Plugins\Forum\Admin\Options\Sections\Contribution\Moderation;

use tiFy\Core\Taboox\Admin;

class Moderation extends Admin
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
				<tr>
					<th scope="row">
						<?php _e( 'Les contributions doivent toujours être approuvées par un administrateur', 'tify' );?>
					</th>
					<td>
					<?php 
						tify_control_switch( 
							array( 
								'name' 		=> 'tify_forum_options[moderation][contrib_moderation]', 
								'checked' 	=> (int) \tiFy\Plugins\Forum\Options::get( 'moderation::contrib_moderation' ),
								'value_on'	=> 1,
								'value_off'	=> 0									
							) 
						);
					?>	
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php _e( 'Approuver automatiquement les contributions des auteurs ayant déjà une contribution approuvée', 'tify' );?>
					</th>
					<td>
					<?php 
						tify_control_switch( 
							array( 
								'name' 		=> 'tify_forum_options[moderation][contrib_whitelist]', 
								'checked' 	=> (int) \tiFy\Plugins\Forum\Options::get( 'moderation::contrib_whitelist' ),
								'value_on'	=> 1,
								'value_off'	=> 0								
							) 
						);
					?>	
					</td>
				</tr>
			</tbody>
		</table>
	<?php			
	}
}