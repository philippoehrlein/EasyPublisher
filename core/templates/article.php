<header>
    <?= $this->snippet('header'); ?>
</header>

<div class="content">
    <article class="content-article">
        <?= $content ?>
    </article> 
    <footer>
        <?= $this->snippet('footer'); ?>
    </footer>
    <?= $this->snippet('navigation', ['contentList' => $contentList]); ?>
</div>
