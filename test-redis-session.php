<?php

\SafeServiceWrapper\Redis::configureSessionHandling();
$session_handler = ini_get('session.save_handler');
$session_path = ini_get('session.save_path');
echo $session_handler."\n";
echo $session_path."\n";
