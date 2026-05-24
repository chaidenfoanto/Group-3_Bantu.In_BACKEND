# Jobaile_BACKEND

[![Contributors](https://img.shields.io/github/contributors/chaidenfoanto/Jobaile_BACKEND?style=for-the-badge)](https://github.com/chaidenfoanto/Jobaile_BACKEND/graphs/contributors)

[contributors-shield]: https://img.shields.io/github/contributors/chaidenfoanto/Jobaile_BACKEND.svg?style=for-the-badge]

[![LinkedIn Franklin Jaya](https://img.shields.io/badge/LinkedIn-Franklin%20Jaya-0A66C2?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/franklin-jaya-6a3697364/) [![LinkedIn Chaiden](https://img.shields.io/badge/LinkedIn-Chaiden-0A66C2?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/chaidenfoanto/?locale=en) [![LinkedIn Felicia Wijaya](https://img.shields.io/badge/LinkedIn-Felicia%20Wijaya-0A66C2?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/felicia-wijaya-a9a795288/)

[linkedin-shield]: https://img.shields.io/badge/LinkedIn-0A66C2?style=for-the-badge&logo=linkedin&logoColor=white



<!-- PROJECT LOGO -->
<p align="center">
  <img src="jobailelogo.png" width="250">
</p>
<br />


<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#built-with">Built With</a></li>
        <li><a href="#project-dependencies">Project Dependencies</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li>
      <a href="#usage">Usage</a>
    </li>
    <li>
      <a href="#development-team">Development Team</a>
    </li>
    <li>
      <a href="#contact">Contact</a>
    </li>
  </ol>
</details>



<!-- ABOUT THE PROJECT -->
## About The Project

<p align="center">
  <img src="mokupjobaile.png" width="250">
</p>

Jobaile is a mobile and web-based recruitment platform designed to connect recruiters with domestic workers such as housemaids (ART), caregivers, cleaners, and other household service providers.

The application was developed to help address trust issues that are still common in domestic worker recruitment processes. Many employers experience difficulties in finding reliable workers, while workers also struggle to access secure and trustworthy job opportunities.

Jobaile provides a digital platform that simplifies the recruitment process through user verification, worker profiles, communication features, and job matching systems.

The system consists of two main roles:

### Recruiter
Recruiters or employers can:

- Search for domestic workers
- View worker profiles and information
- Monitor application status
- Communicate with workers
- Find workers that match their requirements

### Worker
Workers can:

- Create professional profiles
- Apply for available jobs
- Showcase skills and experiences
- Receive job opportunities from recruiters
- Build credibility through profile information

Main features include:

- Domestic worker recruitment platform
- Recruiter and worker role management
- Real-time communication features
- Profile and identity management
- Job searching and matching system
- Mobile-friendly user experience
- Secure authentication system

<p align="right">(<a href="#readme-top">back to top</a>)</p>



### Built With

This backend project was built with php followingg technologies:

<a href="https://laravel.com">
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" height="35">
</a>

### Project Dependencies

This project uses:

- Laravel Composer
- Laravel Sanctum
- Swagger
- Laraavel Reverb
- Laravel Tinker

Example imports used in the project:

```laravel
"require": {
        "php": "^8.2",
        "darkaonline/l5-swagger": "^9.0",
        "laravel/framework": "^11.31",
        "laravel/reverb": "^1.0",
        "laravel/sanctum": "^4.0",
        "laravel/tinker": "^2.9",
        "otnansirk/laravel-dana": "^2.3",
        "thiagoprz/eloquent-composite-key": "^1.0",
        "zircote/swagger-php": "^5.1"
    },
```

## Getting Started

Follow these steps to set up the laravel project locally

### Prerequisites

Make sure you have installed the following software:

- PHP 8.2+
- Composer
- Git
- MySQL 

Check your installation:

```sh
php --version
composer --version
git --version
```

---

### Installation

1. Clone the repository

```sh
git clone https://github.com/your_username/your_repository.git
```

2. Navigate to the project folder

```sh
cd your_repository
```

3. Install project dependencies

```sh
composer install
```

4. Copy the environment configuration file

```sh
cp .env.example .env
```

**Windows (PowerShell)**

```powershell
copy .env.example .env
```

5. Generate the Laravel application key

```sh
php artisan key:generate
```

6. Configure your database in the `.env` file

Example:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=root
DB_PASSWORD=
```

7. Run database migrations

```sh
php artisan migrate
```

8. Start the Laravel development server

```sh
php artisan serve
```

The backend server will run at:

```txt
http://127.0.0.1:8000
```

---

<!-- USAGE EXAMPLES -->
## Usage

This project consists of two separate repositories:

- Frontend (Flutter)
- Backend (Laravel API)

Repository Links:
- Frontend Recruiter: https://github.com/chaidenfoanto/Jobaile_FRONTEND_Recruiter
- Backend: https://github.com/chaidenfoanto/Jobaile_BACKEND

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- CONTACT -->
## Contact

Franklin Jaya - [@franklinjaya_](https://www.instagram.com/franklinjaya_/) - franklinjaya827@gmail.com - [Franklin_Github](https://github.com/FranklinJaya2006)

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## Development Team

This Project are developed by **Jobaile Development Team**, which consist of three people:

1. **Chaiden Richardo Foanto**  
2. **Franklin Jaya** 
3. **Felicia Wijaya** 


<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[Laravel.com]: https://img.shields.io/badge/Laravel-%23FF2D20.svg?logo=laravel&logoColor=white
[Laravel-url]: https://laravel.com
