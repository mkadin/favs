<?php

/**
 * @file FavController Class. Handles REST API calls.
 */
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class FavController {

  /**
   * Stores a new favorite in the database.
   */
  function create(Application $app, Request $request) {
    // Pull the parameters out of the request.
    $data = array(
      'name' => $request->request->get('name'),
      'lat' => $request->request->get('lat'),
      'lon' => $request->request->get('lon'),
      'address' => $request->request->get('address'),
    );

    // All fields are required.
    if (empty($data['name']) || empty($data['lat']) || empty($data['lon']) || empty($data['address'])) {
      $app->abort(400, 'The name, lat, lon, and address fields are all required.');
    }

    
    $model = new FavModel($app['db']);

    $model->setMultiple($data);

    $model->save();
    
    return $app->json($model->getAll(), 201);
  }

  /**
   * Lists all favorites.
   */
  function getList(Application $app, Request $request) {

    $data = FavModel::loadAll($app['db']);
    return $app->json($data, 200);
  }

  /**
   * Gets a specific favorite.
   */
  function read(Application $app, Request $request, $id) {

    $model = new FavModel($app['db'], $id);
    $id = $model->get('id');
    if (!empty($id)) {
      return $app->json($data, 200);
    }
    else {
      $app->abort('404', 'Favorite not found with that id');
    }
  }

  /**
   * Updates a specific favorite with new data.
   */
  function update(Application $app, Request $request, $id) {

    // Pull the parameters out of the request.
    $data = array(
      'name' => $request->request->get('name'),
      'lat' => $request->request->get('lat'),
      'lon' => $request->request->get('lon'),
      'address' => $request->request->get('address'),
      'id' => $id,
    );

    // All fields are required.
    if (empty($data['name']) && empty($data['lat']) && empty($data['lon']) && empty($data['address'])) {
      $app->abort(400, 'One of name, lat, lon, and address is required.');
    }

    
    $model = new FavModel($app['db'], $id);
    
    $model->setMultiple($data);
    
    $model->save();
    
    if ($model->get('id')) {
      return new Response(NULL, 200);
    }
  }
  
  /**
   * Deletes a favorite.
   */
  function delete(Application $app, Request $request, $id) {

    $model = new FavModel($app['db'], $id);
    
    $model->delete();

    // Return an empty response with code 204.
    return new Response(NULL, 204);
  }
  
}