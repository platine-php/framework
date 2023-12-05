## PLATINE FRAMEWORK
**Platine Framework is a lightweight, high-performance, simple and elegant PHP Web framework**

[![Project Status](http://opensource.box.com/badges/active.svg)](http://opensource.box.com/badges)
[![Latest Stable Version](https://poser.pugx.org/platine-php/framework/v/stable)](https://packagist.org/packages/platine-php/framework)
[![Latest Unstable Version](https://poser.pugx.org/platine-php/framework/v/unstable)](https://packagist.org/packages/platine-php/framework)
[![Total Downloads](https://poser.pugx.org/platine-php/framework/downloads)](https://packagist.org/packages/platine-php/framework)
[![License](https://poser.pugx.org/platine-php/framework/license)](https://packagist.org/packages/platine-php/framework)  
![Tests](https://github.com/platine-php/framework/actions/workflows/ci.yml/badge.svg)
[![Quality Score](https://img.shields.io/scrutinizer/g/platine-php/framework.svg?style=flat-square)](https://scrutinizer-ci.com/g/platine-php/framework)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/1ae48400b20c49cf94e716e355d042d0)](https://app.codacy.com/gh/platine-php/framework/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/1ae48400b20c49cf94e716e355d042d0)](https://app.codacy.com/gh/platine-php/framework/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)

Here are some of the features of Platine Framework:  

- It supports both web and command line applications.
- It has a modular architecture that lets you use only the components you need.
- It provides a powerful routing system that supports RESTful APIs, middleware, and parameters validation.
- It has a dependency injection container that allows you to manage your services and dependencies.
- It has a template engine that supports layouts, inheritance, and caching.
- It has a database abstraction layer that supports multiple drivers and query builders.
- It has a validation library that supports custom rules and error messages.
- It has a testing library that helps you write unit and integration tests.
- It has a console library that helps you create and run commands.
- It has a configuration library that supports multiple formats and environments.

### Requirements 
You can use **Platine Framework** for web or command line application. For web application: 
- Any web server **(Apache, nginx, lighttp, etc.)**
- **PHP >= 7.4**, **PHP 8** 

For CLI application just PHP executable is enough. 

### Why another PHP Framework?
Well, I created Platine Framework for my own project, but decided to share with everyone.  
I was also tired of all those bloated PHP Framework, with steep learning curve that create another language on top of PHP. Really?!  
So this one is smaller. It is not bloated and contains some useful libraries, functions and tricks. And above all, it's damn simple.

### Installation
Platine Framework can be installed using composer
```bash
composer require platine-php/framework
```

### Install on docker
If you want to test platine using docker follow the step below:
- Build the framework image
```bash
docker-compose build framework
```
- Run the containers
```bash
docker-compose up -d
```
- Install composer dependencies
```bash
docker-compose exec framework composer install --no-interaction
```

### License
MIT License See [LICENSE.MD](LICENSE.MD)

### Resources
- [Documentation](https://docs.platine-php.com) 
- Tutorial Youtube (French): [Budget Management Application](https://www.youtube.com/playlist?list=PLbUHyVcL-6Q8H6ip40eSdpt4MjRXYGPz9)
