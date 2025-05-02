<!DOCTYPE html>
<html lang="<?= ContentLoader::getConfig('lang'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    
    <title><?= isset($title) ? htmlspecialchars($title) : 'easyPublisher' ?></title>
    <?php if (isset($metaTags)): ?>
        <?= $metaTags ?>
    <?php endif; ?>
    
    <link rel="sitemap" type="application/xml" href="<?= url('sitemap.xml') ?>">
    
    <link rel="stylesheet" href="<?= url('core/assets/css/style.css') ?>">
    
    <?php if (file_exists(THEME_PATH . '/assets/css/style.css')): ?>
    <link rel="stylesheet" href="<?= url('theme/assets/css/style.css') ?>">
    <?php endif; ?>
    
    <?= getPluginCssTags() ?>
</head>