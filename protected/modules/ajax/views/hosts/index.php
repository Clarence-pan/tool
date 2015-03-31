<?php
/**
 * @var $this HostsController
 * @var $fileName string
 * @var $fileContent string
 *
 */


?>

<form target="_self" method="POST" action="" >

    <h2><?php echo htmlspecialchars($fileName) ?></h2>
    <textarea name="content" id="content" cols="120" rows="50"><?php echo $fileContent ?></textarea>
    <input type="submit" value="Save" />
</form>

 