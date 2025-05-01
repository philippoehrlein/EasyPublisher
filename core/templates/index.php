<div class="content content--index">
    <?php if (isset($customContent)): ?>
    <div class="index-content">
        <?= $customContent ?>
    </div>
    <?php else: ?>
    <ol class="content-list">
        <?php foreach ($contentList as $item): ?>
            <li>
                <a href="<?= url($item['slug']) ?>"><?= htmlspecialchars($item['title']) ?></a>
            </li>
        <?php endforeach; ?>
    </ol>
    <?php endif; ?>
    <footer>
        <?= $this->snippet('footer'); ?>
    </footer>
</div> 