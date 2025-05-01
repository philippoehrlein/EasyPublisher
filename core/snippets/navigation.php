<nav class="content-navigation" id="easyPublisher-navigation" role="navigation" aria-label="Navigation" hidden>
    <ol>
        <?php foreach ($contentList as $item): ?>
            <li class="<?= $currentSlug === $item['slug'] ? 'active' : '' ?>">
                <a href="<?= url($item['slug']) ?>" role="link" tabindex="1"><?= htmlspecialchars($item['title']) ?></a>
            </li>
        <?php endforeach; ?>
    </ol>
</nav> 