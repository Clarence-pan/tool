<?php
/**
 * @var $links array
 */
?>
<div class="tools">
    <form method="GET" action="<?php echo  $_SERVER['REQUEST_URI'] ?>" >
        <input type="button" onclick="refresh()" value="Refresh">
        <input type="button" onclick="refresh('clear', getGlobal('fileSize') || 9999999)" Value="Clear" />
        <input type="button" onclick="refresh('displayStackTrace',1)" Value="Display Stack Trace" />
        <input type="button" onclick="refresh('seek', getGlobal('fileSize'))" value="See new"/>
        <input type="button" onclick="window.stopAutoAppend = false; autoAppend();" value="Auto append" />
        <input type="button" onclick="window.stopAutoAppend = true" value="Stop auto append" />
        <input type="button" onclick="scrollToTop()" value="Top" />
        <input type="button" onclick="scrollToBottom()" value="Bottom" />
        <?php foreach ($links as $href => $display) { ?>
            <a href="<?php echo $href ?>"><?php echo $display ?></a>
        <?php  }  ?>
        <label>
            <input type="checkbox" onclick="toggle_very_brief(this.checked)"/>
            Brief
        </label>
    </form>
</div>
