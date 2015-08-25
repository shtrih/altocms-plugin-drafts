<?php
/**
 * Drafts - доступ к черновикам пользователей
 *
 * Автор:	Александр Вереник
 * Профиль:	http://livestreet.ru/profile/Wasja/
 * GitHub:	https://github.com/wasja1982/livestreet_drafts
 *
 * Автор адаптации под Alto CMS: shtrih
 * GitHub: https://github.com/shtrih/altocms-plugin-drafts
 **/

/**
 * Запрещаем напрямую через браузер обращение к этому файлу.
 */
if (!class_exists('Plugin')) {
    die('Hacking attempt!');
}

class PluginDrafts extends Plugin {

    protected $aInherits = array(
        'action' => array(
            'ActionBlog',
            'ActionPersonalBlog',
            'ActionIndex',
            'ActionProfile'
        ),
        'module' => array('ModuleTopic'),
    );

    /**
     * Активация плагина
     */
    public function Activate() {
        return true;
    }

    /**
     * Инициализация плагина
     */
    public function Init() {
    }
}
