<?php
function isCurlInstalled()
{
    return function_exists('curl_version');
}

function pingShopello()
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://se.shopelloapi.com/1/');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_ENCODING , 'gzip');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    $result = curl_exec($ch);
    $error = curl_error($ch);

    if (!empty($error)) {
        return $error.' (HTTP CODE '.curl_getinfo($ch, CURLINFO_HTTP_CODE).')';
    }

    return true;
}

function getCheckOrCross($condition)
{
    if ($condition === true) {
        return '<span style="color: green; font-size: 2em;">&#x2714;</span>';
    }

    return '<span style="color: red; font-size: 2em;">&#x2718;</span>';
}

$ping = pingShopello();
?>
<div class="wrap">
    <h1>System Test</h1>
    <hr />
    <p>
        This page will perform some system-tests to detect problems in your Wordpress setup while using the Shopello API.
        More tests will be added over time when we come up with things to test for, so please report issues to
        partner@shopello.se.
    </p>

    <h2>Performing checks</h2>
    <ul>
        <li>CURL Extension installed <?php echo getCheckOrCross(isCurlInstalled()) ?></li>
        <li>Able to connect to Shopello with CURL <?php echo getCheckOrCross($ping).(($ping === true) ? '' : $ping) ?></li>
    </ul>
</div>
