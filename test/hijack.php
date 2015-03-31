<?php

?>
<form method="post">

<textarea name="comment"><?php echo $_REQUEST['comment'] ?></textarea>
    <input type="submit" />

</form>

<div>
    <?php echo $_REQUEST['comment'] ?>
</div>