# Screen Recording Chrome Extension
This is a backend API for a chrome extension that allows you to record your screen and audio from your microphone.

## Table of Contents
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [License](#license)

## Installation
- Create a folder for the project and clone the repository into the folder.
- Install the dependencies by running the following command in the terminal:
    > composer install

- Create an environment file by running the following command in the terminal:
    > cp .env.example .env

- Generate a new application key by running the following command in the terminal:
    > php artisan key:generate

- Create a database for the project and update the database credentials in the .env file.
- Run the following command in the terminal to migrate the database:
    > php artisan migrate

    Additionally, you can populate the database tables by running the following command instead:
    > php artisan migrate --seed

- Run the following command in the terminal to start the server:
    > php artisan serve

## Configuration
- The API can be configured to use a different database by updating the database credentials in the .env file.
- The API can be configured to use a different base URL by updating the APP_URL variable in the .env file. By default, the base URL is set to http://localhost:8000

## Usage
- Base URL: http://localhost:8000/api
- Detailed documentation of the API can be found here: [API Documentation](https://chromex.adeleke.tech/api/docs)

## License
The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
<p align="left">
<img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">
</p>
