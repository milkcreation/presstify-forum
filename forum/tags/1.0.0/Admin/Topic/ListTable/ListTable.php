<?php
namespace tiFy\Plugins\Forum\Admin\Topic\ListTable;

class ListTable extends \tiFy\Core\Templates\Admin\Model\ListTable\ListTable
{
    /* = DECLENCHEURS = */
    /** == Affichage de l'écran courant == **/
    final public function current_screen($current_screen)
    {
        wp_enqueue_style('tiFyPluginForumAdminTopicListTable', self::tFyAppUrl(get_class()) . '/ListTable.css', [], 160603);
    }

    /* = PARAMETRAGE = */
    /** == Définition des messages de notification == **/
    public function set_notices()
    {
        return [
            'opened'     => __('Le sujet est désormais ouvert aux discussions.', 'tify'),
            'closed'     => __('Le sujet est désormais fermé aux discussions.', 'tify'),
            'approved'   => __('Le sujet est désormais approuvée.', 'tify'),
            'unapproved' => __('Le sujet est désormais desapprouvée.', 'tify')
        ];
    }

    /** == Définition des vues filtrées == **/
    public function set_views()
    {
        return [
            [
                'label'             => __('Tous (hors corbeille)', 'tify'),
                'base_uri'          => $this->BaseUri,
                'count'             => $this->count_items(['topic_status' => 'publish']),
                'add_query_args'    => ['topic_status' => 'publish'],
                'remove_query_args' => 'topic_status'
            ],
            [
                'label'          => __('Fermé', 'tify'),
                'base_uri'       => $this->BaseUri,
                'count'          => $this->count_items([
                    'topic_contrib_status' => 'close',
                    'topic_status'         => 'publish'
                ]),
                'hide_empty'     => true,
                'add_query_args' => ['topic_contrib_status' => 'close', 'topic_status' => 'publish']
            ],
            [
                'label'          => __('Corbeille', 'tify'),
                'base_uri'       => $this->BaseUri,
                'count'          => $this->count_items(['topic_status' => 'trash']),
                'hide_empty'     => true,
                'add_query_args' => ['topic_status' => 'trash']
            ]
        ];
    }

    /** == Définition des colonnes de la table == **/
    public function set_columns()
    {
        return [
            'cb'            => $this->get_cb_column_header(),
            'topic_title'   => __('Titre', 'tify'),
            'topic_excerpt' => __('Extrait', 'tify'),
            'topic_date'    => __('Date de création', 'tify'),
            'topic_author'  => __('Auteur', 'tify')
        ];
    }

    /** == == **/
    public function set_bulk_actions()
    {
        return [
            'delete' => __('Supprimer', 'tify'),
            'trash'  => __('Mettre à la corbeille', 'tify')
        ];
    }

    /** == Définition des actions sur un élément == **/
    public function set_row_actions()
    {
        return [
            'open'      => [
                'label'      => __('Ouvrir', 'tify'),
                'title'      => __('Autoriser l\'ajout de sujet', 'tify'),
                'link_attrs' => ['style' => 'color:#006505;'],
                'nonce'      => $this->get_item_nonce_action('open')
            ],
            'close'     => [
                'label'      => __('Fermer', 'tify'),
                'title'      => __('Autoriser l\'ajout de sujet', 'tify'),
                'link_attrs' => ['style' => 'color:#D98500;'],
                'nonce'      => $this->get_item_nonce_action('close')
            ],
            'approve'   => [
                'label'      => __('Approuver', 'tify'),
                'title'      => __('Approuver le sujet', 'tify'),
                'link_attrs' => ['style' => 'color:#006505;'],
                'nonce'      => $this->get_item_nonce_action('approve')
            ],
            'unapprove' => [
                'label'      => __('Désapprouver', 'tify'),
                'title'      => __('Désapprouver le sujet', 'tify'),
                'link_attrs' => ['style' => 'color:#D98500;'],
                'nonce'      => $this->get_item_nonce_action('unapprove')
            ],
            'trash',
            'untrash',
            'edit',
            'delete',
        ];
    }

    /** == Définition de l'ajout automatique des actions de l'élément pour la colonne principale == **/
    public function set_handle_row_actions()
    {
        return false;
    }

    /* = TRAITEMENT = */
    /** == Éxecution de l'action - Ouvrir == **/
    protected function process_bulk_action_open()
    {
        $item_ids = $this->current_item();

        // Vérification des permissions d'accès
        if (!wp_verify_nonce(@$_REQUEST['_wpnonce'], 'bulk-' . $this->Plural)) :
            check_admin_referer($this->get_item_nonce_action('open'));
        endif;

        // Traitement de l'élément
        foreach ((array)$item_ids as $item_id) :
            $this->db()->handle()->update($item_id, ['topic_contrib_status' => 'open']);
        endforeach;

        // Traitement de la redirection
        $sendback = remove_query_arg(['action', 'action2'], wp_get_referer());
        $sendback = add_query_arg('message', 'opened', $sendback);

        wp_redirect($sendback);
        exit;
    }

    /** == Éxecution de l'action - Fermer == **/
    protected function process_bulk_action_close()
    {
        $item_ids = $this->current_item();

        // Vérification des permissions d'accès
        if (!wp_verify_nonce(@$_REQUEST['_wpnonce'], 'bulk-' . $this->Plural)) :
            check_admin_referer($this->get_item_nonce_action('close'));
        endif;

        // Traitement de l'élément
        foreach ((array)$item_ids as $item_id) :
            $this->db()->handle()->update($item_id, ['topic_contrib_status' => 'close']);
        endforeach;

        // Traitement de la redirection
        $sendback = remove_query_arg(['action', 'action2'], wp_get_referer());
        $sendback = add_query_arg('message', 'closed', $sendback);

        wp_redirect($sendback);
        exit;
    }

    /** == Éxecution de l'action - approbation == **/
    protected function process_bulk_action_approve()
    {
        $item_ids = $this->current_item();

        // Vérification des permissions d'accès
        if (!wp_verify_nonce(@$_REQUEST['_wpnonce'], 'bulk-' . $this->Plural)) :
            check_admin_referer($this->get_item_nonce_action('approve'));
        endif;

        // Traitement de l'élément
        foreach ((array)$item_ids as $item_id) :
            $this->db()->meta()->update($item_id, 'approved', 1);
        endforeach;

        // Traitement de la redirection
        $sendback = remove_query_arg(['action', 'action2'], wp_get_referer());
        $sendback = add_query_arg('message', 'activated', $sendback);

        wp_redirect($sendback);
        exit;
    }

    /** == Éxecution de l'action - désapprobation == **/
    protected function process_bulk_action_unapprove()
    {
        $item_ids = $this->current_item();

        // Vérification des permissions d'accès
        if (!wp_verify_nonce(@$_REQUEST['_wpnonce'], 'bulk-' . $this->Plural)) :
            check_admin_referer($this->get_item_nonce_action('unapprove'));
        endif;

        // Traitement de l'élément
        foreach ((array)$item_ids as $item_id) :
            $this->db()->meta()->update($item_id, 'approved', 0);
        endforeach;

        // Traitement de la redirection
        $sendback = remove_query_arg(['action', 'action2'], wp_get_referer());
        $sendback = add_query_arg('message', 'deactivated', $sendback);

        wp_redirect($sendback);
        exit;
    }

    /* = AFFICHAGE = */
    /** == == **/
    public function single_row($item)
    {
        $class = '';
        if ($item->topic_contrib_status !== 'open') :
            $class = 'closed';
        elseif (!$this->db()->meta()->get($item->topic_id, 'approved', true)) :
            $class = 'unapproved';
        elseif ($this->db()->meta()->get($item->topic_id, 'approved', true) == -1) :
            $class = 'wait_approve';
        endif;
        ?>
        <tr class="<?php echo $class; ?>">
            <?php
            $this->single_row_columns($item);
            ?>
        </tr>
        <?php
    }

    /** == COLONNE - Titre === **/
    public function column_topic_title($item)
    {
        $label = !$item->topic_title ? __('(Pas de titre)', 'tify') : $item->topic_title;

        $status = false;
        $row_actions = ['open', 'close', 'approve', 'unapprove', 'edit', 'trash', 'untrash', 'delete'];

        if ($item->topic_contrib_status === 'open') :
            $row_actions = array_diff($row_actions, ['open']);
        else :
            $row_actions = array_diff($row_actions, ['close']);
        endif;

        $approving = $this->db()->meta()->get($item->topic_id, 'approved', true);
        if ($approving == 1) :
            $row_actions = array_diff($row_actions, ['approve']);
        elseif ($approving == -1) :
            $status = ' — ' . __('En attente de modération', 'tify');
        else :
            $row_actions = array_diff($row_actions, ['unapprove']);
        endif;

        if ($item->topic_status === 'trash') :
            $row_actions = array_diff($row_actions, ['open', 'close', 'approve', 'unapprove', 'edit', 'trash']);
        else :
            $row_actions = array_diff($row_actions, ['untrash', 'delete']);
        endif;

        if ($edit_link = $this->get_item_edit_link($item, [], $label, 'row-title')) :
            return sprintf('<strong>%1$s%2$s</strong>%3$s', $edit_link, $status,
                $this->get_row_actions($item, $row_actions, false));
        else :
            return sprintf('<strong>%1$s%2$s</strong>', $label, $status);
        endif;
    }

    /** == COLONNE - Auteur === **/
    public function column_topic_author($item)
    {
        if ($user = get_user_by('ID', $item->topic_author)) :
            $author = $user->display_name;
            $avatar = get_avatar($user->ID, 32);
        else :
            $author = __('Anonyme', 'tify');
            $avatar = get_avatar(0, 32);
        endif;

        echo $avatar . $author;
    }
}