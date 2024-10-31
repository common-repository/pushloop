<?php
require_once('../../../../wp-load.php');

wp_enqueue_script('jquery');
wp_head();

if(!empty($_SERVER['HTTP_CLIENT_IP'])){
    $ip = $_SERVER['HTTP_CLIENT_IP'];
}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}else{
    $ip = $_SERVER['REMOTE_ADDR'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pushloop Debug Page</title>
</head>
<body>
    <h1>Pushloop Debug Page</h1>
    <div id="token"></div><br>
    <div id="indexDB"></div><br>
    <div id="userAgent"></div><br>
    <div id="ip"><?php echo "IP: $ip" ?></div><br>
    <div id="sw_version"></div><br>
    <div id="plugin_version"><?php echo 'Plugin Ver: '.PUSHLOOP_PLUGIN_VERSION ?></div><br>
    <div id="apiIp"><?php echo 'IP api: '.gethostbyname('api.pushloop.io'); ?></div><br>
    <div id="appIp"><?php echo 'IP app: '.gethostbyname('app.pushloop.io'); ?></div><br>
    
    <script>
        function wait(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        document.addEventListener('DOMContentLoaded', async function() {
            await wait(1000);
            if (typeof window.PushloopSw !== 'undefined' && typeof window.PushloopSw.getToken === 'function') {
                var cookieDeny = window.PushloopSw.getCookie('PushloopDenypushCookie');

                if (Notification.permission === 'granted' && (cookieDeny === undefined || cookieDeny === null)) {
                    try {
                        const token = await window.PushloopSw.getToken();
                        if (token !== null) {
                            document.getElementById('token').innerHTML = 'TOKEN: ' + token;
                            document.getElementById('userAgent').innerHTML = 'User Agent: ' + window.navigator.userAgent;
                            document.getElementById('sw_version').innerHTML = 'SW Ver: ' + window.PushloopSw.sw_version;

                            for (const table of window.PushloopSw.db_tables) {
                                await wait(1000);
                                const tokenData = await window.PushloopSw.getDataFromIndexDB('pushloop', table);
                                const resultElement = document.createElement('div');
                                resultElement.innerHTML = `Table: ${table}<br>IndexDB: ${JSON.stringify(tokenData)}`;
                                document.getElementById('indexDB').appendChild(resultElement);
                            }
                        }
                    } catch (error) {
                        console.error('Errore:', error);
                    }
                }
            }
        });

    </script>

    <?php wp_footer(); ?>
</body>
</html>