<article>
<?php echo $html; ?>
</article>

<!-- the following is style for this article -->
<style>
    td, th {
        padding: 0.2em;
        border: 1px solid #777;
    }
    p {
        white-space: pre;
    }
    .content{
        font-size: 10px;
        line-height: 12px;
        margin-left: 10em;
    }
    h1:before,h2:before,h3:before,h4:before,h5:before{
        content: '* ';
    }
    h1,h2,h3,h4,h5{
        font-weight: bold;
    }
    h1{
        font-size: 20px;
        padding-left: 0;
    }
    h2{
        font-size: 17px;
        padding-left: 1em;
    }
    h3{
        font-size: 15px;
        padding-left: 2em;
    }
    h4{
        font-size: 13px;
        padding-left: 3em;
    }
    h5{
        font-size: 12px;
        padding-left: 4em;
    }
</style>
<script>
    $(function(){
        function makeContent(){
            var id = 1;
            var $content = $('<div class="content"></div>');
            $("h1,h2,h3,h4,h5,h6").each(function(){
                var $this = $(this);
                var $contentPointer = $this.clone().removeAttr('id');
                var $thisId = $this.attr('id');
                if (!$thisId){
                    $thisId = 'content-'+id;
                    id++;
                    $this.attr('id', $thisId);
                }
                $contentPointer.wrapInner('<a href="#'+$thisId+'" ></a>');
                $content.append($contentPointer);
            })
            $content.prependTo('body');
        }
        makeContent();
    });
</script>


