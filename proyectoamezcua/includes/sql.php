<?php
  require_once('includes/load.php');

/*--------------------------------------------------------------*/
/* Function for find all database table rows by table name
/*--------------------------------------------------------------*/
function find_all($table) {
   global $db;
   if(tableExists($table))
   {
     return find_by_sql("SELECT * FROM ".$db->escape($table));
   }
}
/*--------------------------------------------------------------*/
/* Function for Perform queries
/*--------------------------------------------------------------*/
function find_by_sql($sql)
{
  global $db;
  $result = $db->query($sql);
  $result_set = $db->while_loop($result);
 return $result_set;
}
/*--------------------------------------------------------------*/
/*  Function for Find data from table by id
/*--------------------------------------------------------------*/
function find_by_id($table,$id)
{
  global $db;
  $id = (int)$id;
    if(tableExists($table)){
          $sql = $db->query("SELECT * FROM {$db->escape($table)} WHERE id='{$db->escape($id)}' LIMIT 1");
          if($result = $db->fetch_assoc($sql))
            return $result;
          else
            return null;
     }
}
/*--------------------------------------------------------------*/
/* Function for Delete data from table by id
/*--------------------------------------------------------------*/
function delete_by_id($table,$id)
{
  global $db;
  if(tableExists($table))
   {
    $sql = "DELETE FROM ".$db->escape($table);
    $sql .= " WHERE id=". $db->escape($id);
    $sql .= " LIMIT 1";
    $db->query($sql);
    return ($db->affected_rows() === 1) ? true : false;
   }
}
/*--------------------------------------------------------------*/
/* Function for Count id  By table name
/*--------------------------------------------------------------*/

function count_by_id($table){
  global $db;
  if(tableExists($table))
  {
    $sql    = "SELECT COUNT(id) AS total FROM ".$db->escape($table);
    $result = $db->query($sql);
     return($db->fetch_assoc($result));
  }
}
/*--------------------------------------------------------------*/
/* Determine if database table exists
/*--------------------------------------------------------------*/
function tableExists($table){
  global $db;
  $table_exit = $db->query('SHOW TABLES FROM '.DB_NAME.' LIKE "'.$db->escape($table).'"');
      if($table_exit) {
        if($db->num_rows($table_exit) > 0)
              return true;
         else
              return false;
      }
  }
 /*--------------------------------------------------------------*/
 /* Login with the data provided in $_POST,
 /* coming from the login form.
/*--------------------------------------------------------------*/
  function authenticate($username='', $password='') {
    global $db;
    $username = $db->escape($username);
    $password = $db->escape($password);
    $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
    $result = $db->query($sql);
    if($db->num_rows($result)){
      $user = $db->fetch_assoc($result);
      $password_request = sha1($password);
      if($password_request === $user['password'] ){
        return $user['id'];
      }
    }
   return false;
  }
  /*--------------------------------------------------------------*/
  /* Login with the data provided in $_POST,
  /* coming from the login_v2.php form.
  /* If you used this method then remove authenticate function.
 /*--------------------------------------------------------------*/
   function authenticate_v2($username='', $password='') {
     global $db;
     $username = $db->escape($username);
     $password = $db->escape($password);
     $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
     $result = $db->query($sql);
     if($db->num_rows($result)){
       $user = $db->fetch_assoc($result);
       $password_request = sha1($password);
       if($password_request === $user['password'] ){
         return $user;
       }
     }
    return false;
   }


  /*--------------------------------------------------------------*/
  /* Find current log in user by session id
  /*--------------------------------------------------------------*/
  function current_user(){
      static $current_user;
      global $db;
      if(!$current_user){
         if(isset($_SESSION['user_id'])):
             $user_id = intval($_SESSION['user_id']);
             $current_user = find_by_id('users',$user_id);
        endif;
      }
    return $current_user;
  }
  /*--------------------------------------------------------------*/
  /* Find all user by
  /* Joining users table and user gropus table
  /*--------------------------------------------------------------*/
  function find_all_user(){
      global $db;
      $results = array();
      $sql = "SELECT u.id,u.name,u.username,u.user_level,u.status,u.last_login,";
      $sql .="g.group_name ";
      $sql .="FROM users u ";
      $sql .="LEFT JOIN user_groups g ";
      $sql .="ON g.group_level=u.user_level ORDER BY u.name ASC";
      $result = find_by_sql($sql);
      return $result;
  }
  /*--------------------------------------------------------------*/
  /* Function to update the last log in of a user
  /*--------------------------------------------------------------*/

 function updateLastLogIn($user_id)
	{
		global $db;
    $date = make_date();
    $sql = "UPDATE users SET last_login='{$date}' WHERE id ='{$user_id}' LIMIT 1";
    $result = $db->query($sql);
    return ($result && $db->affected_rows() === 1 ? true : false);
	}

  /*--------------------------------------------------------------*/
  /* Find all Group name
  /*--------------------------------------------------------------*/
  function find_by_groupName($val)
  {
    global $db;
    $sql = "SELECT group_name FROM user_groups WHERE group_name = '{$db->escape($val)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Find group level
  /*--------------------------------------------------------------*/
  function find_by_groupLevel($level)
  {
    global $db;
    $sql = "SELECT group_level FROM user_groups WHERE group_level = '{$db->escape($level)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Function for cheaking which user level has access to page
  /*--------------------------------------------------------------*/
   function page_require_level($require_level){
     global $session;
     $current_user = current_user();
     $login_level = find_by_groupLevel($current_user['user_level']);
     //if user not login
     if (!$session->isUserLoggedIn(true)):
            $session->msg('d','Por favor Iniciar sesión...');
            redirect('index.php', false);
      //cheackin log in User level and Require level is Less than or equal to
     elseif($current_user['user_level'] <= (int)$require_level):
              return true;
      else:
            $session->msg("d", "¡Lo siento! No tienes permiso para ver la página.");
            redirect('home.php', false);
        endif;

     }
     
 
     function search_products($term, $warehouse_id = 0) {
      global $db;
      $escaped_term = $db->escape($term);
      
      $sql  = "SELECT p.id, p.name, p.quantity, p.categorie_id, p.media_id, p.material_code, p.modelo, p.traslados, p.devoluciones, p.used_by, p.liquidaciones, ";
      $sql .= "c.name AS categorie, m.file_name AS image, s.name AS supplier ";
      $sql .= "FROM products p ";
      $sql .= "LEFT JOIN categories c ON c.id = p.categorie_id ";
      $sql .= "LEFT JOIN media m ON m.id = p.media_id ";
      $sql .= "LEFT JOIN suppliers s ON s.id = p.supplier_id ";
      $sql .= "WHERE (p.name LIKE '%{$escaped_term}%' OR p.material_code LIKE '%{$escaped_term}%' OR p.modelo LIKE '%{$escaped_term}%')";
      
      // Si se especifica un warehouse_id, agregar una cláusula adicional
      if ($warehouse_id > 0) {
          $sql .= " AND p.warehouse_id = '{$warehouse_id}'";
      }
  
      $sql .= " ORDER BY p.id ASC";
      
      return find_by_sql($sql);
  }
  
  
  

function join_product_table() {
  global $db;
  $sql  = "SELECT p.id, p.name, p.quantity, p.categorie_id, p.media_id, p.material_code, p.modelo, p.traslados, p.devoluciones, p.liquidaciones, ";
  $sql .= "p.traslados, "; // Añadir la columna que se requiera
  $sql .= "p.devoluciones, ";
  $sql .= "p.used_by, ";
  $sql .= "p.liquidaciones, ";
  $sql .= "c.name AS categorie, m.file_name AS image, s.name AS supplier ";
  $sql .= "FROM products p ";
  $sql .= "LEFT JOIN categories c ON c.id = p.categorie_id ";
  $sql .= "LEFT JOIN media m ON m.id = p.media_id ";
  $sql .= "LEFT JOIN suppliers s ON s.id = p.supplier_id ";
  $sql .= "ORDER BY p.id ASC";
  return find_by_sql($sql);
}
  /*--------------------------------------------------------------*/
  /* Function for Finding all product name
  /* Request coming from ajax.php for auto suggest
  /*--------------------------------------------------------------*/

   function find_product_by_title($product_name){
     global $db;
     $p_name = remove_junk($db->escape($product_name));
     $sql = "SELECT name FROM products WHERE name like '%$p_name%' LIMIT 5";
     $result = find_by_sql($sql);
     return $result;
   }

  /*--------------------------------------------------------------*/
  /* Function for Finding all product info by product title
  /* Request coming from ajax.php
  /*--------------------------------------------------------------*/
  function find_all_product_info_by_title($title){
    global $db;
    $sql  = "SELECT * FROM products ";
    $sql .= " WHERE name ='{$title}'";
    $sql .=" LIMIT 1";
    return find_by_sql($sql);
  }

  /*--------------------------------------------------------------*/
  /* Function for Update product quantity
  /*--------------------------------------------------------------*/
  function update_product_qty($qty,$p_id){
    global $db;
    $qty = (int) $qty;
    $id  = (int)$p_id;
    $sql = "UPDATE products SET quantity=quantity -'{$qty}' WHERE id = '{$id}'";
    $result = $db->query($sql);
    return($db->affected_rows() === 1 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Function for Display Recent product Added
  /*--------------------------------------------------------------*/
 function find_recent_product_added($limit){
   global $db;
   $sql   = " SELECT p.id,p.name,p.sale_price,p.media_id,c.name AS categorie,";
   $sql  .= "m.file_name AS image FROM products p";
   $sql  .= " LEFT JOIN categories c ON c.id = p.categorie_id";
   $sql  .= " LEFT JOIN media m ON m.id = p.media_id";
   $sql  .= " ORDER BY p.id DESC LIMIT ".$db->escape((int)$limit);
   return find_by_sql($sql);
 }
//transferencias
function join_transfer_table() {
  global $db;
  $sql  = "SELECT t.id, t.transfer_code, t.supervisor_id, t.date ";
  $sql .= "FROM transfers t ";
  return find_by_sql($sql);
}
//peticiones
function join_request_table() {
  global $db;
  $sql  = "SELECT r.id, r.request_code, r.request_name, r.date, r.status ";
  $sql .= "FROM requests r ";
  return find_by_sql($sql);
}
function join_return_table() {
  global $db;
  $sql  = "SELECT r.id, r.return_code, r.supervisor_id, r.date ";
  $sql .= "FROM returns r ";
  $sql .= "ORDER BY r.date DESC";
  return find_by_sql($sql);
}
function join_liquidation_table() {
  global $db;
  $sql  = "SELECT l.id, l.liquidation_code, l.supervisor_id, l.date ";
  $sql .= "FROM liquidations l ";
  $sql .= "ORDER BY l.date DESC";
  return find_by_sql($sql);
}
//tecnicos
function find_transfer_by_technician($technician_id) {
  global $db;
  $sql  = "SELECT t.id, t.transfer_code, t.transfer_name, t.date, t.status ";
  $sql .= "FROM transfers t ";
  $sql .= "WHERE t.technician_id = '{$db->escape((int)$technician_id)}'";
  return find_by_sql($sql);
}
function find_technician_returns($technician_id) {
  global $db;
  $technician_id = (int)$technician_id;
  $sql  = "SELECT p.id AS product_id, p.name AS product_name, SUM(r.quantity) AS quantity ";
  $sql .= "FROM products p ";
  $sql .= "JOIN return_items r ON p.id = r.product_id ";
  $sql .= "JOIN returns rt ON r.return_id = rt.id ";
  $sql .= "WHERE rt.technician_id = '{$db->escape($technician_id)}' ";
  $sql .= "GROUP BY p.id, p.name";
  return find_by_sql($sql);
}
function find_technician_liquidations($technician_id) {
  global $db;
  $technician_id = (int)$technician_id;
  $sql  = "SELECT p.id AS product_id, p.name AS product_name, SUM(l.quantity) AS quantity ";
  $sql .= "FROM products p ";
  $sql .= "JOIN liquidation_items l ON p.id = l.product_id ";
  $sql .= "JOIN liquidations liq ON l.liquidation_id = liq.id ";
  $sql .= "WHERE liq.technician_id = '{$db->escape($technician_id)}' ";
  $sql .= "GROUP BY p.id, p.name";
  return find_by_sql($sql);
}
function find_cuadrilla_returns($cuadrilla_id) {
  global $db;
  $cuadrilla_id = (int)$cuadrilla_id;
  $sql  = "SELECT p.id AS product_id, p.name AS product_name, SUM(r.quantity) AS quantity ";
  $sql .= "FROM products p ";
  $sql .= "JOIN return_items r ON p.id = r.product_id ";
  $sql .= "JOIN returns rt ON r.return_id = rt.id ";
  $sql .= "WHERE rt.cuadrilla_id = '{$db->escape($cuadrilla_id)}' ";
  $sql .= "GROUP BY p.id, p.name";
  return find_by_sql($sql);
}

function find_cuadrilla_liquidations($cuadrilla_id) {
  global $db;
  $cuadrilla_id = (int)$cuadrilla_id;
  $sql  = "SELECT p.id AS product_id, p.name AS product_name, SUM(l.quantity) AS quantity ";
  $sql .= "FROM products p ";
  $sql .= "JOIN liquidation_items l ON p.id = l.product_id ";
  $sql .= "JOIN liquidations liq ON l.liquidation_id = liq.id ";
  $sql .= "WHERE liq.cuadrilla_id = '{$db->escape($cuadrilla_id)}' ";
  $sql .= "GROUP BY p.id, p.name";
  return find_by_sql($sql);
}


function find_transfer_by_id($id) {
  global $db;
  $id = (int)$id;
  $sql  = "SELECT t.*, s.name AS supervisor_name, tech.name AS technician_name, c.name AS cuadrilla_name, ";
  $sql .= "o.operacion, o.oei, o.oe AS obra_name, o.central, o.ruta, o.pep ";
  $sql .= "FROM transfers t ";
  $sql .= "LEFT JOIN supervisors s ON t.supervisor_id = s.id ";
  $sql .= "LEFT JOIN technicians tech ON t.technician_id = tech.id ";
  $sql .= "LEFT JOIN cuadrillas c ON t.cuadrilla_id = c.id ";
  $sql .= "LEFT JOIN obras o ON t.obra_id = o.id ";
  $sql .= "WHERE t.id='{$db->escape($id)}'";
  $result = $db->query($sql);
  return $db->fetch_assoc($result);
}



function find_return_by_id($id) {
  global $db;
  $id = (int)$id;
  $sql  = "SELECT r.*, s.name AS supervisor_name, tech.name AS technician_name, c.name AS cuadrilla_name, ";
  $sql .= "o.operacion, o.oei, o.oe AS obra_name, o.central, o.ruta, o.pep ";
  $sql .= "FROM returns r ";
  $sql .= "LEFT JOIN supervisors s ON r.supervisor_id = s.id ";
  $sql .= "LEFT JOIN technicians tech ON r.technician_id = tech.id ";
  $sql .= "LEFT JOIN cuadrillas c ON r.cuadrilla_id = c.id ";
  $sql .= "LEFT JOIN obras o ON r.obra_id = o.id ";
  $sql .= "WHERE r.id='{$db->escape($id)}'";
  $result = $db->query($sql);
  return $db->fetch_assoc($result);
}


function find_liquidation_by_id($id) {
  global $db;
  $id = (int)$id;
  $sql  = "SELECT l.*, s.name AS supervisor_name, tech.name AS technician_name, c.name AS cuadrilla_name, ";
  $sql .= "o.operacion, o.oei, o.oe AS obra_name, o.central, o.ruta, o.pep ";
  $sql .= "FROM liquidations l ";
  $sql .= "LEFT JOIN supervisors s ON l.supervisor_id = s.id ";
  $sql .= "LEFT JOIN technicians tech ON l.technician_id = tech.id ";
  $sql .= "LEFT JOIN cuadrillas c ON l.cuadrilla_id = c.id ";
  $sql .= "LEFT JOIN obras o ON l.obra_id = o.id ";
  $sql .= "WHERE l.id='{$db->escape($id)}'";
  $result = $db->query($sql);
  return $db->fetch_assoc($result);
}

function find_technician_name($technician_id) {
  global $db;
  $technician_id = (int)$technician_id;
  $sql  = "SELECT name FROM technicians WHERE id = '{$db->escape($technician_id)}' LIMIT 1";
  $result = find_by_sql($sql);
  return !empty($result) ? $result[0]['name'] : null;
}
function find_cuadrilla_name($cuadrilla_id) {
  global $db;
  $cuadrilla_id = (int)$cuadrilla_id;
  $sql  = "SELECT name FROM cuadrillas WHERE id = '{$db->escape($cuadrilla_id)}' LIMIT 1";
  $result = find_by_sql($sql);
  return !empty($result) ? $result[0]['name'] : null;
}

?>
