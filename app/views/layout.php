<html>
<head>
    <title>My Web Site - <?= $l->page_view ?></title>
</head>
<body>
    <div id="content">
        <?= $this->renderPartial($l->page_view, $l->page_context) ?>
    </div>
</html>
