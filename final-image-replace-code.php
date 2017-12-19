<?php 
/*drms*/
				echo $article_longdesc;
				echo "<br/>";
			$pattern = '@src="([^"]+)"@';
			preg_match_all($pattern, $article_longdesc, $out);
			echo '<pre>';
			print_r($out);
			$i = 0;
			$dd = "";
            foreach ($out[1] as $value) {
            	$modified_string = "http://www.nationalgeographic.com/content/dam/magazine/Logos/national-geographic.jpg";
            	$article_longdesc = str_replace($value, $modified_string, $article_longdesc);
            }
            echo $article_longdesc;
			 exit();
				/*drms*/