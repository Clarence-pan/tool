<?php
/**
 * @var $links array
 * @var $pageEndLogPos int
 */
?>
<div class="tools">
    <form method="GET" action="<?php echo  $_SERVER['REQUEST_URI'] ?>" >
        <input type="button" onclick="refresh()" value="Refresh">
        <input type="button" onclick="refresh('clear', 'all')" Value="Clear" />
        <input type="button" onclick="refresh('displayStackTrace',1)" Value="Display Stack Trace" />
        <input type="button" onclick="refresh('start', <?php echo $pageEndLogPos ?>)" value="See new"/>
        <input type="button" onclick="window.stopAutoAppend = false; autoAppend();" value="Auto append" />
        <input type="button" onclick="window.stopAutoAppend = true" value="Stop auto append" />
        <input type="button" onclick="scrollToTop()" value="Top" />
        <input type="button" onclick="scrollToBottom()" value="Bottom" />
        <?php foreach ($links as $display => $href) {
                    echo CHtml::link($display, $href);
                    echo "<span> </span>";
         }  ?>
        <label>
            <input type="checkbox" onclick="toggle_very_brief(this.checked)"/>
            Brief
        </label>
    </form>
</div>
