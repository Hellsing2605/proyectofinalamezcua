<?php
 $errors = array();

 /*--------------------------------------------------------------*/
 /* Function for Remove escapes special
 /* characters in a string for use in an SQL statement
 /*--------------------------------------------------------------*/
function real_escape($str){
  global $con;
  $escape = mysqli_real_escape_string($con,$str);
  return $escape;
}
/*--------------------------------------------------------------*/
/* Function for Remove html characters
/*--------------------------------------------------------------*/
function remove_junk($str){
  $str = nl2br($str);
  $str = htmlspecialchars(strip_tags($str, ENT_QUOTES));
  return $str;
}
/*--------------------------------------------------------------*/
/* Function for Uppercase first character
/*--------------------------------------------------------------*/
function first_character($str){
  $val = str_replace('-'," ",$str);
  $val = ucfirst($val);
  return $val;
}
/*--------------------------------------------------------------*/
/* Function for Checking input fields not empty
/*--------------------------------------------------------------*/
function validate_fields($var){
  global $errors;
  foreach ($var as $field) {
    $val = remove_junk($_POST[$field]);
    if(isset($val) && $val==''){
      $errors = $field ." No puede estar en blanco.";
      return $errors;
    }
  }
}
/*--------------------------------------------------------------*/
/* Function for Display Session Message
   Ex echo displayt_msg($message);
/*--------------------------------------------------------------*/
function display_msg($msg = '') {
  $output = array();
  if (!empty($msg)) {
      foreach ($msg as $key => $value) {
          $output  = "<div class=\"alert alert-{$key}\">";
          $output .= "<a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a>";
          $output .= remove_junk(first_character($value));
          $output .= "</div>";
      }
      return $output;
  } else {
      return "";
  }
}


/*--------------------------------------------------------------*/
/* Function for redirect
/*--------------------------------------------------------------*/
function redirect($url, $permanent = false) {
  if (headers_sent() === false) {
      header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
  }
  exit();
}


/*--------------------------------------------------------------*/
/* Function for Readable date time
/*--------------------------------------------------------------*/
function read_date($str){
     if($str)
      return date('d/m/Y', strtotime($str));
     else
      return null;
  }
/*--------------------------------------------------------------*/
/* Function for  Readable Make date time
/*--------------------------------------------------------------*/
function make_date(){
  return strftime("%Y-%m-%d %H:%M:%S", time());
}
/*--------------------------------------------------------------*/
/* Function for  Readable date time
/*--------------------------------------------------------------*/
function count_id(){
  static $count = 1;
  return $count++;
}
/*--------------------------------------------------------------*/
/* Function for Creting random string
/*--------------------------------------------------------------*/
function randString($length = 5)
{
  $str='';
  $cha = "0123456789abcdefghijklmnopqrstuvwxyz";

  for($x=0; $x<$length; $x++)
   $str .= $cha[mt_rand(0,strlen($cha))];
  return $str;
}
//Receptions
function join_reception_table() {
  global $db;
  $sql  = "SELECT * FROM receptions";
  return find_by_sql($sql);
}


function find_products_by_reception($reception_id) {
  global $db;
  $sql  = "SELECT products.name, reception_products.quantity ";
  $sql .= "FROM reception_products ";
  $sql .= "JOIN products ON reception_products.product_id = products.id ";
  $sql .= "WHERE reception_products.reception_id = '{$reception_id}'";
  return find_by_sql($sql);
}
function find_products_by_reception_id($reception_id) {
  global $db;
  $sql  = "SELECT p.name, rp.quantity ";
  $sql .= "FROM products p ";
  $sql .= "JOIN reception_products rp ON p.id = rp.product_id ";
  $sql .= "WHERE rp.reception_id = '{$reception_id}'";
  return find_by_sql($sql);
}
function find_reception_items($reception_id) {
  global $db;
  $sql  = "SELECT ri.id, ri.reception_id, ri.product_id, ri.quantity, p.name AS product_name, c.name AS category_name ";
  $sql .= "FROM reception_items ri ";
  $sql .= "JOIN products p ON p.id = ri.product_id ";
  $sql .= "LEFT JOIN categories c ON p.categorie_id = c.id ";
  $sql .= "WHERE ri.reception_id = '{$db->escape((int)$reception_id)}'";
  return find_by_sql($sql);
}

function find_transfer_items($transfer_id) {
  global $db;
  $sql  = "SELECT ti.id, ti.transfer_id, ti.product_id, ti.quantity, p.name AS product_name, c.name AS category_name ";
  $sql .= "FROM transfer_items ti ";
  $sql .= "JOIN products p ON p.id = ti.product_id ";
  $sql .= "LEFT JOIN categories c ON p.categorie_id = c.id ";
  $sql .= "WHERE ti.transfer_id = '{$db->escape((int)$transfer_id)}'";
  return find_by_sql($sql);
}
function find_request_items($request_id) {
  global $db;
  $sql  = "SELECT ri.id, ri.request_id, ri.product_id, ri.quantity, p.name AS product_name, c.name AS category_name ";
  $sql .= "FROM request_items ri ";
  $sql .= "JOIN products p ON p.id = ri.product_id ";
  $sql .= "LEFT JOIN categories c ON p.categorie_id = c.id ";
  $sql .= "WHERE ri.request_id = '{$db->escape((int)$request_id)}'";
  return find_by_sql($sql);
}
function find_return_items($return_id) {
  global $db;
  $sql  = "SELECT ri.id, ri.return_id, ri.product_id, ri.quantity, p.material_code, p.name AS product_name, c.name AS category_name ";
  $sql .= "FROM return_items ri ";
  $sql .= "JOIN products p ON p.id = ri.product_id ";
  $sql .= "LEFT JOIN categories c ON p.categorie_id = c.id ";
  $sql .= "WHERE ri.return_id = '{$db->escape((int)$return_id)}'";
  return find_by_sql($sql);
}

function find_liquidation_items($liquidation_id) {
  global $db;
  $sql  = "SELECT li.*, p.material_code, p.name AS product_name, c.name AS category_name ";
  $sql .= "FROM liquidation_items li ";
  $sql .= "LEFT JOIN products p ON li.product_id = p.id ";
  $sql .= "LEFT JOIN categories c ON p.categorie_id = c.id ";
  $sql .= "WHERE li.liquidation_id='{$db->escape($liquidation_id)}'";
  return find_by_sql($sql);
}



function find_products_by_used_by($used_by) {
  global $db;
  $sql  = "SELECT p.id, p.name, p.quantity, p.categorie_id, p.media_id, p.material_code, p.modelo, p.traslados, p.devoluciones, p.used_by, p.liquidaciones, s.name as supplier, c.name as categorie ";
  $sql .= "FROM products p ";
  $sql .= "LEFT JOIN suppliers s ON p.supplier_id = s.id "; // Asegúrate de que la tabla y columna son correctas
  $sql .= "LEFT JOIN categories c ON p.categorie_id = c.id ";
  $sql .= "WHERE p.used_by='{$used_by}'";
  return find_by_sql($sql);
}

function find_all_products() {
  global $db;
  $sql  = "SELECT p.id, p.name, p.quantity, p.categorie_id, p.media_id, p.material_code, p.modelo, p.traslados, p.devoluciones, p.used_by, p.liquidaciones, s.name as supplier, c.name as categorie ";
  $sql .= "FROM products p ";
  $sql .= "LEFT JOIN suppliers s ON p.supplier_id = s.id ";
  $sql .= "LEFT JOIN categories c ON p.categorie_id = c.id ";
  return find_by_sql($sql);
}
function generate_reception_code() {
  global $db;
  $sql = "SELECT reception_code FROM receptions ORDER BY id DESC LIMIT 1";
  $result = $db->query($sql);
  if ($result && $db->num_rows($result) > 0) {
      $last_code = $db->fetch_assoc($result)['reception_code'];
      $numeric_part = (int)substr($last_code, 1); // la parte numérica del código
      $new_code = 'R' . str_pad($numeric_part + 1, 5, '0', STR_PAD_LEFT); // Incrementa y formatea el nuevo código
  } else {
      $new_code = 'R00001'; // Si no hay ningún registro, comienza con "R00001"
  }
  return $new_code;
}
function generate_transfer_code() {
  global $db;
  $sql = "SELECT transfer_code FROM transfers ORDER BY id DESC LIMIT 1";
  $result = $db->query($sql);
  if ($result && $db->num_rows($result) > 0) {
      $last_code = $db->fetch_assoc($result)['transfer_code'];
      $numeric_part = (int)substr($last_code, 1); // la parte numérica del código
      $new_code = 'T' . str_pad($numeric_part + 1, 5, '0', STR_PAD_LEFT); // Incrementa y formatea el nuevo código
  } else {
      $new_code = 'T00001'; // Si no hay ningún registro, comienza con "T0001"
  }
  return $new_code;
}
function generate_return_code() {
  global $db;
  $sql = "SELECT return_code FROM returns ORDER BY id DESC LIMIT 1";
  $result = $db->query($sql);
  if ($result && $db->num_rows($result) > 0) {
      $last_code = $db->fetch_assoc($result)['return_code'];
      $numeric_part = (int)substr($last_code, 1); //la parte numérica del código
      $new_code = 'D' . str_pad($numeric_part + 1, 5, '0', STR_PAD_LEFT); // Incrementa y formatea el nuevo código
  } else {
      $new_code = 'D00001'; // Si no hay ningún registro, comienza con "R00001"
  }
  return $new_code;
}
function generate_liquidation_code() {
  global $db;
  $sql = "SELECT liquidation_code FROM liquidations ORDER BY id DESC LIMIT 1";
  $result = $db->query($sql);
  if ($result && $db->num_rows($result) > 0) {
      $last_code = $db->fetch_assoc($result)['liquidation_code'];
      $numeric_part = (int)substr($last_code, 1); // la parte numérica del código
      $new_code = 'L' . str_pad($numeric_part + 1, 5, '0', STR_PAD_LEFT); // Incrementa y formatea el nuevo código
  } else {
      $new_code = 'L00001'; // Si no hay ningún registro, comienza con "L00001"
  }
  return $new_code;
}
function generate_request_code() {
  global $db;
  $sql = "SELECT request_code FROM requests ORDER BY id DESC LIMIT 1";
  $result = $db->query($sql);
  if ($result && $db->num_rows($result) > 0) {
      $last_code = $db->fetch_assoc($result)['request_code'];
      $numeric_part = (int)substr($last_code, 1); // la parte numérica del código
      $new_code = 'P' . str_pad($numeric_part + 1, 5, '0', STR_PAD_LEFT); // Incrementa y formatea el nuevo código
  } else {
      $new_code = 'P00001'; // Si no hay ningún registro, comienza con "R00001"
  }
  return $new_code;
}

function find_technician_inventory($technician_id) {
  global $db;
  $technician_id = (int)$technician_id;
  $sql  = "SELECT p.id AS product_id, p.name AS product_name, SUM(t.quantity) AS quantity ";
  $sql .= "FROM products p ";
  $sql .= "JOIN transfer_items t ON p.id = t.product_id ";
  $sql .= "JOIN transfers tr ON t.transfer_id = tr.id ";
  $sql .= "WHERE tr.technician_id = '{$db->escape($technician_id)}' ";
  $sql .= "GROUP BY p.id, p.name";
  return find_by_sql($sql);
}
function find_cuadrilla_inventory($cuadrilla_id) {
  global $db;
  $cuadrilla_id = (int)$cuadrilla_id;
  $sql  = "SELECT p.id AS product_id, p.name AS product_name, SUM(t.quantity) AS quantity ";
  $sql .= "FROM products p ";
  $sql .= "JOIN transfer_items t ON p.id = t.product_id ";
  $sql .= "JOIN transfers tr ON t.transfer_id = tr.id ";
  $sql .= "WHERE tr.cuadrilla_id = '{$db->escape($cuadrilla_id)}' ";
  $sql .= "GROUP BY p.id, p.name";
  return find_by_sql($sql);
}

function find_obras_inventory($obra_id) {
  global $db;
  $sql = "SELECT products.id as product_id, products.name as product_name, SUM(transfer_items.quantity) as quantity
          FROM transfer_items
          JOIN products ON products.id = transfer_items.product_id
          JOIN transfers ON transfers.id = transfer_items.transfer_id
          WHERE transfers.obra_id = '{$db->escape($obra_id)}'
          GROUP BY products.id";
  return find_by_sql($sql);
}

function find_obras_returns($obra_id) {
  global $db;
  $sql = "SELECT products.id as product_id, products.name as product_name, SUM(return_items.quantity) as quantity
          FROM return_items
          JOIN products ON products.id = return_items.product_id
          JOIN returns ON returns.id = return_items.return_id
          WHERE returns.obra_id = '{$db->escape($obra_id)}'
          GROUP BY products.id";
  return find_by_sql($sql);
}

function find_obras_liquidations($obra_id) {
  global $db;
  $sql = "SELECT products.id as product_id, products.name as product_name, SUM(liquidation_items.quantity) as quantity
          FROM liquidation_items
          JOIN products ON products.id = liquidation_items.product_id
          JOIN liquidations ON liquidations.id = liquidation_items.liquidation_id
          WHERE liquidations.obra_id = '{$db->escape($obra_id)}'
          GROUP BY products.id";
  return find_by_sql($sql);
}
function get_open_works() {
  global $db;
  $sql  = "SELECT * FROM obras WHERE status = 1"; // Consideramos status = 1 como 'Abierta'
  return find_by_sql($sql);
}
function find_product_by_id($id)
{
    global $db;
    $id = (int)$id;
    $sql = "SELECT p.*, c.name AS category_name FROM products p";
    $sql .= " LEFT JOIN categories c ON p.categorie_id = c.id";
    $sql .= " WHERE p.id='{$db->escape($id)}' LIMIT 1";
    $result = $db->query($sql);
    if ($result = $db->fetch_assoc($sql))
        return $result;
    else
        return null;
}
function find_products_by_warehouse($warehouse_id) {
  global $db;
  $sql = "SELECT p.*, c.name AS categorie, s.name AS supplier ";
  $sql .= "FROM products p ";
  $sql .= "JOIN categories c ON c.id = p.categorie_id ";
  $sql .= "JOIN suppliers s ON s.id = p.supplier_id ";
  $sql .= "WHERE p.warehouse_id = '{$warehouse_id}'";
  return find_by_sql($sql);
}
function search_products_in_warehouse($term, $warehouse_id) {
  global $db;
  $escaped_term = $db->escape($term);
  $sql  = "SELECT p.id, p.name, p.quantity, p.categorie_id, p.media_id, p.material_code, p.modelo, p.traslados, p.devoluciones, p.used_by, p.liquidaciones, ";
  $sql .= "c.name AS categorie, m.file_name AS image, s.name AS supplier ";
  $sql .= "FROM products p ";
  $sql .= "LEFT JOIN categories c ON c.id = p.categorie_id ";
  $sql .= "LEFT JOIN media m ON m.id = p.media_id ";
  $sql .= "LEFT JOIN suppliers s ON s.id = p.supplier_id ";
  $sql .= "WHERE (p.name LIKE '%{$escaped_term}%' OR p.material_code LIKE '%{$escaped_term}%' OR p.modelo LIKE '%{$escaped_term}%') ";
  $sql .= "AND p.warehouse_id = '{$warehouse_id}' ";
  $sql .= "ORDER BY p.id ASC";
  return find_by_sql($sql);
}
function find_technician_inventory_by_warehouse($technician_id, $warehouse_id, $product_id = null) {
  global $db;
  $sql  = "SELECT p.id as product_id, p.name as product_name, SUM(ti.quantity) as quantity ";
  $sql .= "FROM transfer_items ti ";
  $sql .= "JOIN transfers t ON t.id = ti.transfer_id ";
  $sql .= "JOIN products p ON p.id = ti.product_id ";
  $sql .= "WHERE t.technician_id = '{$technician_id}' ";
  $sql .= "AND t.warehouse_id = '{$warehouse_id}' ";

  // Si se pasa un product_id, filtrar por este producto y devolver solo la cantidad total
  if ($product_id) {
      $sql .= "AND p.id = '{$product_id}' ";
      $sql .= "GROUP BY p.id, p.name ";
      $result = $db->query($sql);
      $row = $result ? $result->fetch_assoc() : null;
      return $row ? (int)$row['quantity'] : 0;
  } else {
      $sql .= "GROUP BY p.id, p.name ";
      $sql .= "HAVING SUM(ti.quantity) > 0";
      $result = $db->query($sql);
      return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }
}
function find_cuadrilla_inventory_by_warehouse($cuadrilla_id, $warehouse_id, $product_id = null) {
  global $db;
  $sql  = "SELECT p.id as product_id, p.name as product_name, SUM(ti.quantity) as quantity ";
  $sql .= "FROM transfer_items ti ";
  $sql .= "JOIN transfers t ON t.id = ti.transfer_id ";
  $sql .= "JOIN products p ON p.id = ti.product_id ";
  $sql .= "WHERE t.cuadrilla_id = '{$cuadrilla_id}' ";
  $sql .= "AND t.warehouse_id = '{$warehouse_id}' ";

  if ($product_id) {
      $sql .= "AND p.id = '{$product_id}' ";
      $sql .= "GROUP BY p.id, p.name ";
      $result = $db->query($sql);
      $row = $result ? $result->fetch_assoc() : null;
      return $row ? (int)$row['quantity'] : 0;
  } else {
      $sql .= "GROUP BY p.id, p.name ";
      $sql .= "HAVING SUM(ti.quantity) > 0";
      $result = $db->query($sql);
      return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
  }
}



function find_products_by_warehouse_and_used_by($warehouse_id, $used_by) {
  global $db;
  $sql  = "SELECT p.id, p.name, p.quantity, p.categorie_id, p.media_id, p.material_code, p.modelo, p.traslados, p.devoluciones, p.used_by, p.liquidaciones, s.name as supplier, c.name as categorie ";
  $sql .= "FROM products p ";
  $sql .= "LEFT JOIN suppliers s ON p.supplier_id = s.id "; 
  $sql .= "LEFT JOIN categories c ON p.categorie_id = c.id ";
  $sql .= "WHERE p.warehouse_id = '{$warehouse_id}' AND p.used_by = '{$used_by}'";
  return find_by_sql($sql);
}
function join_reception_table_pagination($offset, $records_per_page) {
  global $db;
  $sql  = "SELECT r.id, r.reception_code, r.date, r.warehouse_id, w.name AS warehouse_name, ";
  $sql .= "r.supervisor_id, s.name AS supervisor_name ";
  $sql .= "FROM receptions r ";
  $sql .= "LEFT JOIN warehouses w ON r.warehouse_id = w.id ";
  $sql .= "LEFT JOIN supervisors s ON r.supervisor_id = s.id ";
  $sql .= "ORDER BY r.date DESC ";
  $sql .= "LIMIT {$offset}, {$records_per_page}";
  return find_by_sql($sql);
}
function find_receptions_by_date_with_pagination($date, $offset, $limit) {
  global $db;
  
  $sql  = "SELECT r.id, r.reception_code, r.date, r.warehouse_id, w.name AS warehouse_name ";
  $sql .= "FROM receptions r ";
  $sql .= "LEFT JOIN warehouses w ON r.warehouse_id = w.id ";
  $sql .= "WHERE r.date = '{$db->escape($date)}' ";
  $sql .= "ORDER BY r.date DESC ";
  $sql .= "LIMIT {$db->escape((int)$offset)}, {$db->escape((int)$limit)}";

  return find_by_sql($sql);
}
function count_receptions_by_date($date) {
  global $db;
  
  $sql  = "SELECT COUNT(id) AS total ";
  $sql .= "FROM receptions ";
  $sql .= "WHERE date = '{$db->escape($date)}'";
  $result = find_by_sql($sql);
  return array_shift($result); // Devuelve el total de recepciones
}
function join_transfer_table_pagination($offset, $records_per_page) {
  global $db;
  $sql  = "SELECT t.id, t.transfer_code, t.date, t.warehouse_id, w.name AS warehouse_name, ";
  $sql .= "t.supervisor_id, s.name AS supervisor_name ";
  $sql .= "FROM transfers t ";
  $sql .= "LEFT JOIN warehouses w ON t.warehouse_id = w.id ";
  $sql .= "LEFT JOIN supervisors s ON t.supervisor_id = s.id ";
  $sql .= "ORDER BY t.date DESC ";
  $sql .= "LIMIT {$offset}, {$records_per_page}";
  return find_by_sql($sql);
}
function find_transfer_by_date_with_pagination($date, $offset, $limit) {
  global $db;
  
  $sql  = "SELECT t.id, t.transfer_code, t.date, t.warehouse_id, w.name AS warehouse_name ";
  $sql .= "FROM transfers t ";
  $sql .= "LEFT JOIN warehouses w ON t.warehouse_id = w.id ";
  $sql .= "WHERE t.date = '{$db->escape($date)}' ";
  $sql .= "ORDER BY t.date DESC ";
  $sql .= "LIMIT {$db->escape((int)$offset)}, {$db->escape((int)$limit)}";

  return find_by_sql($sql);
}
function count_transfers_by_date($date) {
  global $db;
  
  $sql  = "SELECT COUNT(id) AS total ";
  $sql .= "FROM transfers ";
  $sql .= "WHERE date = '{$db->escape($date)}'";
  $result = find_by_sql($sql);
  return array_shift($result); // Devuelve el total de transferencias
}
function join_return_table_pagination($offset, $records_per_page) {
  global $db;
  $sql  = "SELECT r.id, r.return_code, r.date, r.warehouse_id, w.name AS warehouse_name, ";
  $sql .= "r.supervisor_id, s.name AS supervisor_name ";
  $sql .= "FROM returns r ";
  $sql .= "LEFT JOIN warehouses w ON r.warehouse_id = w.id ";
  $sql .= "LEFT JOIN supervisors s ON r.supervisor_id = s.id ";
  $sql .= "ORDER BY r.date DESC ";
  $sql .= "LIMIT {$offset}, {$records_per_page}";
  return find_by_sql($sql);
}

function find_return_by_date_with_pagination($date, $offset, $limit) {
  global $db;
  
  $sql  = "SELECT r.id, r.return_code, r.date, r.warehouse_id, w.name AS warehouse_name ";
  $sql .= "FROM returns r ";
  $sql .= "LEFT JOIN warehouses w ON r.warehouse_id = w.id ";
  $sql .= "WHERE r.date = '{$db->escape($date)}' ";
  $sql .= "ORDER BY r.date DESC ";
  $sql .= "LIMIT {$db->escape((int)$offset)}, {$db->escape((int)$limit)}";

  return find_by_sql($sql);
}
function count_returns_by_date($date) {
  global $db;
  
  $sql  = "SELECT COUNT(id) AS total ";
  $sql .= "FROM returns ";
  $sql .= "WHERE date = '{$db->escape($date)}'";
  $result = find_by_sql($sql);
  return array_shift($result); // Devuelve el total de devoluciones
}
function join_liquidation_table_pagination($offset, $records_per_page) {
  global $db;
  $sql  = "SELECT l.id, l.liquidation_code, l.date, l.warehouse_id, w.name AS warehouse_name, ";
  $sql .= "l.supervisor_id, s.name AS supervisor_name ";
  $sql .= "FROM liquidations l ";
  $sql .= "LEFT JOIN warehouses w ON l.warehouse_id = w.id ";
  $sql .= "LEFT JOIN supervisors s ON l.supervisor_id = s.id ";
  $sql .= "ORDER BY l.date DESC ";
  $sql .= "LIMIT {$offset}, {$records_per_page}";
  return find_by_sql($sql);
}
function find_liquidations_by_date_with_pagination($date, $offset, $limit) {
  global $db;
  
  $sql  = "SELECT l.id, l.liquidation_code, l.date, l.warehouse_id, w.name AS warehouse_name ";
  $sql .= "FROM liquidations l ";
  $sql .= "LEFT JOIN warehouses w ON l.warehouse_id = w.id ";
  $sql .= "WHERE l.date = '{$db->escape($date)}' ";
  $sql .= "ORDER BY l.date DESC ";
  $sql .= "LIMIT {$db->escape((int)$offset)}, {$db->escape((int)$limit)}";

  return find_by_sql($sql);
}
function count_liquidations_by_date($date) {
  global $db;
  
  $sql  = "SELECT COUNT(id) AS total ";
  $sql .= "FROM liquidations ";
  $sql .= "WHERE date = '{$db->escape($date)}'";
  $result = find_by_sql($sql);
  return array_shift($result); // Devuelve el total de liquidaciones
}
function find_product_devoluciones($technician_id, $warehouse_id, $product_id) {
  global $db;
  $sql  = "SELECT SUM(ri.quantity) as total_devoluciones ";
  $sql .= "FROM return_items ri ";
  $sql .= "JOIN returns r ON r.id = ri.return_id ";
  $sql .= "WHERE r.technician_id = '{$technician_id}' ";
  $sql .= "AND r.warehouse_id = '{$warehouse_id}' ";
  $sql .= "AND ri.product_id = '{$product_id}' ";
  $sql .= "GROUP BY ri.product_id";
  
  $result = $db->query($sql);
  $row = $result ? $result->fetch_assoc() : null;
  
  return $row ? (int)$row['total_devoluciones'] : 0;
}
function find_product_devoluciones_c($cuadrilla_id, $warehouse_id, $product_id) {
  global $db;
  $sql  = "SELECT SUM(ri.quantity) as total_devoluciones ";
  $sql .= "FROM return_items ri ";
  $sql .= "JOIN returns r ON r.id = ri.return_id ";
  $sql .= "WHERE r.cuadrilla_id = '{$cuadrilla_id}' ";
  $sql .= "AND r.warehouse_id = '{$warehouse_id}' ";
  $sql .= "AND ri.product_id = '{$product_id}' ";
  $sql .= "GROUP BY ri.product_id";
  
  $result = $db->query($sql);
  $row = $result ? $result->fetch_assoc() : null;
  
  return $row ? (int)$row['total_devoluciones'] : 0;
}
function find_product_liquidaciones($technician_id, $warehouse_id, $product_id) {
  global $db;
  $sql  = "SELECT SUM(li.quantity) as total_liquidaciones ";
  $sql .= "FROM liquidation_items li ";
  $sql .= "JOIN liquidations l ON l.id = li.liquidation_id ";
  $sql .= "WHERE l.technician_id = '{$technician_id}' ";
  $sql .= "AND l.warehouse_id = '{$warehouse_id}' ";
  $sql .= "AND li.product_id = '{$product_id}' ";
  $sql .= "GROUP BY li.product_id";

  $result = $db->query($sql);
  $row = $result ? $result->fetch_assoc() : null;

  return $row ? (int)$row['total_liquidaciones'] : 0;
}
function find_product_liquidaciones_c($cuadrilla_id, $warehouse_id, $product_id) {
  global $db;
  $sql  = "SELECT SUM(li.quantity) as total_liquidaciones ";
  $sql .= "FROM liquidation_items li ";
  $sql .= "JOIN liquidations l ON l.id = li.liquidation_id ";
  $sql .= "WHERE l.cuadrilla_id = '{$cuadrilla_id}' ";
  $sql .= "AND l.warehouse_id = '{$warehouse_id}' ";
  $sql .= "AND li.product_id = '{$product_id}' ";
  $sql .= "GROUP BY li.product_id";

  $result = $db->query($sql);
  $row = $result ? $result->fetch_assoc() : null;

  return $row ? (int)$row['total_liquidaciones'] : 0;
}

?>


