<?php
/**
 * @var $this DefaultController
 */
?>
<form action="index" method="GET">
    <label>
        <input type="checkbox" name="sum" />
        Summary - whether display summary of requests (not perfect!)
    </label>
    <label>
        <input type="checkbox" name="profile" />
        Profile - whether display profile results
    </label>
    <label>
        <input type="text" name="heading" />
        matching @msgHead at index 0
    </label>
    <label>
        <input type="text" name="category" />
        substring matching @category
    </label>
    <label>
        <input type="text" name="request" />
        substring matching @request URL
    </label>
    <label>
        Start from <input type="number" name="start" value="0" />
    </label>
    <label>
        limit <input type="number" name="limit" value="500" /> items.
    </label>
    <input type="submit" value="submit">
</form>