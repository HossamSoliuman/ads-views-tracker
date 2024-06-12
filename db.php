<?php

$db = new SQLite3('ad_watch.db');

$db->exec("CREATE TABLE IF NOT EXISTS ad_watches (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    gender TEXT CHECK(gender IN ('male', 'female', 'family')),
    watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL
)");

$default_username = 'admin';
$default_password = 'password';

$stmt = $db->prepare("INSERT OR IGNORE INTO users (id, username, password) VALUES (1, :username, :password)");
$stmt->bindValue(':username', $default_username, SQLITE3_TEXT);
$stmt->bindValue(':password', $default_password, SQLITE3_TEXT);
$stmt->execute();
