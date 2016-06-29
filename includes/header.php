<?php

  $pagename = basename($_SERVER["PHP_SELF"]);

  $pages = array("index.php" => array("NAME" => "Home", "LINK" => "./"),
            "about.php" => array("NAME" => "About", "LINK" => "about"),
            "events.php" => array("NAME" => "Meetings &amp; events", "LINK" => "events"),
            "news.php" => array("NAME" => "News &amp; media", "LINK" => "news"),
            "discuss.php" => array("NAME" => "Discuss", "LINK" => "discuss"));
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Interopen - Supplier led healthcare IT interoperability in the UK</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700%7COpen+Sans:400" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    
    <div class="container">
    
      <div class="logo-header">
        <a href="./"><img src="images/logo.png" alt="INTEROPen logo" />
        <h1>INTEROPen</h1></a>
        <div class="logo-header-right"><h4>Supplier-led healthcare IT interoperability</h4></div>
        <div class="logo-header-clear"></div>  
      </div>
      
      <nav class="navbar navbar-default">
        <div class="container-fluid">
    
      <ul class="nav navbar-nav">  
<?php
  
  foreach ($pages as $key => $value) 
  {
    $active = ' class="active"';
    
    if ($pagename != $key)
      $active = '';
    
    echo '<li' . $active . '><a href="' . $value["LINK"] . '">' . $value["NAME"] . '</a></li>';
  }
          
?>
        </ul>
      
        </div>
          
      </nav>
      <div style="overflow: hidden; height: 0px; width: 0px;">.</div>
<!-- end header -->
