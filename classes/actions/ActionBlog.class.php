<?php

/**
 * Drafts - доступ к черновикам пользователей
 *
 * Автор:     Александр Вереник
 * Профиль:   http://livestreet.ru/profile/Wasja/
 * GitHub:    https://github.com/wasja1982/livestreet_drafts
 *
 * Автор адаптации под Alto CMS: shtrih
 * GitHub: https://github.com/shtrih/altocms-plugin-drafts
 **/
class PluginDrafts_ActionBlog extends PluginDrafts_Inherit_ActionBlog
{
    /**
     * Инизиализация экшена
     *
     */
    public function Init() {
        parent::Init();
        $this->aBadBlogUrl[] = 'draft';
    }

    /**
     * Регистрируем евенты, по сути определяем УРЛы вида /blog/.../
     *
     */
    protected function RegisterEvent() {
        if (Config::Get('plugin.drafts.show_blog')) {
            $this->AddEventPreg('/^draft$/i', '/^(page([1-9]\d{0,5}))?$/i', array('EventTopics', 'topics'));
            $this->AddEventPreg('/^[\w\-\_]+$/i', '/^draft$/i', '/^(page([1-9]\d{0,5}))?$/i', array('EventShowBlog', 'blog'));
        }
        parent::RegisterEvent();
    }

    /**
     * Показ всех топиков
     *
     */
    protected function EventTopics() {
        $sShowType = $this->sCurrentEvent;
        if ($sShowType == 'draft') {
            if (!E::IsAdmin()) {
                return parent::EventNotFound();
            }
        }

        return parent::EventTopics();
    }

    /**
     * Вывод топиков из определенного блога
     *
     */
    protected function EventShowBlog() {
        $sShowType = $this->GetParamEventMatch(0, 0);
        if ($sShowType != 'draft') {
            return parent::EventShowBlog();
        }
        if (!E::IsAdmin()) {
            return parent::EventNotFound();
        }
        $sBlogUrl = $this->sCurrentEvent;
        /**
         * Проверяем есть ли блог с таким УРЛ
         */
        if (!($oBlog = E::ModuleBlog()->GetBlogByUrl($sBlogUrl))) {
            return parent::EventNotFound();
        }
        /**
         * Определяем права на отображение закрытого блога
         */
        if ($oBlog->getType() == 'close'
            and (!$this->oUserCurrent
                 or !in_array(
                    $oBlog->getId(),
                    E::ModuleBlog()->GetAccessibleBlogsByUser($this->oUserCurrent)
                )
            )
        ) {
            $bCloseBlog = true;
        }
        else {
            $bCloseBlog = false;
        }
        /**
         * Меню
         */
        $this->sMenuSubItemSelect = $sShowType;
        $this->sMenuSubBlogUrl    = $oBlog->getUrlFull();
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(1, 2) ? $this->GetParamEventMatch(1, 2) : 1;

        if (!$bCloseBlog) {
            /**
             * Получаем список топиков
             */
            $aResult = E::ModuleTopic()->GetTopicsByBlog($oBlog, $iPage, Config::Get('module.topic.per_page'), $sShowType, null);
            $aTopics = $aResult['collection'];
            /**
             * Формируем постраничность
             */
            $aPaging = E::ModuleViewer()->MakePaging(
                $aResult['count'],
                $iPage,
                Config::Get('module.topic.per_page'),
                Config::Get('pagination.pages.count'),
                $oBlog->getUrlFull() . $sShowType,
                array()
            );
            /**
             * Получаем число новых топиков в текущем блоге
             */
            $this->iCountTopicsBlogNew = E::ModuleTopic()->GetCountTopicsByBlogNew($oBlog);

            E::ModuleViewer()->Assign('aPaging', $aPaging);
            E::ModuleViewer()->Assign('aTopics', $aTopics);
        }
        /**
         * Выставляем SEO данные
         */
        $sTextSeo = strip_tags($oBlog->getDescription());
        E::ModuleViewer()->SetHtmlDescription(func_text_words($sTextSeo, Config::Get('seo.description_words_count')));
        /**
         * Получаем список юзеров блога
         */
        $aBlogUsersResult          = E::ModuleBlog()->GetBlogUsersByBlogId(
            $oBlog->getId(),
            ModuleBlog::BLOG_USER_ROLE_USER,
            1,
            Config::Get('module.blog.users_per_page')
        );
        $aBlogUsers                = $aBlogUsersResult['collection'];
        $aBlogModeratorsResult     = E::ModuleBlog()->GetBlogUsersByBlogId($oBlog->getId(), ModuleBlog::BLOG_USER_ROLE_MODERATOR);
        $aBlogModerators           = $aBlogModeratorsResult['collection'];
        $aBlogAdministratorsResult = E::ModuleBlog()->GetBlogUsersByBlogId($oBlog->getId(), ModuleBlog::BLOG_USER_ROLE_ADMINISTRATOR);
        $aBlogAdministrators       = $aBlogAdministratorsResult['collection'];
        /**
         * Для админов проекта получаем список блогов и передаем их во вьювер
         */
        if ($this->oUserCurrent and $this->oUserCurrent->isAdministrator()) {
            $aBlogs = E::ModuleBlog()->GetBlogs();
            unset($aBlogs[$oBlog->getId()]);

            E::ModuleViewer()->Assign('aBlogs', $aBlogs);
        }
        /**
         * Вызов хуков
         */
        E::ModuleHook()->Run('blog_collective_show', array('oBlog' => $oBlog, 'sShowType' => $sShowType));
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aBlogUsers', $aBlogUsers);
        E::ModuleViewer()->Assign('aBlogModerators', $aBlogModerators);
        E::ModuleViewer()->Assign('aBlogAdministrators', $aBlogAdministrators);
        E::ModuleViewer()->Assign('iCountBlogUsers', $aBlogUsersResult['count']);
        E::ModuleViewer()->Assign('iCountBlogModerators', $aBlogModeratorsResult['count']);
        E::ModuleViewer()->Assign('iCountBlogAdministrators', $aBlogAdministratorsResult['count'] + 1);
        E::ModuleViewer()->Assign('oBlog', $oBlog);
        E::ModuleViewer()->Assign('bCloseBlog', $bCloseBlog);
        /**
         * Устанавливаем title страницы
         */
        E::ModuleViewer()->AddHtmlTitle($oBlog->getTitle());
        E::ModuleViewer()->SetHtmlRssAlternate(Router::GetPath('rss') . 'blog/' . $oBlog->getUrl() . '/', $oBlog->getTitle());
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('blog');
    }
}
