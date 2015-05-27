<?php

require 'BazaDanych.php';
$link = BazaDanych::polacz();
$haslo = md5('haslo01');

$query = <<< QUERY

update uzytkownicy
set haslo='{$haslo}'

QUERY;

mysqli_query( $link, $query );

?>