<?php

namespace WebCore;

class Template
{
    private $views_path;
    private $default_layout;
    private $global_context = [];

    public function __construct($views_path, $default_layout = 'layout')
    {
        $this->views_path = $views_path;
        $this->default_layout = $default_layout;
    }

    public function addGlobalCtx($key, $value)
    {
        $this->global_context[$key] = $value;
    }

    public function renderPage($page, $context = [], $layout = '')
    {
        $layout_name = $layout ?: $this->default_layout;

        $layout_context = [
            'page_view' => $page,
            'page_context' => $context
        ];

        $content = $this->renderPartial($layout_name, $layout_context);
        return $content;
    }

    public function renderPartial($template_name, $context = [])
    {
        $l = (object)$context;
        $g = (object)$this->global_context;

        ob_start();
        require $this->views_path . '/' . $template_name . '.php';
        $content = ob_get_clean();

        return $content;
    }
}
