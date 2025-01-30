<?php

class Template
{
    private $views_path;
    private $default_layout;

    public function __construct($views_path, $default_layout = 'layout') {
        $this->views_path = $views_path;
        $this->default_layout = $default_layout;
    }

    public function renderPage($response, $page, $context = [], $layout = '') {
        $layout_name = $layout ?: $this->default_layout;
        $layout_context = [
            'page_view' => $page,
            'page_context' => $context,
        ];

        $content = $this->renderPartial($layout_name, $layout_context);
        return $response->withBody($content);
    }

    public function renderPartial($template_name, $context = []) {
        extract($context, EXTR_PREFIX_ALL, 'ctx');
        unset($context);

        ob_start();
        require $this->views_path . '/' . $template_name . '.php';
        $content = ob_get_clean();

        return $content;
    }
}
