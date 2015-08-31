<?php
$time = addslashes($_POST['time']);
$submit = addslashes($_POST['submit']);

$array = array();

if($submit == '处理'){
    $daytime = str_replace("-", "", $time);
    
    $getcwd_file = getcwd();

    $dir = $getcwd_file."/phoneexcel/".$daytime;

    if (is_dir($dir)){

        if ($dh = opendir($dir)){
            $i = 0;
            while (($file = readdir($dh))!= false){
                $i++;

                $is_xls = strstr($file, '.xls');

                if($is_xls){
                    $array[$i] = $file;
                }
            }
            closedir($dh);
        }
    }else{
		die('没有/phone/phoneexcel/'.$daytime.'这个目录，请上传'.$time.'日数据');
	}

    foreach ($array as $filekey => $filevalue) {
        $duqu = $dir."/".$filevalue;

        $phpstr = file_get_contents($duqu);

        $rankrules = '/<Row>[\s\S]*?<\/Row>/i';

        preg_match_all($rankrules, $phpstr, $contentRows);

        foreach($contentRows as $key => $value){

            foreach($value as $kkk){
               $rankrules2 = '/<Cell>[\s\S]*?<\/Cell>/i';

                preg_match_all($rankrules2, $kkk, $contentRows2);

                foreach ($contentRows2 as $k => $v) {
                    $phonenum = strip_tags($v[0]);

                    $is_phone = strstr($phonenum, '*');

                    if($is_phone){
					
						$phonenum_new = preg_replace('/\D/u', '', $phonenum);

						if(!empty($phonenum_new)){
						//preg_match_all("/1[3,4,5,7,8]{1}[0-9]{1}[0-9]{8}|0[0-9]{2,3}-[0-9]{7,8}(-[0-9]{1,4})?/", $phonenum_new, $contentRows2);
						preg_match_all("((\d{11})|^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))$)", $phonenum_new, $contentRows2);
						//preg_match_all("((1[3,4,5,7,8]{1}\d{9}|0[0-9]{2,3}-[0-9]{7,8}(-[0-9]{1,4}))|^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})$)", $phonenum_new, $contentRows2);
							if(!empty($contentRows2[0])){
									$speaknum =  strip_tags($v[2]);
									$ipstr = strip_tags($v[3]);
									$ip_city_start = strripos($ipstr,"[");
									$ip_wangduan_end = strripos($ipstr,"]");
									$ipstart = strripos($ipstr,"(");
									$ipend = strripos($ipstr,")");
									$lengthip = $ipend - $ipstart;
									$ip = mb_substr($ipstr, $ipstart, $lengthip);
									$ip = str_replace("(", "", $ip);
									$city = mb_substr($ipstr, 0, $ip_city_start);
									$lengthwang = $ip_wangduan_end - $ip_city_start;
									$wangduan = mb_substr($ipstr, $ip_city_start, $lengthwang);
									$wangduan = str_replace("[", "", $wangduan);
									$wangduan = str_replace("]", "", $wangduan);

									$pageurl = strip_tags($v[4]);

									$pagefromkeywords = strip_tags($v[5]);
									$startkeywords = strripos($pagefromkeywords, "(");
									$endkeywords = strripos($pagefromkeywords, ")");
									$lengthkeywords = $endkeywords - $startkeywords;
									$search_keywords = mb_substr($pagefromkeywords, $startkeywords, $lengthkeywords, 'utf-8');
									$search_keywords = str_replace("(", "", $search_keywords);
									$search_keywords = str_replace(")", "", $search_keywords);

									@preg_match_all('/href\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^"\'>\s]+))/', $v[5], $pagefrom);

									$ppagefrom = @urldecode($pagefrom[2][0]);

									$datetime = strip_tags($v[6]);
									$endway = strip_tags($v[8]);
									$speaktime = strip_tags($v[10]);
									$open = fopen($getcwd_file."/phonetxt/".$daytime.".txt","a");
									$phonenum_new = substr_replace($phonenum_new,"****",-4,4);
									$content = $speaknum."|".$wangduan."|".$city."|".$phonenum_new."|".$ip."|".$pageurl."|".$ppagefrom."|".$search_keywords."|".$datetime."|".$endway."|".$speaktime."|";
									
									if(PATH_SEPARATOR==':') {
										$contxt = fwrite($open, $content."\n");//linux
									}else{
										$contxt = fwrite($open, $content."\r\n");  //windows
									}      
							}
						}
                    }
                }
            }
        }  
        echo $filevalue."文件处理完成<br />";
    }
	echo "<br /><br /><span style='color:red'>1、请您下载phonetxt目录下".$daytime.".txt至本地，下载成功后删除该txt文档</span><br />";
	echo "<span style='color:red'>2、删除phoneexcel目录下的".$daytime."文件夹</span>";
}

