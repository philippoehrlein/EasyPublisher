![Cover Inline Footnotes](.github/cover-inline-footnotes.png)

# Inline Footnotes for CommonMark

[![Version](https://img.shields.io/badge/version-1.0.1-blue.svg)](https://packagist.org/packages/philippoehrlein/inline-footnotes)

This PHP library extends the [League/CommonMark](https://github.com/thephpleague/commonmark) parser to support inline footnotes in the format `[^Footnote text]`. Unlike the reference-style footnote format commonly used in various [Markdown](https://daringfireball.net/projects/markdown/) implementations, this format allows you to enter the footnote text directly at the point where it's needed.

## How It Works

This extension doesn't handle the rendering of footnotes itself. Instead, it works as a preprocessor that:

1. Detects inline footnotes in the format `[^Footnote text]`
2. Converts them to standard reference-style footnotes before passing the content to the CommonMark parser
3. Relies on the standard `FootnoteExtension` for actual rendering

Therefore, you need to use both extensions together.

## Installation

Install the library via Composer:

```bash
composer require philippoehrlein/inline-footnotes
```

## Usage

```php
<?php

require 'vendor/autoload.php';

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\MarkdownConverter;
use PhilippOehrlein\InlineFootnotes\InlineFootnoteExtension;

// Create CommonMark environment
$environment = new Environment();

// Add standard CommonMark rules
$environment->addExtension(new CommonMarkCoreExtension());

// Add standard footnote extension (REQUIRED for rendering footnotes)
$environment->addExtension(new FootnoteExtension());

// Add inline footnotes extension (for preprocessing inline footnotes)
$environment->addExtension(new InlineFootnoteExtension());

// Create Markdown converter with the customized environment
$converter = new MarkdownConverter($environment);

// Convert Markdown with both types of footnotes
$markdown = "Here is a text with an inline footnote[^This is the footnote text].

And here is a text with a reference footnote[^1].

[^1]: This is the referenced footnote.";

$html = $converter->convert($markdown);

echo $html;
```

## Format

The inline footnotes can be used anywhere in the text and are converted to regular Markdown footnotes:

- `[^This is an inline footnote]` is converted to a numbered footnote during preprocessing.
- The footnotes can also contain complex content such as links and formatting.
- The extension also supports nested brackets within footnotes.

## Difference from Reference-Style Footnotes

The footnote syntax with numbered references (as implemented in MultiMarkdown, GitHub Flavored Markdown, and other Markdown flavors) requires a separate definition at the end of the document:

```markdown
Here is a text with a footnote[^1].

[^1]: This is the footnote text.
```

With this extension, the footnote text can be specified directly inline:

```markdown
Here is a text with a footnote[^This is the footnote text].
```

## Related Markdown Dialects

[MultiMarkdown](https://github.com/fletcher/MultiMarkdown), created by [Fletcher Penney](https://github.com/fletcher), supports both footnote styles: the reference-style footnotes ([^id]) and inline footnotes ([^This is an inline footnote.]). Many Markdown editors including iA Writer have adopted this syntax.

Unlike MultiMarkdown, this extension focuses specifically on providing inline footnote capability to the League/CommonMark parser. If you need reference-style footnotes, the standard League/CommonMark footnote extension already supports them.

## License

This project is licensed under the MIT License. 
