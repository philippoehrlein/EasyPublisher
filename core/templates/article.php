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
    <?php if (isset($contentList)): ?>
        <?= $this->snippet('navigation', ['contentList' => $contentList]); ?>
    <?php endif; ?>
</div>
