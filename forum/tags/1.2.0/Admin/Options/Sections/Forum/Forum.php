<?php
namespace tiFy\Plugins\Forum\Admin\Options\Sections\Forum;

use tiFy\Core\Taboox\Admin;

class Forum extends Admin
{
    /* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
    public function admin_init()
    {
        \register_setting( $this->page, 'page_for_tify_forum' );
    }

    /* = FORMULAIRE DE SAISIE = */
    public function form()
    {
        $value = \get_option( 'page_for_tify_forum', 0 );
    ?>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <?php _e( 'Page d\'affichage des forums', 'tify' );?>
                </th>
                <td>
                <?php 
                    wp_dropdown_pages( 
                        array(
                            'selected'             => $value,
                            'name'                 => 'page_for_tify_forum',
                            'show_option_none'     => __( 'Aucune', 'tify' ), 
                            'option_none_value' => 0 
                        ) 
                    );
                ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e( 'Consultation privée', 'tify' );?></label>
                    <em style="display:block;font-weight:normal;color:#AAA;font-size:11px;"><?php _e( 'L\'accès à la consultation est réservé aux personnes habilitées', 'tify' );?></em>
                </th>
                <td>
                <?php 
                    tify_control_switch( 
                        array( 
                            'name'      => 'tify_forum_options[forum][private_read]', 
                            'checked'   => (int) \tiFy\Plugins\Forum\Options::get( 'forum::private_read' ),
                            'value_on'  => 1,
                            'value_off' => 0
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