<?php
/**
 * @var $this DefaultController
 */
?>
<form id="query" action="index" method="GET">
    <label>
        <input type="checkbox" name="sum" />
        Summary - whether display summary of requests (not perfect!)
    </label>
    <br/>
    <label>
        <input type="checkbox" name="profile" />
        Profile - whether display profile results
    </label>
    <br/>
    <label>
        Matching @msgHead at index 0:
        <input type="text" name="heading" />
    </label>
    <br/>
    <label>
        Substring matching @category:
        <input type="text" name="category" />
    </label>
    <br/>
    <label>
        Substring matching @request URL:
        <input type="text" name="request" />
    </label>
    <br/>
    <label>
        Start from <input type="number" name="start" value="0" />
    </label>
    <label>
        limit <input type="number" name="limit" value="500" /> items.
    </label>
    <input type="submit" value="submit">
</form>