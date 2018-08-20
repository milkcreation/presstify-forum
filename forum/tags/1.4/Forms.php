<?php
/*
 * @Overridable
 */
namespace tiFy\Plugins\Forum;

class Forms extends \tiFy\App\Factory
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $tFyAppActions				= array(
		'tify_form_register'
	); 
	
	/* = DECLENCHEURS = */
	/** == Déclaration des formulaires == **/
	final public function tify_form_register()
	{
		// Formulaire d'inscription
		$subscribe_form = $this->subscribeForm();		
		$subscribe_form['addons']['user'] = array(
			'roles' 		=> Forum::getRoles()
		);
		tify_form_register( 'tiFyPluginForum_SubscribeForm', $subscribe_form );
		
		// Formulaire de modification des informations de compte
		if( is_user_logged_in() && ( $current_user = wp_get_current_user() ) ) :
			$account_form = $this->accountForm( $current_user );
			$account_form['addons']['user'] 	= array(
				'roles' 		=> Forum::getRoles()
			);
			tify_form_register( 'tiFyPluginForum_AccountForm', $account_form );
		endif;
	}
	
	/* = CONTROLEURS = */
	/** == Définition du formulaire d'inscription == **/
	public function subscribeForm()
	{	
		// Formulaire d'inscription
		return array(
			'title' 	=>  __( 'Formulaire d\'inscription aux forums', 'tify' ),
			'prefix' 	=> 'tify_forum_subscribe_form',
			'fields' => array(
				array(
					'slug'			=> 'login',
					'label' 		=> __( 'Identifiant', 'tify' ),
					'placeholder' 	=> __( 'Identifiant (obligatoire)', 'tify' ),
					'type' 			=> 'input',
					'required'		=> true,
					'addons'		=> array(
						'user'	=> array( 'userdata' => 'user_login' )
					)
				),
				array(
					'slug'			=> 'email',
					'label' 		=> __( 'E-mail', 'tify' ),
					'placeholder' 	=> __( 'E-mail (obligatoire)', 'tify' ),
					'type' 			=> 'input',
					'required'		=> true,
					'integrity_cb'	=> 'is_email',
					'addons'		=> array(
						'user'	=> array( 'userdata' => 'user_email' )
					)
				),
				array(
					'slug'			=> 'firstname',
					'label' 		=> __( 'Prénom', 'tify' ),
					'placeholder' 	=> __( 'Prénom', 'tify' ),
					'type' 			=> 'input',
					'addons'		=> array(
						'user'	=> array( 'userdata' => 'first_name' )
					)
				),
				array(
					'slug'			=> 'lastname',
					'label' 		=> __( 'Nom', 'tify' ),
					'placeholder' 	=> __( 'Nom', 'tify' ),
					'type' 			=> 'input',
					'addons'		=> array(
						'user'	=> array( 'userdata' => 'last_name' )
					)
				),
				array(
					'slug'			=> 'password',
					'label' 		=> __( 'Mot de passe', 'tify' ),
					'placeholder' 	=> __( 'Mot de passe (obligatoire)', 'tify' ),
					'type' 			=> 'password',
					'autocomplete'	=> 'off',
					'required'		=> true,
					'addons'		=> array(
						'user'	=> array( 'userdata' => 'user_pass' )
					)
				),
				array(
					'slug'			=> 'confirm',
					'label' 		=> __( 'Confirmation de mot de passe', 'tify' ),
					'placeholder' 	=> __( 'Confirmation de mot de passe (obligatoire)', 'tify' ),
					'type' 			=> 'password',
					'autocomplete'	=> 'off',
					'required'		=> true,
					'integrity_cb'	=> array( 
						'function' => 'compare', 
						'args' => array( '%%password%%' ), 
						'error' => __( 'Les champs "Mot de passe" et "Confirmation de mot de passe" doivent correspondre', 'tify' ) 
					)
				),
				array(
					'slug'			=> 'captcha',
					'label' 		=> __( 'Code de sécurité', 'tify' ),
					'placeholder' 	=> __( 'Code', 'tify' ),
					'type' 			=> 'simple-captcha-image',
					'required'		=> true
				)
			),
			'buttons'		=> array(
				'submit' 		=> __( 'S\'inscrire', 'tify' ),
			),
			'notices'		=> array(
				'success'		=> __( 'Votre demande d\'inscription a bien été enregistrée', 'tify' )
			),
			'options' 		=> array(

			)
		);		
	}	
	
	/** == Définition du formulaire de modification de compte == **/
	public function accountForm( $current_user )
	{
		return array(
			'title' 	=>  __( 'Formulaire de modification de compte personnel des contributeurs de forums', 'tify' ),
			'prefix' 	=> 'tify_forum_user_account_form',
			'fields' => array(
				array(
					'slug'			=> 'login',
					'label' 		=> __( 'Votre identifiant', 'tify' ),
					'placeholder' 	=> __( 'Votre identifiant', 'tify' ),
					'type' 			=> 'input',
					'value'			=> $current_user->user_login,
					'required'		=> true,
					'readonly'		=> true,
					'addons'		=> array(
						'user'	=> array( 'userdata' => 'user_login' )
					)
				),
				array(
					'slug'			=> 'email',
					'label' 		=> __( 'Votre  E-mail', 'tify' ),
					'placeholder' 	=> __( 'Votre  E-mail', 'tify' ),
					'type' 			=> 'input',
					'value'			=> $current_user->user_email,
					'required'		=> true,
					'integrity_cb'	=> 'is_email',
					'addons'		=> array(
						'user'	=> array( 'userdata' => 'user_email' )
					)
				),
				array(
					'slug'			=> 'firstname',
					'label' 		=> __( 'Votre Prénom', 'tify' ),
					'placeholder' 	=> __( 'Votre Prénom', 'tify' ),
					'value'			=> $current_user->user_firstname,
					'type' 			=> 'input',
					'addons'		=> array(
						'user'	=> array( 'userdata' => 'first_name' )
					)
				),
				array(
					'slug'			=> 'lastname',
					'label' 		=> __( 'Nom', 'tify' ),
					'placeholder' 	=> __( 'Nom', 'tify' ),
					'value'			=> $current_user->user_lastname,
					'type' 			=> 'input',
					'addons'		=> array(
						'user'	=> array( 'userdata' => 'last_name' )
					)
				),
				array(
					'slug'			=> 'password',
					'label' 		=> __( 'Nouveau mot de passe', 'tify' ),
					'placeholder' 	=> __( 'Nouveau mot de passe', 'tify' ),
					'type' 			=> 'password',
					'autocomplete'	=> 'off',
					'addons'		=> array(
						'user'	=> array( 'userdata' => 'user_pass' )
					)
				),
				array(
					'slug'			=> 'confirm',
					'label' 		=> __( 'Confirmation de nouveau mot de passe', 'tify' ),
					'placeholder' 	=> __( 'Confirmation de nouveau mot de passe', 'tify' ),
					'type' 			=> 'password',
					'autocomplete'	=> 'off',
					'integrity_cb'	=> array( 
						'function' => 'compare', 
						'args' => array( '%%password%%' ), 
						'error' => __( 'Les champs "Mot de passe" et "Confirmation de mot de passe" doivent correspondre', 'tify' ) 
					)
				)
			),
			'buttons'		=> array(
				'submit' 		=> __( 'Mettre à jour', 'tify' ),
			),
			'notices'		=> array(
				'success'		=> __( 'Vos informations personnelles ont été mises à jour', 'tify' )
			),
			'options' => array(
				'success_cb'	=> 'form' 
			)
		);
	}
}