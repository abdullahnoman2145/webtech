<?php
session_start();
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit;
}

require_once __DIR__ . '/db.php';  
$db = db_connect();

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function csrf_field(){ echo '<input type="hidden" name="csrf_token" value="'.htmlspecialchars($_SESSION['csrf_token']).'">'; }
function check_csrf(){ if(($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')){ die('Invalid CSRF token'); } }
function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$errors=[]; $ok='';

// Add movie
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='add_movie'){
  check_csrf();
  $title=trim($_POST['title']??'');
  $poster=trim($_POST['poster_url']??'');
  $trailer=trim($_POST['trailer_url']??'');
  $cast=trim($_POST['cast']??'');
  $rating=trim($_POST['rating']??'');
  $genres=implode(',',$_POST['genres']??[]);
  $status=$_POST['status']??'Upcoming';

  if($title==='') $errors[]='Title required';
  if($rating!=='' && !is_numeric($rating)) $errors[]='Rating must be numeric';

  if(!$errors){
    $stmt=$db->prepare("INSERT INTO movies (title,poster_url,trailer_url,cast,rating,genres,status) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param('sssssss',$title,$poster,$trailer,$cast,$rating,$genres,$status);
    if($stmt->execute()) $ok='Movie added successfully'; else $errors[]='Insert failed';
  }
}


if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='delete_movie'){
  check_csrf();
  $id=(int)($_POST['id']??0);
  $stmt=$db->prepare("DELETE FROM movies WHERE id=? LIMIT 1");
  $stmt->bind_param('i',$id);
  if($stmt->execute()) $ok='Movie removed'; else $errors[]='Delete failed';
}


$movies=$db->query("SELECT * FROM movies ORDER BY id DESC");
$GENRES=['Action','Comedy','Horror','Drama','Romance','Sci-Fi','Thriller','Animation','Family','Adventure','Fantasy'];
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Panel</title>
<style>
body{background:#0b1020;color:#fff;font-family:sans-serif;padding:20px}
.card{background:#121a2b;padding:20px;border-radius:10px;margin-bottom:20px}
input,select{width:100%;padding:8px;margin-bottom:10px;border-radius:6px;border:1px solid #333;background:#0a1426;color:#fff}
.btn{padding:8px 14px;border-radius:6px;border:0;cursor:pointer}
.btn-primary{background:#5078ff;color:#fff}
.btn-danger{background:#ef4444;color:#fff}
.table{width:100%;border-collapse:collapse}
.table th,.table td{padding:8px;border-bottom:1px solid #333}
.poster{width:50px;height:70px;object-fit:cover}
.ok{background:#14532d;padding:8px;margin-bottom:10px}
.err{background:#7f1d1d;padding:8px;margin-bottom:10px}
</style>
</head>
<body>
<h1>🎬 Movie Admin Panel</h1>

<?php if($ok): ?><div class="ok"><?=esc($ok)?></div><?php endif; ?>
<?php if($errors): ?><div class="err"><?php foreach($errors as $e) echo esc($e).'<br>'; ?></div><?php endif; ?>

<div class="card">
<h2>Add Movie</h2>
<form method="post">
  <?php csrf_field(); ?>
  <input type="hidden" name="action" value="add_movie">
  <input type="text" name="title" placeholder="Movie Title" required>
  <input type="url" name="poster_url" placeholder="Poster URL">
  <input type="url" name="trailer_url" placeholder="Trailer URL">
  <input type="text" name="cast" placeholder="Cast (comma separated)">
  <input type="number" name="rating" min="0" max="10" step="0.1" placeholder="Rating">
  <label>Genres:</label>
  <?php foreach($GENRES as $g): ?>
    <label><input type="checkbox" name="genres[]" value="<?=esc($g)?>"> <?=esc($g)?></label>
  <?php endforeach; ?>
  <br>
  <select name="status">
    <option value="Released">Released</option>
    <option value="Upcoming">Upcoming</option>
  </select>
  <button class="btn btn-primary">Save</button>
</form>
</div>

<div class="card">
<h2>Movies List</h2>
<table class="table">
<tr><th>Poster</th><th>Title</th><th>Status</th><th>Action</th></tr>
<?php while($m=$movies->fetch_assoc()): ?>
<tr>
  <td><?php if($m['poster_url']): ?><img src="<?=esc($m['poster_url'])?>" class="poster"><?php endif; ?></td>
  <td><?=esc($m['title'])?></td>
  <td><?=esc($m['status'])?></td>
  <td>
    <form method="post" onsubmit="return confirm('Remove this movie?')">
      <?php csrf_field(); ?>
      <input type="hidden" name="action" value="delete_movie">
      <input type="hidden" name="id" value="<?=$m['id']?>">
      <button class="btn btn-danger">Remove</button>
    </form>
  </td>
</tr>
<?php endwhile; ?>
</table>
</div>
</body>
</html>
