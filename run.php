#!/usr/bin/php
<?php
$HOST = 'gbdev.xyz';
// $HOST = 'develop@gbdev.xyz';
$PORT = '9022';
$PROJECT_PATH = '~/www/pravo.gbdev.xyz';
$PROJECT_NAME = 'pravo-rosta';
$DUMP_NAME = 'db.sql';

// echo
$connection = ssh2_connect($HOST, $PORT);

if (ssh2_auth_pubkey_file(
    $connection,
    'develop',
    '/home/andrew/.ssh/id_rsa.pub',
    '/home/andrew/.ssh/id_rsa'
)) {
    $result = ssh2_scp_send($connection, './mysqldump.php', '/tmp/mysqldump.php', 0644);
    // print_r($result);
    $stream = ssh2_exec($connection, 'php /tmp/mysqldump.php pp=' . $PROJECT_PATH);
        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
    stream_set_blocking($errorStream, true);
    stream_set_blocking($stream, true);

    if ($output = stream_get_contents($stream)) {
        echo "Output: " . $output;
    }
    if ($error = stream_get_contents($errorStream)) {
        echo "Error: " . $error;
    }
    fclose($errorStream);
    fclose($stream);
    // Close the streams

    if (empty($output) && empty($output)) {
        $result = ssh2_scp_recv($connection, '/tmp/mysqldump.sql', './mysqldump.sql');
    }
}
