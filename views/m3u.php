<?php
/** @var array $content */
?>
#EXTM3U
<?php
foreach ($content as $track):
    echo $track . "\r\n";
endforeach;
?>