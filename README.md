# Project Name
Images to PDF

## Requirements
- PHP >= 7.3
- Composer
- Laravel >= 8.x

## Installation

1. Clone the repository:
    ```bash
    git@github.com:superAI4003/img-to-pdf.git
    cd img-to-pdf
    ```

2. Install dependencies:
    ```bash
    composer install
    ```

3. Copy the `.env.example` file to `.env`:
    ```bash
    cp .env.example .env
    ```
    set aws info
4. Generate an application key:
    ```bash
    php artisan key:generate
    ```

5. Start the local development server:
    ```bash
    php artisan serve
    ```

