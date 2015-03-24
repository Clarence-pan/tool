<?php
/**
 * @var $this HostsController
 * @var $fileName string
 * @var $lines array of string
 *
 */


?>
<style>
    code.head {
        font-weight: bold;
    }
    label{
        cursor: pointer;
    }
    .head{
        margin-top: 1em;
    }
    label:hover{
        color: #96910b;
    }

</style>

<form id="hosts" target="_self" method="POST" action="" >

    <h2><?php echo htmlspecialchars($fileName) ?></h2>
    <?php foreach ($lines as $index => $line) {
        $line = rtrim($line);
        $isHead = preg_match('/^#!/',$line);
        $isDisabled = preg_match('/^#/',$line);
        $isBlank = preg_match('/^\s*$/', $line);
        if ($isBlank){
            continue;
        }
        ?>
        <div class="<?php echo $isHead ? 'head':'' ?>">
         <label >
             <input type="checkbox" class="lines" name="lines[<?php echo $index ?>]"
                    title="Disable host"
                    value="<?php echo htmlspecialchars($line) ?>"
                   <?php echo $isDisabled ? 'checked=checked' : '' ?>
                   <?php echo $isHead ? 'disabled=disabled' : '' ?>
                   <?php echo $isBlank ? 'style="display: none"':'' ?>
                    />
             <code class="<?php echo $isHead ? 'head':'' ?>"><?php echo htmlspecialchars($line) ?></code>
         </label>
        </div>
    <?php } ?>
    <input type="hidden" name="content">
    <input type="submit" value="Save" style="width: 20em; height: 2em; text-align: center; margin-left: 2em" />
</form>
<script type="application/javascript" src="/static/js/jquery.js"></script>
<script>
    $(function(){
        $('#hosts :checkbox').on('change', function(){
            var $this = $(this);
            if ($this.is(':checked')){
                $this.val('#'+$this.val());
            } else {
                $this.val($this.val().substring(1));
            }
            $this.next('code').text($this.val());
        });
        $('#hosts').on('submit', function(){
            var content = "";
            $('input:checkbox.lines').each(function(){
                content += $(this).val()+"\n";
            });
            $('input[name=content]').val(content);
        });
    });
</script>