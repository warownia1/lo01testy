<?php


$report = <<< REPORT

Raport błędu.
Linia 2.\r\n
Linia.

REPORT;


$file = fopen( 'bug_report.txt', 'a' );

fwrite( $file, $report );

fclose( $file );

?>

<!DOCTYPE html>
<html>

<head>
<meta charset="utf-8" />
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<script type="text/javascript" src="script.js"></script>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>



</body>
</html>