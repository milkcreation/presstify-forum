<?php
namespace tiFy\Plugins\Forum\Admin\Options\Sections\Contributor;

use tiFy\Core\Taboox\Admin;

class Contributor extends Admin
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
						<?php _e( 'Demander une confirmation d\'enregistrement par email aux nouveaux utilisateurs', 'tify' );?>
					</th>
					<td>
					<?php 
						tify_control_switch( 
							array( 
								'name' 		=> 'tify_forum_options[contributor][double_optin]', 
								'checked' 	=> (int) \tiFy\Plugins\Forum\Options::get( 'contributor::double_optin' ),
								'value_on'	=> 1,
								'value_off'	=> 0 									
							) 
						);
					?>	
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php _e( 'Le compte des nouveaux inscrits doit être activer par un modérateur', 'tify' );?>
					</th>
					<td>
					<?php 
						tify_control_switch( 
							array( 
								'name' 		=> 'tify_forum_options[contributor][moderate_account_activation]', 
								'checked' 	=> (int) \tiFy\Plugins\Forum\Options::get( 'contributor::moderate_account_activation' ),
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