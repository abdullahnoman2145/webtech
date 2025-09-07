<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Staff Panel</title>
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
<h1>🎬 Movie Staff Panel</h1>

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
