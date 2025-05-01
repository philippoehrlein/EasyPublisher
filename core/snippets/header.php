<?php
// Titel aus meta.txt auslesen, falls vorhanden
$metaContent = file_exists(CONTENT_PATH . '/info/meta.txt') 
    ? file_get_contents(CONTENT_PATH . '/info/meta.txt')
    : null;
    
$siteTitle = 'easyPublisher';
if ($metaContent && preg_match('/^#\s+(.+)$/m', $metaContent, $matches)) {
    $siteTitle = trim($matches[1]);
}
?>

<div class="article-header">
    <div class="article-header--left" id="easyPublisher-toolbar">
        <a class="header-btn" href="<?= url() ?>" title="<?= $siteTitle ?>" tabindex="0" role="link">
            <?= $this->icon('home') ?>
        </a>
    </div>
    <div class="article-header--right">
    </div>
</div> 
