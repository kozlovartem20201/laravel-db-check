# Installation and Publishing Guide

This guide will help you install the package in a Laravel project and publish it to GitHub and Packagist.

## Installation in a Laravel Project

### Via Composer (After Publishing to Packagist)

Once the package is published to Packagist, you can install it in any Laravel project:

```bash
composer require kozlovartem/laravel-db-check
```

### Manual Installation (For Development)

If you want to test the package locally before publishing:

1. Clone or copy the package to a local directory.
2. In your Laravel project's `composer.json`, add a repository entry:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../laravel-db-check"
        }
    ]
}
```

3. Require the package:

```bash
composer require kozlovartem/laravel-db-check
```

## Publishing to GitHub

### Step 1: Initialize Git Repository

Navigate to the package directory and initialize a Git repository:

```bash
cd laravel-db-check
git init
git add .
git commit -m "Initial commit: Laravel Database Connection Checker"
```

### Step 2: Create GitHub Repository

1. Go to [GitHub](https://github.com) and log in with your account (kozlovartem20201).
2. Click the **+** icon in the top right and select **New repository**.
3. Set the repository name to `laravel-db-check`.
4. Add a description: "Laravel Artisan command for database connection testing and error diagnostics".
5. Choose **Public** visibility.
6. Do **not** initialize with README, .gitignore, or license (we already have these files).
7. Click **Create repository**.

### Step 3: Push to GitHub

```bash
git remote add origin https://github.com/kozlovartem20201/laravel-db-check.git
git branch -M main
git push -u origin main
```

### Step 4: Create a Release (Optional but Recommended)

1. Go to your repository on GitHub.
2. Click **Releases** → **Create a new release**.
3. Tag version: `v1.0.0`
4. Release title: `v1.0.0 - Initial Release`
5. Description: Brief description of features.
6. Click **Publish release**.

## Publishing to Packagist

### Step 1: Create Packagist Account

1. Go to [Packagist.org](https://packagist.org).
2. Sign up or log in using your GitHub account.

### Step 2: Submit Package

1. Click **Submit** in the top navigation.
2. Enter your GitHub repository URL: `https://github.com/kozlovartem20201/laravel-db-check`
3. Click **Check**.
4. If everything is valid, click **Submit**.

### Step 3: Enable Auto-Update (Recommended)

To automatically update Packagist when you push to GitHub:

1. Go to your package page on Packagist.
2. Click **Show API Token**.
3. Copy the webhook URL.
4. Go to your GitHub repository → **Settings** → **Webhooks** → **Add webhook**.
5. Paste the Packagist webhook URL.
6. Content type: `application/json`
7. Click **Add webhook**.

## Versioning

When making updates to the package:

1. Update the code.
2. Commit changes: `git commit -am "Description of changes"`
3. Create a new tag: `git tag v1.0.1`
4. Push changes and tags: `git push && git push --tags`

Packagist will automatically detect the new version if you've set up the webhook.

## Support

For issues or questions, please open an issue on the [GitHub repository](https://github.com/kozlovartem20201/laravel-db-check/issues).
