<?php
namespace tiFy\Plugins\Forum\Admin\Options\Sections\Topic;

use tiFy\Core\Taboox\Admin;

class Topic extends Admin
{
	/* = FORMULAIRE DE SAISIE = */
    public function form()
    {
    ?>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label><?php _e( 'Proposition ouverte', 'tify' );?></label>
                    <em style="display:block;font-weight:normal;color:#AAA;font-size:11px;"><?php _e( 'Les utilisateurs habilités sont autorisés à proposer de nouveaux sujet de discussion.', 'tify' );?></em>
                </th>
                <td>
                <?php 
                    tify_control_switch( 
                        array( 
                            'name'      => 'tify_forum_options[topic][submit_opened]', 
                            'checked'   => (int) \tiFy\Plugins\Forum\Options::get( 'topic::submit_opened' ),
                            'value_on'  => 1,
                            'value_off' => 0
                        ) 
                    );
                ?>  
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e( 'Modération', 'tify' );?></label>
                    <em style="display:block;font-weight:normal;color:#AAA;font-size:11px;"><?php _e( 'Les nouveaux sujets doivent être validés par un modérateur.', 'tify' );?></em>
                </th>
                <td>
                <?php 
                    tify_control_switch( 
                        array( 
                            'name'      => 'tify_forum_options[topic][moderate]', 
                            'checked'   => (int) \tiFy\Plugins\Forum\Options::get( 'topic::moderate' ),
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