<?php
if (PHP_SAPI !== 'cli') {
    echo "CLI only\n";
    exit(1);
}

$_SERVER['AUTO_COMMENT_SIMPLE_UPDATE'] = '1';
require __DIR__.'/build_package.php';
