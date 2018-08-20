<?php
use tiFy\Plugins\Forum\Forum;

/* = Vérifie si la page courante affiche un template de membership = */
function is_tify_forum( $template = null )
{
	$Template = Forum::getController( 'template' );
	
	return $Template::IsTemplate( $template );
}