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

class PluginDrafts_HookAddlink extends Hook
{
    public function RegisterHook()
    {
        if (E::IsAdmin()) {
            if (Config::Get('plugin.drafts.show_blog')) {
                $this->AddHook('template_menu_blog_blog_item', 'InjectBlogLink');
            }
            if (Config::Get('plugin.drafts.show_personal') || Config::Get('plugin.drafts.show_blog')) {
                $this->AddHook('template_menu_blog_index_item', 'InjectIndexLink');
            }
            if (Config::Get('plugin.drafts.show_profile')) {
                $this->AddHook('template_menu_profile_created_item', 'InjectProfileLink');
            }
        }
    }

    public function InjectBlogLink($aParam)
    {
        $sTemplatePath = Plugin::GetTemplatePath(__CLASS__) . 'inject_blog_link.tpl';
        if ($this->Viewer_TemplateExists($sTemplatePath)) {
            return $this->Viewer_Fetch($sTemplatePath);
        }
    }

    public function InjectIndexLink($aParam)
    {
        $sTemplatePath = Plugin::GetTemplatePath(__CLASS__) . 'inject_index_link.tpl';
        if ($this->Viewer_TemplateExists($sTemplatePath)) {
            return $this->Viewer_Fetch($sTemplatePath);
        }
    }

    public function InjectProfileLink($aParam)
    {
        $sTemplatePath = Plugin::GetTemplatePath(__CLASS__) . 'inject_profile_link.tpl';
        if ($this->Viewer_TemplateExists($sTemplatePath)) {
            return $this->Viewer_Fetch($sTemplatePath);
        }
    }
}
