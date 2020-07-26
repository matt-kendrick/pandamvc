<?php
class homeController extends controller
{
  function __construct()
  {
    echo "~homeController";
  }

  function index()
  {
      $this->render("index");
  }
}
?>
