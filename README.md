# The Fourth Ink

A dynamic news portal and contributor publishing platform built with Laravel 12 and MySQL.
It combines categorised news with **Writers' Block**, where registered contributors write
opinions, reviews, analysis and features that are published only after editorial review.

News → Opinions → Reviews → Analysis → Features

## Main features

- **Public site:** homepage with featured, latest and trending stories, category pages with filters, search and article pages
- **Writers' Block:** contributors write articles, save drafts, submit for review, read editor feedback and resubmit
- **Editorial desk:** admins review submissions (approve, request revision, reject), publish and unpublish, and manage articles, users and categories
- **Roles:** Reader, Contributor and Administrator, enforced on the server with middleware and policies
- **Editorial workflow:** Draft → Pending Review → Revision Requested / Approved / Rejected → Published, with every change recorded in the article history

## Requirements

- PHP 8.2 or newer (use the latest patched 8.2 release) with the extensions mbstring, openssl, pdo_mysql, fileinfo, curl, tokenizer, xml, ctype and zip
- Composer 2
- Node.js 20 or newer, with npm
- MySQL or MariaDB (XAMPP works)

## Installation

```bash
git clone https://github.com/polaris-tono/the-fourth-ink.git
cd the-fourth-ink
composer install
npm install
copy .env.example .env
php artisan key:generate
```

(On macOS or Linux use `cp` instead of `copy`.)

1. Start MySQL and create the database:
    ```bash
    mysql -u root -e "CREATE DATABASE fourth_ink CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    ```
2. Open `.env` and set `SEED_USER_PASSWORD` to a password of your choice. It is used for the demo accounts.
3. Create the tables and demo data:
    ```bash
    php artisan migrate --seed
    php artisan db:seed --class=SampleContentSeeder
    ```
4. Run the app in two terminals:
    ```bash
    npm run dev
    php artisan serve
    ```
5. Open http://127.0.0.1:8000

## Demo accounts

Every demo account uses the password you set in `SEED_USER_PASSWORD`.

| Role          | Email                      |
| ------------- | -------------------------- |
| Administrator | admin@fourthink.test       |
| Contributor   | contributor@fourthink.test |
| Reader        | reader@fourthink.test      |

## Project parts and owners

| Part                                 | Owner          | Main folders                                                                                                    |
| ------------------------------------ | -------------- | --------------------------------------------------------------------------------------------------------------- |
| 1. Public site and news API          | polaris-tono   | `app/Http/Controllers/Site`, public views, news API service                                                     |
| 2. Authentication and Writers' Block | fah-fatima| `Controllers/Auth`, `Controllers/Contributor`, `EnsureRole`, `ArticlePolicy`, `views/auth`, `views/contributor` |
| 3. Editorial desk and administration | to be assigned | `EditorialWorkflowService`, `Controllers/Admin`, `views/admin`                                                  |

Shared files (migrations, models, enums, seeders, layout, routes) are reviewed by everyone.

## Team workflow

- `main` always holds working code.
- Each part is developed on its own branch: `feature/public-site-news-api`, `feature/auth-writers-block` and `feature/admin-editorial`.
- Changes reach `main` through pull requests that another member reviews.
- Never commit `.env` or API keys.

## Development note

This project was developed with the help of an AI assistant (Claude, by Anthropic) for planning, code generation and debugging. All team members have reviewed and can explain the code in their own part. (Adjust this statement to match your department's rules.)
