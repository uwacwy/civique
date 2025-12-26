# civique

A free, open-source WordPress theme that allows non-profit organizations to maintain a professional and easy-to-use online presence.

# Installation

1. Copy the `civique` directory to `wp-content/themes/`
2. Visit the Themes page in wordpress
3. Activate the theme
4. Use the customizer to adjust colors, etc.

## Development

Install dependencies once:

```bash
npm install
composer install --no-dev
```

Run Prettier or ESLint:

```bash
npm run format
npm run lint
```

Compile the primary stylesheet and modern TypeScript bundle:

```bash
npm run build:css
npm run build:js
```

Rebuild automatically on changes:

```bash
npm run watch:css
npm run watch:js
```

### Local WordPress via Docker

Spin up a disposable WordPress instance with the theme mounted from the repository:

```bash
docker compose up
```

The first run will download the images and initialize the database. Then visit http://localhost:8080 and complete the standard WordPress install flow using the following database settings (pre-configured in the container):

- Host: db
- Database: wordpress
- Username: wordpress
- Password: wordpress

Any changes you make in the civique directory reflect immediately inside the container at wp-content/themes/civique. Stop the stack with docker compose down. Database content persists between runs in the db-data volume.

## Build & Release

Create the distributable zip used for GitHub releases:

```bash
npm run build:theme
```

The build script reads the version from `civique/style.css` and writes a zip to `dist/`. When a release is published on GitHub, the `Build Theme Release` workflow packages the theme and attaches the artifact to the release automatically.

To allow WordPress sites to download private release assets, define the optional constant in `wp-config.php`:

```php
define("CIVIQUE_GITHUB_TOKEN", "ghp_xxx");
```

## Self-Hosted Updates

The theme checks GitHub releases for updates in the WordPress dashboard. Ensure each release contains a zip asset named `civique-{version}.zip` so the built-in updater can download the package successfully.
