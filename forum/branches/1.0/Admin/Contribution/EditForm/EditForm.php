<?php
namespace tiFy\Plugins\Forum\Admin\Contribution\EditForm;

class EditForm extends \tiFy\Core\Templates\Admin\Model\EditForm\EditForm
{	
	/* = DECLENCHEURS = */
	/** == Chargement de l'écran courant == **/
	final public function current_screen( $current_screen )
	{
		tify_control_enqueue( 'quicktags_editor' );
	}
	
	/* = VUES = */
	/** == Formulaire d'édition == **/
	public function form()
	{
	?>
		<div class="stuffbox" id="namediv">
			<div class="inside">
				<fieldset>
					<legend class="edit-comment-author"><h4><?php _e( 'Auteur', 'tify' );?></h4></legend>
					<table class="form-table">
						<tbody>
							<tr>
								<td class="first">
									<label for="name"><?php _e( 'Nom', 'tify' );?>&nbsp;:</label>
								</td>
								<td><input type="text" id="name" value="<?php echo $this->item->contrib_author;?>" size="30" name="contrib_author"></td>
							</tr>
							<tr>
								<td class="first">
									<label for="name"><?php _e( 'E-mail', 'tify' );?>&nbsp;:</label>
								</td>
								<td>
									<input type="text" id="email" value="<?php echo $this->item->contrib_author_email;?>" size="30" name="contrib_author_email">
								</td>
							</tr>
							<tr>
								<td class="first">
									<label for="name"><?php _e( 'Adresse Web', 'tify' );?>&nbsp;:</label>
								</td>
								<td>
									<input type="text" value="<?php echo $this->item->contrib_author_url;?>" class="code" size="30" name="contrib_author_url" id="author_url">
								</td>
							</tr>
						</tbody>
					</table>
					<br>
				</fieldset>
			</div>
		</div>	
	<?php	
		tify_control_quicktags_editor(
			array( 
				'id' 	=> 'content', 
				'name' 	=> 'contrib_content', 
				'value' => $this->item->contrib_content
			) 
		);
	}
}