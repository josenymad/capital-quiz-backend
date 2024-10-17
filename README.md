# Quiz API Documentation

## Overview

The **Quiz API** provides a simple endpoint for retrieving a random country along with a list of three possible capital cities. Users can guess the correct capital, and the API will indicate which one is correct.

The API proxies an external endpoint to ensure secure data fetching while also implementing caching for efficiency.

## Endpoints

### - `GET /quiz`

#### Response

The response contains the name of the country, an array of three capital city options, and the correct capital as a separate field.

##### Response Example

```json
{
    "country": "France",
    "options": ["Berlin", "Paris", "Rome"],
    "correctCapital": "Paris"
}
```

## Installation & Setup

#### Requirements

-   [PHP](https://www.php.net/)
-   [Laravel](https://laravel.com/)
-   [Composer](https://getcomposer.org/)
-   External API URL (configured via environment variable)

#### Steps to Install

1. Clone the repository and navigate to new directory.

    `git clone https://github.com/josenymad/capital-quiz-backend.git`

    `cd capital-quiz-backend`

2. Install dependencies using composer

    `composer install`

3. Copy .env.example to .env and update the necessary environment variables.

    `cp .env.example .env`

4. Update the following variable with the external API URL

    `EXTERNAL_COUNTRIES_API_URL=`

5. Generate the Laravel application key.

    `php artisan key:generate`

6. Run migrations

    `php artisan migrate`

7. Start the application

    `php artisan serve`

8. The API will be accessible at http://localhost:8000/quiz.

## Testing

To run the tests, use the following command:

`php artisan test`

## Usage

Once this application is running, you can play the interactive quiz with [this](https://github.com/josenymad/capitals-quiz-front-end) UI.
