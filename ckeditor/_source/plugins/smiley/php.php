<?php
$a=0;
if ($handle = opendir('images/')) :
echo '[';
    while (false !== ($file = readdir($handle))) :
        if (preg_match("/gif/",$file)) : ?>
                  
           '<?php echo $file;?>',
            
        <?php endif;
		$a++;
		endwhile ;
    echo ']';

    closedir($handle);
endif;


?>