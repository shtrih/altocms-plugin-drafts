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
class PluginDrafts_ActionIndex extends PluginDrafts_Inherit_ActionIndex
{
    /**
     * Регистрация евентов
     *
     */
    protected function RegisterEvent() {
        if (Config::Get('plugin.drafts.show_personal') || Config::Get('plugin.drafts.show_blog')) {
            $this->AddEventPreg('/^draft$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventDraft');
        }
        parent::RegisterEvent();
    }

    /**
     * Вывод всех черновиков
     */
    protected function EventDraft() {
        if (!E::IsAdmin()) {
            return parent::EventNotFound();
        }
        E::ModuleViewer()->SetHtmlRssAlternate(Router::GetPath('rss') . 'draft/', Config::Get('view.name'));
        /**
         * Меню
         */
        $this->sMenuSubItemSelect = 'draft';
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(0, 2) ? $this->GetParamEventMatch(0, 2) : 1;
        /**
         * Получаем список топиков
         */
        $aResult = E::ModuleTopic()->GetTopicsDraftAll($iPage, Config::Get('module.topic.per_page'));
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
            Router::GetPath('index') . 'draft'
        );
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('index');
    }
}
