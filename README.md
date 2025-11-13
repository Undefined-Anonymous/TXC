# TXC - ![](https://img.shields.io/badge/Version-1.3.1-purple.svg)


![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![SMTP](https://img.shields.io/badge/Email-D14836?style=for-the-badge&logo=gmail&logoColor=white)

A Lightweight And Efficient PHP-Based Chatroom Software Designed To Help People Connect, Communicate, And Grow.

---

## Overview

**TXC - 1.3.1** Is a Simple Yet Powerful Chatroom System Built With PHP. It Includes User Registration, Login, Message Handling, Email Verification, And SMTP Support Via PHPMailer. The System Is Easy To Deploy And Suitable For Small Communities, Private Groups, Or Educational Environments.

---

## Features

* Real-Time Message Fetching
* Secure Login And Registration
* Email Verification System
* SMTP Support Through PHPMailer
* Clean And Minimal UI
* Easily Customizable Asset Structure

---

## Setup Guide

### 1. Database

Copy And Import The Database Schema From:

```
database.sql
```

### 2. Configuration

Edit The File:

```
config.php
```

Inside this file, configure:

* Database Credentials (Host, User, Password, Database Name)
* SMTP Details (Host, Port, Username, Password, Security Type)

PHPMailer Files Are Included And Ready To Use.

---

## File Structure

```
TXC – 1.3.1
├── Assets/
│   ├── CSS/
│   │   └── styles.css
│   ├── JS/
│   │   └── main.js
│   └── PHPMailer/
│       ├── DSNConfigurator.php
│       ├── Exception.php
│       ├── OAuth.php
│       ├── OAuthTokenProvider.php
│       ├── PHPMailer.php
│       ├── POP3.php
│       └── SMTP.php
├── uploads/
│   └── Default/
│        └── default.png
├── config.php
├── database.sql
├── get_messages.php
├── index.php
├── licence
├── login.php
├── register.php
├── verify.php
└── verify_pending.php
```

---

## Goal

To Provide a Simple, Secure, And Friendly Chatroom Platform That Helps People Connect And Grow.

---

## License

This Project Is Distributed Under **THE CREATOR'S SOURCE LICENSE (CSL) v1.0**. Review The Full License Terms Here:

**[THE CREATOR'S SOURCE LICENSE (CSL) v1.0](https://github.com/Undefined-Anonymous/TXC/blob/Master/LICENSE.md)**
