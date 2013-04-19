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

    // Pull up the db service and save the new favorite.
    $db = $app['db'];

    // Insert the new data.
    $statement = $db->prepare('INSERT INTO favs (name, lat, lon, address) VALUES (:name, :lat, :lon, :address)');
    $statement->execute(array(
      ':name' => $data['name'],
      ':lat' => $data['lat'],
      ':lon' => $data['lon'],
      ':address' => $data['address'],
    ));

    // Get the new ID and return the data as JSON.
    $data['id'] = $db->lastInsertId();
    return $app->json($data, 201);
  }

  /**
   * Lists all favorites.
   */
  function getList(Application $app, Request $request) {

    // Pull up the db service.
    $db = $app['db'];

    // Query for the existing favs.
    $statement = $db->query('SELECT * FROM favs');
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the list and output it as JSON.
    $data = array();
    foreach ($results as $result) {
      $data[] = $result;
    }
    return $app->json($data, 200);
  }

  /**
   * Gets a specific favorite.
   */
  function read(Application $app, Request $request, $id) {

    // Pull up the db service.
    $db = $app['db'];

    // Query for the existing favs.
    $statement = $db->prepare('SELECT * FROM favs WHERE id = :id');
    $statement->execute(array(':id' => $id));
    $data = $statement->fetch(PDO::FETCH_ASSOC);
    if ($data) {
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

    // Pull up the db service.
    $db = $app['db'];

    // Prepare the SQL.
    $sql = 'UPDATE favs SET ';
    
    // Loop through each included field to prepare the piece of the SQL
    // statement and the array of PDO::execute() placeholders.
    foreach ($data as $field => $value) {
      if (!empty($value)) {
        $updates[] = $field . " = :" . $field;
        $params[':' . $field] = $value;
      }
    }
    
    // Turn the update SQL into a string separated by commas and include the
    // where clause.
    $sql .= implode(', ', $updates);
    $sql .= " WHERE id = :id";

    // Execute the query.
    $statement = $db->prepare($sql);
    $statement->execute($params);
    if ($data) {
      return new Response(NULL, 200);
    }
  }
  
  /**
   * Deletes a favorite.
   */
  function delete(Application $app, Request $request, $id) {
     // Pull up the db service.
    $db = $app['db'];
    
    // Execute the delete query.
    $statement = $db->prepare('DELETE FROM favs WHERE id = :id');
    $statement->execute(array(':id' => $id));
    
    // Return an empty response with code 204.
    return new Response(NULL, 204);
  }
  
}