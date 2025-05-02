<?php

$type = $_SERVER['REQUEST_URI'] === '/' ? 'WebSite' : 'Article';

$schema = [
    '@context' => 'https://schema.org',
    '@type' => $type,
    'name' => $meta['title'] ?? 'EasyPublisher',
    'url' => 'https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/')
];

if(isset($metaTagsArray['author'])) {
    $schema['author'] = [
        '@type' => 'Person',
        'name' => $metaTagsArray['author'],
        'url' => $metaTagsArray['authorurl'] ?? ''
    ];
}

if(isset($metaTagsArray['title'])) {
  $schema['title'] = $metaTagsArray['title'];
}

if(isset($metaTagsArray['description'])) {
    $schema['description'] = $metaTagsArray['description'];
}
?>

<script type="application/ld+json">
<?= json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>
</script>