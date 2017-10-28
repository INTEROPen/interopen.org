<?php

  $current_filename = basename($_SERVER["PHP_SELF"]);

  $page_configuration = array(
            "index.php" => array("NAME" => "Home", "LINK" => "./"),
            "about.php" => array("NAME" => "About", "LINK" => "about"),
            "DROPDOWN" => array("NAME" => "Standards development",
                                "ELEMENTS" => array("standards.php" => array("NAME" => "Overview", "LINK" => "standards"),
                                                    "resource-profiles.php" => array("NAME" => "FHIR CareConnect Resource Profiles", "LINK" => "resource-profiles"),
                                                    "connectathon.php" => array("NAME" => "Connectathon", "LINK" => "connectathon"),
                                                    "interoperability-case-studies.php" => array("NAME" => "Use Cases", "LINK" => "interoperability-case-studies"),
                                                    "https://nhsconnect.github.io/CareConnectAPI/" => array("NAME" => "CareConnect API", "LINK" => "https://nhsconnect.github.io/CareConnectAPI/"))),
            "events.php" => array("NAME" => "Meetings &amp; events", "LINK" => "events"),
            "news.php" => array("NAME" => "News &amp; media", "LINK" => "news"),
            "discuss.php" => array("NAME" => "Discuss", "LINK" => "discuss"),
            "http://www.interopsummit.com" => array("NAME" => "<b><span style='color: #1b5a7d'>@INTEROP</span><span style='color: #F16D3B'>SUMMIT</span></b>", "LINK" => "http://www.interopsummit.com"));

  function writeNavbarItem($page_filename, $page_name, $page_link, $current_filename)
  {
    $active = ' class="active"';

    if ($page_filename != $current_filename)
      $active = '';

    echo '<li' . $active . '><a href="' . $page_link . '">' . $page_name . '</a></li>';
  }

  function writeNavbarItems($page_configuration, $current_filename)
  {
    foreach ($page_configuration as $key => $value)
    {
      if ($key == "DROPDOWN")
      {
        echo '<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">' . $value["NAME"] . '<span class="caret"></span></a><ul class="dropdown-menu">';

        foreach ($value["ELEMENTS"] as $key2 => $value2)
          writeNavbarItem($key2, $value2["NAME"], $value2["LINK"], $current_filename);

        echo '</ul></li>';
      }
      else
      {
        writeNavbarItem($key, $value["NAME"], $value["LINK"], $current_filename);
      }
    }
  }

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Interopen - Healthcare IT interoperability in the UK</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!--Font Awesome http://fontawesome.io-->
    <link rel="stylesheet" href="fonts/font-awesome/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700%7COpen+Sans:400" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
	<link rel="shortcut icon" href="~/favicon.ico" type="image/x-icon">
	<link rel="icon" href="~/favicon.ico" type="image/x-icon">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <?php include_once("analyticstracking.php") ?>

    <div class="container">

      <div class="logo-header">
        <a href="./"><img src="images/logo.png" alt="INTEROPen logo" />
        <h1>INTEROPen</h1></a>
        <div class="logo-header-right"><h4>An Open Collaborative Leading Healthcare IT Interoperability</h4></div>
        <div class="logo-header-clear"></div>
      </div>

      <nav class="navbar navbar-default">
        <div class="container-fluid">

      <ul class="nav navbar-nav">
<?php

  writeNavbarItems($page_configuration, $current_filename);

?>
        </ul>

        </div>

      </nav>
      <div style="overflow: hidden; height: 0px; width: 0px;">.</div>
<!-- end header -->
