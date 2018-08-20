<?php
namespace tiFy\Plugins\Forum\Admin\Options;

class Options extends \tiFy\Core\Templates\Admin\Model\TabooxOption\TabooxOption
{
    /**
     * Définition des sections de formulaire
     */
    public function set_sections()
    {
        return [
            [
                'id'    => 'tify_forum-options-forum',
                'title' => __('Forum', 'tify'),
                'cb'    => "tiFy\\Plugins\\Forum\\Admin\\Options\\Sections\\Forum\\Forum",
                'order' => 1
            ],
            [
                'id'    => 'tify_forum-options-topics',
                'title' => __('Sujets', 'tify'),
                'cb'    => "tiFy\\Plugins\\Forum\\Admin\\Options\\Sections\\Topic\\Topic",
                'order' => 2
            ],
            [
                'id'    => 'tify_forum-options-contributors',
                'title' => __('Contributeurs', 'tify'),
                'cb'    => "tiFy\\Plugins\\Forum\\Admin\\Options\\Sections\\Contributor\\Contributor",
                'order' => 3
            ],
            [
                'id'    => 'tify_forum-options-contribs',
                'title' => __('Contributions', 'tify'),
                'order' => 4
            ],
            [
                'id'     => 'tify_forum-options-contribs_global',
                'parent' => 'tify_forum-options-contribs',
                'title'  => __('Généralités', 'tify'),
                'cb'     => "tiFy\\Plugins\\Forum\\Admin\\Options\\Sections\\Contribution\\General\\General",
                'order'  => 1
            ],
            [
                'id'     => 'tify_forum-options-contribs_mailing',
                'parent' => 'tify_forum-options-contribs',
                'title'  => __('Envoi de mail', 'tify'),
                'cb'     => "tiFy\\Plugins\\Forum\\Admin\\Options\\Sections\\Contribution\\Mailing\\Mailing",
                'order'  => 2
            ],
            [
                'id'     => 'tify_forum-options-contribs_moderation',
                'parent' => 'tify_forum-options-contribs',
                'title'  => __('Modération', 'tify'),
                'cb'     => "tiFy\\Plugins\\Forum\\Admin\\Options\\Sections\\Contribution\\Moderation\\Moderation",
                'order'  => 3
            ]
        ];
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de l'interface d'administration
     */
    public function admin_init()
    {
        \register_setting($this->MenuSlug, 'tify_forum_options');
    }

    /**
     * Chargement de l'écran courant
     */
    public function current_screen()
    {
        wp_enqueue_style('tiFyPluginForumAdminOptions', self::tFyAppUrl() . '/Options.css', [], 160609);
    }
}