 <?php
 $expires = 60*60*24*365;
 header("Pragma: public");
 header("Cache-Control: max-age={$expires}");
 header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
 ?>

 <script src="//connect.facebook.net/en_US/all.js"></script>