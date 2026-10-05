# Lab Activity 7 — PDO Blog

A text-only blog with session login, password hashing, posts, and comments. The home feed and post pages require authentication. Users can edit or delete only their own posts and comments.

## Run in XAMPP

1. Start Apache and MySQL in the XAMPP Control Panel.
2. Open phpMyAdmin at `http://localhost/phpmyadmin` and import `database.sql`. This creates the `blog_site` database and its `users`, `posts`, and `comments` tables.
3. If your local MySQL credentials differ from the XAMPP defaults, update the settings at the top of `db.php`.
4. Visit `http://localhost/LabActivity7-Senajon-PDO/register.php` to create the first account. Then sign in and write a post.

The app uses native PDO prepared statements for queries containing user input. Values are passed separately to `execute()`; they are never concatenated into SQL. Comment deletion is a multi-step transaction: it locks and verifies the user's comment, deletes it, then commits. On failure, the code rolls back.

It also uses `password_hash()` / `password_verify()`, session ID regeneration, CSRF tokens on form submissions, and server-side validation. Browser validation is enabled on forms too.
