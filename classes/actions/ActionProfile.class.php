<?php

/**
 * Drafts - доступ к черновикам пользователей
 *
 * Автор:    Александр Вереник
 * Профиль:    http://livestreet.ru/profile/Wasja/
 * GitHub:    https://github.com/wasja1982/livestreet_drafts
 *
 * Автор адаптации под Alto CMS: shtrih
 * GitHub: https://github.com/shtrih/altocms-plugin-drafts
 **/
class PluginDrafts_ActionProfile extends PluginDrafts_Inherit_ActionProfile
{
    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {
        if (Config::Get('plugin.drafts.show_profile')) {
            $this->AddEventPreg('/^.+$/i', '/^created/i', '/^draft$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventCreatedDrafts');
        }
        parent::RegisterEvent();
    }

    /**
     * Список черновиков пользователя
     */
    protected function EventCreatedDrafts() {
        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }
        if (!E::IsAdmin()) {
            return parent::EventNotFound();
        }
        $this->sMenuSubItemSelect = 'draft';
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(2, 2) ? $this->GetParamEventMatch(2, 2) : 1;
        /**
         * Получаем список топиков
         */
        $aResult = E::ModuleTopic()->GetDraftsPersonalByUser($this->oUserProfile->getId(), $iPage, Config::Get('module.topic.per_page'));
        $aTopics = $aResult['collection'];
        /**
         * Вызов хуков
         */
        E::ModuleHook()->Run('topics_list_show', array('aTopics' => $aTopics));
        /**
         * Формируем постраничность
         */
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'],
            $iPage,
            Config::Get('module.topic.per_page'),
            Config::Get('pagination.pages.count'),
            $this->oUserProfile->getUserWebPath() . 'created/draft'
        );
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_publication') . ' ' . $this->oUserProfile->getLogin());
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_publication_blog'));
        E::ModuleViewer()->SetHtmlRssAlternate(
            Router::GetPath('rss') . 'personal_blog/' . $this->oUserProfile->getLogin() . '/',
            $this->oUserProfile->getLogin()
        );
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('created_topics');
    }

    /**
     * Выполняется при завершении работы экшена
     */
    public function EventShutdown() {
        parent::EventShutdown();
        if (!$this->oUserProfile) {
            return;
        }
        /**
         * Загружаем в шаблон необходимые переменные
         */
        $iCountDraftUser = E::ModuleTopic()->GetCountDraftsPersonalByUser($this->oUserProfile->getId());
        E::ModuleViewer()->Assign('iCountDraftUser', $iCountDraftUser);
    }
}

