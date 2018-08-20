<?php
namespace tiFy\Plugins\Forum\Admin\Options\Sections\Contribution\Mailing;

use tiFy\Core\Taboox\Admin;

class Mailing extends Admin
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
						<?php _e( 'Lorsqu\'une nouvelle contribution est publiée', 'tify' );?>
					</th>
					<td>
					<?php 
						tify_control_switch( 
							array( 
								'name' 		=> 'tify_forum_options[mailing][contribs_notify]', 
								'checked' 	=> (int) \tiFy\Plugins\Forum\Options::get( 'mailing::contribs_notify' ),
								'value_on'	=> 1,
								'value_off'	=> 0
							) 
						);
					?>	
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php _e( 'Lorsqu\'une contribution est en attente de modération', 'tify' );?>
					</th>
					<td>
					<?php 
						tify_control_switch( 
							array( 
								'name' 		=> 'tify_forum_options[mailing][moderation_notify]', 
								'checked' 	=> (int) \tiFy\Plugins\Forum\Options::get( 'mailing::moderation_notify' ),
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