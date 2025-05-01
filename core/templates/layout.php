<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    
    <title><?= isset($title) ? htmlspecialchars($title) : 'easyPublisher' ?></title>
    <?php if (isset($metaTags)): ?>
        <?= $metaTags ?>
    <?php endif; ?>
    
    <link rel="stylesheet" href="<?= url('core/assets/css/style.css') ?>">
    
    <?php if (file_exists(THEME_PATH . '/assets/css/style.css')): ?>
    <link rel="stylesheet" href="<?= url('theme/assets/css/style.css') ?>">
    <?php endif; ?>
    <?php 
        $pluginFiles = glob(THEME_PATH . '/plugins/*.css');
        foreach ($pluginFiles as $file) {
            $url = 'theme/plugins/' . basename($file);
            echo "<link rel=\"stylesheet\" href=\"$url\">\n";
        }

        $pluginFiles = glob(CORE_PATH . '/assets/plugins/*.css');
        foreach ($pluginFiles as $file) {
            $url = 'core/assets/plugins/' . basename($file);
            echo "<link rel=\"stylesheet\" href=\"$url\">\n";
        }
    ?>
</head>
<body>
    <div class="container">
        <?= $content ?>
    </div>
    <script src="<?= url('core/assets/js/script.js') ?>"></script>
    <?php
        $pluginFiles = glob(CORE_PATH . '/assets/plugins/*.js');
        foreach ($pluginFiles as $file) {
            $url = 'core/assets/plugins/' . basename($file);
            echo "<script src=\"$url\"></script>\n";
        }
    ?>
    <?php
        $pluginFiles = glob(THEME_PATH . '/plugins/*.js');
        foreach ($pluginFiles as $file) {
            $url = 'theme/plugins/' . basename($file);
            echo "<script src=\"$url\"></script>\n";
        }
    ?>
</body>
</html> 