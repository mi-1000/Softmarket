<?php

$dureeSession = 3600;

session_set_cookie_params(time() + $dureeSession, '/');
session_start();