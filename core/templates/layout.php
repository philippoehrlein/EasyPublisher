<?= $this->snippet('head', ['metaTags' => $metaTags, 'title' => $title]) ?>
<body>
    <div class="container">
        <?= $content ?>
    </div>
    <?= $this->snippet('scripts') ?>
    <?= $this->snippet('schema', ['metaTagsArray' => $metaTagsArray, 'title' => $title]) ?>
</body>
</html> 