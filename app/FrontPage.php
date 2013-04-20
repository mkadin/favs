<?php
/**
 * @file Callback for the app's front page.
 */
use Silex\Application;

function frontPage(Application $app) {
  return $app['twig']->render('page.twig', array(
    'title' => 'Favorite Locations!',
  ));
}
