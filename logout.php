<?php

require_once __DIR__ . '/php/config.php';

session_destroy();
session_start();
flash('success', 'Sesión cerrada correctamente.');
redirect('index.php');
