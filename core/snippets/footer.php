<div class="site-footer">
    <div class="footer-links">
        <?php if (file_exists(CONTENT_PATH . '/info/imprint.txt')): ?>
            <?php
            $imprintContent = file_get_contents(CONTENT_PATH . '/info/imprint.txt');
            $imprintTitle = extractTitle($imprintContent);
            ?>
            <a href="<?= url('imprint') ?>"><?= htmlspecialchars($imprintTitle) ?></a>
        <?php endif; ?>
        
        <?php if (file_exists(CONTENT_PATH . '/info/privacy.txt')): ?>
            <?php
            $privacyContent = file_get_contents(CONTENT_PATH . '/info/privacy.txt');
            $privacyTitle = extractTitle($privacyContent);
            ?>
            <a href="<?= url('privacy') ?>"><?= htmlspecialchars($privacyTitle) ?></a>
        <?php endif; ?>

        <?php if (file_exists(CONTENT_PATH . '/info/contact.txt')): ?>
            <?php
            $contactContent = file_get_contents(CONTENT_PATH . '/info/contact.txt');
            $contactTitle = extractTitle($contactContent);
            ?>
            <a href="<?= url('contact') ?>"><?= htmlspecialchars($contactTitle) ?></a>
        <?php endif; ?>
    </div>
    
    <div class="footer-credit">
        Made with <a href="https://github.com/philippoehrlein/easypublisher" target="_blank">easyPublisher</a>
    </div>
</div> 