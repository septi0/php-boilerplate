<html>
<head>
    <title>My Web Site - <?= $ctx_page_view ?></title>
</head>
<body>
    <div id="content">
        <?= $this->renderPartial($ctx_page_view, $ctx_page_context) ?>
    </div>
</html>