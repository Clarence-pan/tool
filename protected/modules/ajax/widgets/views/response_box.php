<?php
/**
 * @var $this ResponseBoxWidget
 */
?>
<div class="response-box">
    <i><?php echo $this->response->createTime ?></i>
    <pre><code><?php echo htmlspecialchars($this->response->header); ?></code></pre>
    <pre><code><?php echo htmlspecialchars($this->response->body); ?></code></pre>
</div>