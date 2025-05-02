![easyPublisher](.github/easypublisher.png)

# easyPublisher

A super lightweight publishing tool for writers who want to focus on content, not technology.

> ⚠️ Currently in Beta - Features and structure might change

## What is easyPublisher?

easyPublisher is a simple, file-based publishing system that lets you write and publish content without any technical setup. Just write your content in Markdown, drop it in the content folder, and you're done.

## Features

- **Zero Configuration**: No setup required, just start writing
- **Markdown Support**: Write in simple Markdown format
- **Clean URLs**: Automatic generation of readable URLs
- **Wiki-Style Links**: Easy internal linking between articles
- **Image Support**: Use both local and remote images
- **File-Based**: No database required, just plain text files
- **Lightweight**: Simple PHP-based system that runs anywhere

## Getting Started

1. Drop your Markdown files into the `/content` folder
2. That's it! Your content is automatically published

## File Structure

```
/content/           # Your content goes here
  ├── info/        # Special content folder (see below)
  └── *.txt        # Your markdown files
/core/             # System files (don't touch)
  ├── classes/     # Core functionality
  ├── templates/   # HTML templates
  ├── assets/      # Static assets
  ├── helpers/     # Helper functions
  └── snippets/    # Reusable code snippets
```

## Writing Content

- Use Markdown for formatting
- Files are ordered alphabetically by filename
- Tip: Use numbers as prefixes (e.g., `00-intro.txt`) to control the order
- Use `[[filename|display text]]` for internal links
- Images can be local or remote URLs

## The Info Folder

The `/content/info` folder contains special content that's automatically integrated into your site:

- `index.txt`: Custom content for your homepage
- `404.txt`: Custom 404 error page
- `meta.txt`: Site-wide meta information
- `config.txt`: Basic site configuration
- `privacy.txt`: Privacy policy page
- `imprint.txt`: Legal imprint page
- `contact.txt`: Contact information page

These files are optional

## Perfect For

- Writers who want to focus on content
- Non-technical users
- Essays and articles
- Personal notes and thoughts
- Small to medium publications

## Requirements

- PHP 8.0 or higher
- A web server (Apache/Nginx)
- Write permissions for the content folder

## Customization

easyPublisher is fully customizable through:

- **Snippets**: The easiest way to customize your site. These are reusable parts that you can modify:
  - `header.php`: Site header
  - `navigation.php`: Navigation menu
  - `footer.php`: Site footer
  - `head.php`: Meta tags and CSS includes
  - `scripts.php`: JavaScript includes

- **Templates**: Modify the look and feel by editing the template files
- **CSS**: Add your own styles or modify existing ones
- **JavaScript**: Add interactivity and custom features
- **Plugins**: Extend functionality with additional plugins

The system uses a simple template structure:
- `layout.php`: Base template for all pages
- `article.php`: Template for individual articles
- `index.php`: Template for the homepage

All assets (CSS, JS, icons) can be customized in the `assets` folder.

## License

easyPublisher is licensed under the GPL-3.0 with the following exceptions:

You may use easyPublisher for:
- Journalistic purposes
- Personal use
- Educational purposes
- Non-profit organizations

You may NOT:
- Sell or distribute easyPublisher as a product
- Offer easyPublisher as a hosted service
- Use easyPublisher for commercial SaaS offerings

For commercial use or if you're unsure about your use case, please contact me.
